<?php
/**
 * Enhanced Authentication System
 * Includes secure login, account lockout, and session management
 */

// Prevent direct access
if (!defined('ACCESS_ALLOWED')) {
    die('Direct access not permitted');
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configuration constants
define('MAX_LOGIN_ATTEMPTS', 3);
define('LOCKOUT_DURATION', 900); // 15 minutes in seconds

/**
 * Check if user is logged in
 * @return bool True if logged in, false otherwise
 */
function isLoggedIn() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true &&
           isset($_SESSION['manager_id']) && isset($_SESSION['username']);
}

/**
 * Get current user's IP address
 * @return string IP address
 */
function getUserIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

/**
 * Check if account is currently locked
 * @param mysqli $conn Database connection
 * @param string $username Username to check
 * @return array Lock status and time remaining
 */
function isAccountLocked($conn, $username) {
    $username = $conn->real_escape_string($username);
    
    // Check for active lock
    $sql = "SELECT LockedUntil, FailedAttempts FROM account_locks 
            WHERE Username = '$username' AND LockedUntil > NOW()";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $timeRemaining = strtotime($row['LockedUntil']) - time();
        return [
            'locked' => true,
            'until' => $row['LockedUntil'],
            'seconds_remaining' => $timeRemaining,
            'minutes_remaining' => ceil($timeRemaining / 60)
        ];
    }
    
    // Clean up expired locks
    $conn->query("DELETE FROM account_locks WHERE Username = '$username' AND LockedUntil <= NOW()");
    
    return ['locked' => false];
}

/**
 * Get failed login attempt count within the last hour
 * @param mysqli $conn Database connection
 * @param string $username Username to check
 * @return int Number of failed attempts
 */
function getFailedAttemptCount($conn, $username) {
    $username = $conn->real_escape_string($username);
    
    $sql = "SELECT COUNT(*) as count FROM login_attempts 
            WHERE Username = '$username' 
            AND Success = 0 
            AND AttemptTime > DATE_SUB(NOW(), INTERVAL 1 HOUR)";
    
    $result = $conn->query($sql);
    if ($result) {
        $row = $result->fetch_assoc();
        return (int)$row['count'];
    }
    return 0;
}

/**
 * Log a login attempt
 * @param mysqli $conn Database connection
 * @param string $username Username attempting login
 * @param bool $success Whether login was successful
 */
