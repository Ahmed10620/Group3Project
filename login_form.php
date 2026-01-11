<div class="login-box">
    <h2>Manager Login</h2>
    <?php if (isset($loginError) && $loginError): ?>
        <div class="error-message"><?php echo $loginError; ?></div>
    <?php endif; ?>
    <form method="POST" action="manage.php">
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit" name="login" class="btn btn-full">Login</button>
    </form>
    <p style="text-align: center; margin-top: 1.5rem; color: #666; font-size: 1.1rem;">
        Demo credentials: <strong>manager</strong> / <strong>admin123</strong>
    </p>
</div>