function logLoginAttempt($conn, $username, $success) {
    $username = $conn->real_escape_string($username);
    $ipAddress = getUserIP();
    $successInt = $success ? 1 : 0;
    
    $stmt = $conn->prepare("INSERT INTO login_attempts (Username, IPAddress, Success) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $username, $ipAddress, $successInt);
    $stmt->execute();
    $stmt->close();
}

/**
 * Lock an account temporarily
 * @param mysqli $conn Database connection
 * @param string $username Username to lock
 */
function lockAccount($conn, $username) {
    $username = $conn->real_escape_string($username);
    $failedAttempts = getFailedAttemptCount($conn, $username);
    
    // Calculate lock duration (can increase with repeated violations)
    $lockDuration = LOCKOUT_DURATION;
    
    // Remove existing lock record if any
    $conn->query("DELETE FROM account_locks WHERE Username = '$username'");
    
    // Create new lock
    $stmt = $conn->prepare(
        "INSERT INTO account_locks (Username, LockedUntil, FailedAttempts) 
         VALUES (?, DATE_ADD(NOW(), INTERVAL ? SECOND), ?)"
    );
    $stmt->bind_param("sii", $username, $lockDuration, $failedAttempts);
    $stmt->execute();
    $stmt->close();
}

/**
 * Handle login with security measures
 * @param mysqli $conn Database connection
 * @param string $username Username
 * @param string $password Password
 * @return array Result with success status and message
 */
function handleLogin($conn, $username, $password) {
    $username = trim($username);
    $password = trim($password);
    
    // Check if account is locked
    $lockStatus = isAccountLocked($conn, $username);
    if ($lockStatus['locked']) {
        return [
            'success' => false,
            'message' => "Account is temporarily locked due to too many failed login attempts. Please try again in " . 
                        $lockStatus['minutes_remaining'] . " minute(s)."
        ];
    }
    
    // Check failed attempt count
    $failedAttempts = getFailedAttemptCount($conn, $username);
    
    // Prepare and execute query
    $stmt = $conn->prepare("SELECT ManagerID, Username, PasswordHash, FullName, IsActive FROM managers WHERE Username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $manager = $result->fetch_assoc();
        
        // Check if account is active
        if ($manager['IsActive'] != 1) {
            logLoginAttempt($conn, $username, false);
            $stmt->close();
            return [
                'success' => false,
                'message' => "This account has been deactivated. Contact administrator."
            ];
        }
        
        // Verify password
        if (password_verify($password, $manager['PasswordHash'])) {
            // Successful login
            $_SESSION['logged_in'] = true;
            $_SESSION['manager_id'] = $manager['ManagerID'];
            $_SESSION['username'] = $manager['Username'];
            $_SESSION['full_name'] = $manager['FullName'];
            $_SESSION['login_time'] = time();
            
            // Regenerate session ID to prevent session fixation
            session_regenerate_id(true);
            
            // Update last login time
            $updateStmt = $conn->prepare("UPDATE managers SET LastLogin = NOW() WHERE ManagerID = ?");
            $updateStmt->bind_param("i", $manager['ManagerID']);
            $updateStmt->execute();
            $updateStmt->close();
            
            // Log successful attempt
            logLoginAttempt($conn, $username, true);
            
            // Clear any failed attempts
            $conn->query("DELETE FROM login_attempts WHERE Username = '" . $conn->real_escape_string($username) . "' AND Success = 0");
            $conn->query("DELETE FROM account_locks WHERE Username = '" . $conn->real_escape_string($username) . "'");
            
            // Log manager action
            logManagerAction($conn, $manager['ManagerID'], 'Login', null, 'Successful login');
            
            $stmt->close();
            return [
                'success' => true,
                'message' => "Login successful! Welcome back, " . htmlspecialchars($manager['FullName']) . "."
            ];
        } else {
            // Failed password
            logLoginAttempt($conn, $username, false);
            $failedAttempts++;
            
            // Lock account if max attempts reached
            if ($failedAttempts >= MAX_LOGIN_ATTEMPTS) {
                lockAccount($conn, $username);
                $stmt->close();
                return [
                    'success' => false,
                    'message' => "Too many failed login attempts. Your account has been locked for " . 
                                (LOCKOUT_DURATION / 60) . " minutes."
                ];
            }
            
            $remainingAttempts = MAX_LOGIN_ATTEMPTS - $failedAttempts;
            $stmt->close();
            return [
                'success' => false,
                'message' => "Invalid username or password. " . $remainingAttempts . " attempt(s) remaining."
            ];
        }
    } else {
        // Username not found - still log the attempt
        logLoginAttempt($conn, $username, false);
        $failedAttempts++;
        
        // Lock potential brute force attempts even on non-existent users
        if ($failedAttempts >= MAX_LOGIN_ATTEMPTS) {
            lockAccount($conn, $username);
            $stmt->close();
            return [
                'success' => false,
                'message' => "Too many failed login attempts. Please try again later."
            ];
        }
        
        $stmt->close();
        return [
            'success' => false,
            'message' => "Invalid username or password."
        ];
    }
}

/**
 * Log manager actions for audit trail
 * @param mysqli $conn Database connection
 * @param int $managerId Manager ID
 * @param string $actionType Type of action
 * @param int|null $eoiNumber EOI number if applicable
 * @param string $details Additional details
 */
function logManagerAction($conn, $managerId, $actionType, $eoiNumber = null, $details = '') {
    $ipAddress = getUserIP();
    
    $stmt = $conn->prepare(
        "INSERT INTO manager_actions (ManagerID, ActionType, EOInumber, ActionDetails, IPAddress) 
         VALUES (?, ?, ?, ?, ?)"
    );
    $stmt->bind_param("isiss", $managerId, $actionType, $eoiNumber, $details, $ipAddress);
    $stmt->execute();
    $stmt->close();
}

/**
 * Handle logout
 */
function handleLogout() {
    global $conn;
    
    // Log the logout action before destroying session
    if (isset($_SESSION['manager_id'])) {
        logManagerAction($conn, $_SESSION['manager_id'], 'Logout', null, 'User logged out');
    }
    
    // Destroy session
    $_SESSION = array();
    
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    session_destroy();
    header("Location: manage.php");
    exit();
}

/**
 * Require login (redirect if not logged in)
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: manage.php");
        exit();
    }
}

/**
 * Check session timeout (optional - 30 minutes of inactivity)
 */
function checkSessionTimeout() {
    $timeout = 1800; // 30 minutes
    
    if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time'] > $timeout)) {
        handleLogout();
    }
    
    // Update last activity time
    $_SESSION['login_time'] = time();
}
?>