<?php include 'header.inc'; ?>
    

    <div class="content-container">

        <section class="box">
            <h1 class="section-title">Project Enhancements</h1>
            <p>This page documents the additional features and enhancements implemented in our project beyond the basic requirements.</p>
        </section>

        <section class="box">
            <h2 class="section-title">Enhancement 1: Manager Authentication System</h2>
            
            <h3>What it does:</h3>
            <p>We implemented a complete authentication system that controls access to the HR manager dashboard (manage.php). Only authorized managers can view, update, and delete job applications.</p>

            <h3>How it works:</h3>
            <ul>
                <li><strong>Login System:</strong> Managers must log in with valid credentials through login_form.php</li>
                <li><strong>Session Management:</strong> PHP sessions track logged-in users throughout their visit using auth.php</li>
                <li><strong>Access Control:</strong> The manage.php page checks if a user is authenticated before displaying any EOI data</li>
                <li><strong>Logout Functionality:</strong> Managers can securely log out, which destroys their session through logout.php</li>
                <li><strong>Password Security:</strong> Passwords are hashed using PHP's password_hash() function for security</li>
            </ul>

            <h3>Technical Implementation:</h3>
            <p><strong>Files Created:</strong></p>
            <ul>
                <li><code>login_form.php</code> - Login page with form validation</li>
                <li><code>auth.php</code> - Authentication handler and session management</li>
                <li><code>logout.php</code> - Session destruction and logout</li>
                <li><code>manage.php</code> - Protected manager dashboard (modified with authentication check)</li>
            </ul>

            <p><strong>Database:</strong></p>
            <ul>
                <li>Created <code>managers</code> table with fields: id, username, password (hashed), created_date</li>
            </ul>

            <p><strong>Security Features:</strong></p>
            <ul>
                <li>Passwords are never stored in plain text</li>
                <li>Sessions prevent unauthorized access</li>
                <li>Automatic redirect to login page for unauthenticated users</li>
                <li>Session timeout after period of inactivity</li>
            </ul>

            <h3>What we learned:</h3>
            <p>This enhancement taught us about:</p>
            <ul>
                <li>PHP session management and security best practices</li>
                <li>Password hashing and verification using modern PHP functions</li>
                <li>Implementing access control in web applications</li>
                <li>User authentication flow and state management</li>
                <li>Protecting sensitive pages from unauthorized access</li>
            </ul>

            <h3>How to test it:</h3>
            <ol>
                <li>Navigate to <code>manage.php</code> - you will be redirected to the login page</li>
                <li>Enter valid manager credentials on <code>login_form.php</code></li>
                <li>Upon successful login, you'll be redirected to the manager dashboard</li>
                <li>You can now view and manage EOI records</li>
                <li>Click logout to end your session</li>
            </ol>
        </section>

        <section class="box">
            <h2 class="section-title">Enhancement 2: Database Configuration & EOI Table Design</h2>
            
            <h3>What it does:</h3>
            <p>We created a robust database infrastructure including connection configuration and a comprehensive Expression of Interest (EOI) table to store and manage job application data efficiently.</p>

            <h3>How it works:</h3>
            <ul>
                <li><strong>Database Configuration:</strong> settings.php provides centralized connection parameters for MySQL database</li>
                <li><strong>EOI Table Structure:</strong> Stores complete applicant information with proper data types and constraints</li>
                <li><strong>Status Tracking:</strong> ENUM field tracks application lifecycle (New, Current, Final)</li>
                <li><strong>Auto-Increment:</strong> Primary key automatically generates unique EOI numbers</li>
                <li><strong>Timestamp Automation:</strong> Records creation time automatically for each application</li>
            </ul>

            <h3>Technical Implementation:</h3>
            <p><strong>Files Created:</strong></p>
            <ul>
                <li><code>settings.php</code> - Database connection configuration (host, user, password, database name)</li>
                <li><code>eoi.sql</code> - SQL script to create the EOI table with all fields and constraints</li>
            </ul>

            <p><strong>Database Structure:</strong></p>
            <ul>
                <li><strong>Primary Key:</strong> EOInumber (INT, AUTO_INCREMENT)</li>
                <li><strong>Personal Info:</strong> first_name, last_name, date_of_birth, gender (VARCHAR fields)</li>
                <li><strong>Address Fields:</strong> unit_number, building_number, street_name, street_number, zone, city</li>
                <li><strong>Contact Info:</strong> email_address (VARCHAR 100), phone_number (VARCHAR 12)</li>
                <li><strong>Skills Fields:</strong> data_analyst, soc_analyst, other_skills (TEXT for detailed input)</li>
                <li><strong>Status Management:</strong> status ENUM('New', 'Current', 'Final') DEFAULT 'New'</li>
                <li><strong>Audit Trail:</strong> created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP</li>
            </ul>

            <p><strong>Key Features:</strong></p>
            <ul>
                <li>Normalized database design following best practices</li>
                <li>Appropriate data types for each field (VARCHAR, TEXT, ENUM, INT, TIMESTAMP)</li>
                <li>Zone-based address system for Qatar location structure</li>
                <li>Flexible TEXT fields allow detailed skill descriptions</li>
                <li>Automatic timestamp ensures data integrity</li>
            </ul>

            <h3>What we learned:</h3>
            <p>This enhancement taught us about:</p>
            <ul>
                <li>Database normalization and proper table structure design</li>
                <li>Choosing appropriate data types for different information types</li>
                <li>Using ENUM for controlled status values</li>
                <li>Implementing auto-increment primary keys</li>
                <li>Creating reusable database configuration files</li>
                <li>Separating configuration from application logic</li>
            </ul>
        </section>

        <section class="box">
            <h2 class="section-title">Enhancement 3: Modern CSS System</h2>
            
            <h3>What it does:</h3>
            <p>We developed a comprehensive, modern CSS styling system that provides professional aesthetics, smooth animations, and excellent accessibility across the entire website.</p>

            <h3>How it works:</h3>
            <ul>
                <li><strong>Global Styling:</strong> Consistent design applied across all pages using a single CSS file</li>
                <li><strong>Gradient Animations:</strong> Dynamic gradients with keyframe animations</li>
                <li><strong>Interactive Elements:</strong> Hover effects, transitions, and transforms on buttons and links</li>
                <li><strong>Responsive Design:</strong> Mobile-first approach with media queries for all screen sizes</li>
                <li><strong>Accessibility Focus:</strong> Keyboard navigation, focus states, and high-contrast elements</li>
            </ul>

            <h3>Technical Implementation:</h3>
            <p><strong>Key CSS Features:</strong></p>
            <ul>
                <li><strong>Color Gradients:</strong> Linear gradients using orange, red, and yellow (#FF7A3D, #FFB649, #FFD16A, #dd5472)</li>
                <li><strong>Animated Text:</strong> Gradient text with shifting animation using @keyframes</li>
                <li><strong>Advanced Buttons:</strong> Ripple effects, scale transforms, and dynamic shadows on hover</li>
                <li><strong>Modern Forms:</strong> Enhanced inputs with focus states, transitions, and accessibility features</li>
                <li><strong>Sticky Header:</strong> Position sticky with smooth transitions and hover effects</li>
                <li><strong>Hero Section:</strong> Parallax background with overlay gradients and fade-in animations</li>
                <li><strong>Content Cards:</strong> Box shadows, border accents, and hover lift effects</li>
                <li><strong>Footer Design:</strong> Gradient backgrounds with animated link underlines</li>
            </ul>

            <p><strong>Animation Examples:</strong></p>
            <ul>
                <li>Gradient shift animation for text (3s infinite loop)</li>
                <li>Button hover ripple effect with pseudo-elements</li>
                <li>Transform translateY and scale on interactive elements</li>
                <li>Fade-in-up animation for hero text (1s ease)</li>
                <li>Smooth scroll behavior for navigation links</li>
            </ul>

            <p><strong>Accessibility Features:</strong></p>
            <ul>
                <li>High-contrast focus indicators (3px solid outline with offset)</li>
                <li>Custom accent colors for radio buttons and checkboxes</li>
                <li>Skip-to-content link for keyboard navigation</li>
                <li>ARIA-friendly form labels and semantic HTML</li>
                <li>Responsive breakpoints at 768px for mobile devices</li>
            </ul>

            <h3>What we learned:</h3>
            <p>This enhancement taught us about:</p>
            <ul>
                <li>Advanced CSS3 features including gradients, transforms, and animations</li>
                <li>Creating cohesive design systems with consistent branding</li>
                <li>Implementing smooth transitions and hover effects</li>
                <li>Writing accessible CSS with proper focus states</li>
                <li>Using CSS Grid and Flexbox for responsive layouts</li>
                <li>Performance optimization with efficient selectors</li>
                <li>Creating reusable CSS classes and maintaining code organization</li>
            </ul>
        </section>

        <section class="box">
            <h2 class="section-title">Enhancement 4: Jobs Page & Custom Styling</h2>
            
            <h3>What it does:</h3>
            <p>We created a dedicated jobs page that displays available career opportunities at ORA Technologies with custom styling that integrates seamlessly with our design system.</p>

            <h3>How it works:</h3>
            <ul>
                <li><strong>Structured Layout:</strong> Semantic HTML5 structure for job listings and descriptions</li>
                <li><strong>Grid System:</strong> CSS Grid layout for responsive job card display</li>
                <li><strong>Job Cards:</strong> Individual sections for each position with detailed information</li>
                <li><strong>Interactive Design:</strong> Hover effects and transitions on job postings</li>
                <li><strong>Call-to-Action:</strong> Prominent "Apply Now" buttons with styled design</li>
            </ul>

            <h3>Technical Implementation:</h3>
            <p><strong>Files Created:</strong></p>
            <ul>
                <li><code>jobs.html</code> - Complete job listings page with structured sections</li>
                <li>Custom CSS in global stylesheet for job-specific styling</li>
            </ul>

            <p><strong>CSS Features for Jobs Page:</strong></p>
            <ul>
                <li><strong>Grid Layout:</strong> <code>display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));</code></li>
                <li><strong>Job Cards:</strong> White background, border-radius, box-shadow, and left border accent</li>
                <li><strong>Hover Effects:</strong> Transform translateY(-8px) with enhanced shadow on hover</li>
                <li><strong>Typography:</strong> Section titles with gradient underlines using ::after pseudo-elements</li>
                <li><strong>Responsive Design:</strong> Adapts from multi-column grid to single column on mobile</li>
            </ul>

            <p><strong>Key Design Elements:</strong></p>
            <ul>
                <li>Consistent color scheme throughout</li>
                <li>Clear job titles with gradient text effects</li>
                <li>Organized job requirements and responsibilities lists</li>
                <li>Salary information and job details prominently displayed</li>
                <li>Integration with EOI form through apply buttons</li>
            </ul>

            <h3>What we learned:</h3>
            <p>This enhancement taught us about:</p>
            <ul>
                <li>Creating effective job listing pages that convert visitors to applicants</li>
                <li>Using CSS Grid for flexible, responsive card layouts</li>
                <li>Maintaining design consistency across different page types</li>
                <li>Structuring content for readability and user engagement</li>
                <li>Integrating multiple pages into a cohesive website experience</li>
            </ul>
        </section>

        <section class="box">
            <h2 class="section-title">Enhancement 5: Secure Application System + About Page Integration</h2>
            
            <h3>What it does:</h3>
            <p>We improved the Apply, Process EOI, and About sections by creating a secure job application workflow
            (Apply form + server-side processing) and a structured About page that clearly presents team information,
            contributions, and group content using semantic HTML elements.</p>

            <h3>How it works:</h3>
            <ul>
                <li><strong>Apply Form (apply.php):</strong> Collects applicant details, job reference, skills, and acknowledgement, then submits via POST to <code>process_eoi.php</code>.</li>
                <li><strong>CSRF Protection:</strong> Generates a session-based CSRF token in <code>apply.php</code> and validates it in <code>process_eoi.php</code> to prevent unauthorized submissions.</li>
                <li><strong>Multiple Skills Handling:</strong> Supports selecting multiple checkboxes by using array field names (<code>data_analyst[]</code>, <code>soc_analyst[]</code>) and processes them safely in PHP.</li>
                <li><strong>Server-Side Validation + Storage:</strong> Validates all fields in PHP and stores submissions using prepared statements.</li>
                <li><strong>About Page Structure (about.php):</strong> Uses semantic sections, definition lists, nested lists, and a table to present group details, contributions, and interests consistently with the website layout.</li>
            </ul>

            <h3>Technical Implementation:</h3>
            <p><strong>Files Created/Modified:</strong></p>
            <ul>
                <li><code>apply.php</code> - Application form with CSRF token and multi-checkbox support</li>
                <li><code>process_eoi.php</code> - Validation, multi-skill processing, database insertion, and success/error pages</li>
                <li><code>about.php</code> - Structured About page showing group info, contributions, photo, and interests</li>
                <li><code>thankyou.php</code> - Gives user feedback that the application has been submitted successfully</li>

            </ul>

            <p><strong>Key Features:</strong></p>
            <ul>
                <li>Consistent field naming between <code>apply.php</code> and <code>process_eoi.php</code> to avoid submission conflicts</li>
                <li>Secure CSRF protection using sessions</li>
                <li>Multi-checkbox input support for technical skills (stored as combined text values)</li>
                <li>Prepared SQL statements to protect against SQL injection</li>
                <li>Semantic About page layout using <code>&lt;section&gt;</code>, <code>&lt;dl&gt;</code>, nested <code>&lt;ul&gt;</code>, <code>&lt;figure&gt;</code>, and a table with merged cells</li>
            </ul>

            <h3>What we learned:</h3>
            <p>This enhancement taught us about:</p>
            <ul>
                <li>Building secure form workflows using CSRF tokens and server-side validation</li>
                <li>Correctly handling checkbox arrays in HTML/PHP to capture multiple selections</li>
                <li>Maintaining consistency between front-end form design and back-end processing logic</li>
                <li>Using semantic HTML elements to present structured content clearly on an About page</li>
            </ul>

            <h3>How to test it:</h3>
            <ol>
                <li>Open <code>apply.php</code>, fill in all required fields, select a job role, and choose multiple skills.</li>
                <li>Tick the acknowledgement checkbox and submit the form.</li>
                <li>Confirm a success page with an EOI number, or trigger validation by leaving required fields empty.</li>
                <li>Open <code>about.php</code> and verify the group info, contributions list, photo section, and interests table display correctly.</li>
            </ol>
        </section>

        <section class="box">
            <h2 class="section-title">Enhancement 6: Manager Page Integration</h2>
            
            <h3>What it does:</h3>
            <p>
                We implemented a fully integrated manager interface that allows HR managers to securely log in,
                view and manage Expressions of Interest (EOIs), update application statuses, and analyse submitted
                application data. Manager accounts are protected using hashed passwords, ensuring sensitive
                company and applicant data remains secure. Managers can also log out safely, access filtered and
                sorted EOI records, and generate reports or statistics for internal review.
            </p>

            <h3>How it works:</h3>
            <ul>
                <li>
                    Managers authenticate through a secure login system, where credentials are verified against
                    hashed passwords stored in the database using PHP’s password hashing functions.
                </li>
                <li>
                    Session-based authentication ensures only logged-in managers can access protected pages such as
                    the dashboard and individual EOI review pages.
                </li>
                <li>
                    EOIs are retrieved from the database using prepared SQL statements, allowing managers to view,
                    filter, sort, and update application statuses safely.
                </li>
            </ul>

            <h3>Technical Implementation:</h3>
            <p><strong>Files Created/Modified:</strong></p>
            <ul>
                <li><code>login_form.php</code> – Manager login form with validation and error feedback</li>
                <li><code>auth.php</code> – Session handling and access control for protected pages</li>
                <li><code>manage.php</code> – Manager dashboard displaying EOIs with sorting and filtering options</li>
                <li><code>view_eoi.php</code> – Detailed EOI review page with status update functionality</li>
                <li><code>statistics.php</code> – Statistics dashboard showing statistics of all EOIs received</li>
                <li><code>register_manager.php</code> – Registration page for new managers</li>
                <li><code>dashboard_view.php</code> – Presentation layer for the HR manager dashboard, displaying queries, results, status updates, and user feedback</li>
                <li><code>manager.css</code> – Custom styling for manager interface, status badges, and feedback messages</li>
                <li><code>register_manager.php</code> – Registration page for new managers</li>
                <li><code>eoi_queries.php</code> – Backend query library containing reusable, secure database functions for managing EOIs</li>
            </ul>

            <p><strong>Key Features:</strong></p>
            <ul>
                <li>Secure manager authentication using hashed passwords and PHP sessions</li>
                <li>Ability to view full EOI details and update application status (New, Current, Final)</li>
                <li>Clear success and error feedback messages after manager actions</li>
                <li>Sorted and filtered EOI display for efficient application management</li>
                <li>User-friendly interface with clear navigation and action buttons</li>
            </ul>

            <h3>What we learned:</h3>
            <p>This enhancement taught us about:</p>
            <ul>
                <li>Implementing secure authentication systems using sessions and password hashing</li>
                <li>Protecting sensitive management pages from unauthorized access</li>
                <li>Designing manager dashboards that balance security with usability</li>
                <li>Providing clear feedback to improve user confidence and workflow efficiency</li>
            </ul>

            <h3>How to test it:</h3>
            <ol>
                <li>Navigate to the manager login page and log in using valid manager credentials.</li>
                <li>Access the manager dashboard and view the list of submitted EOIs.</li>
                <li>Move around the page and test all the buttons and their functionality</li>
                <li>Open an individual EOI, update its status, and confirm the success message is displayed.</li>
                <li>Log out and verify that protected pages can no longer be accessed.</li>
            </ol>
        </section>



        <section class="box">
            <h2 class="section-title">Additional Improvements</h2>
            
            <h3>Code Modularity:</h3>
            <p>We improved code reusability by extracting common HTML elements (header, navigation, footer) into separate include files that are used across all pages.</p>

            <h3>Database Design:</h3>
            <p>We implemented proper database normalization and used appropriate data types for all fields in our EOI and managers tables.</p>

            <h3>User Experience:</h3>
            <p>We added clear error messages, form validation feedback, and user-friendly confirmation messages throughout the application.</p>

            <h3>Performance Optimization:</h3>
            <p>Efficient CSS selectors, minimal HTTP requests through file consolidation, and optimized database queries for fast page loads.</p>

            <h3>Cross-Browser Compatibility:</h3>
            <p>Tested and verified functionality across modern browsers including Chrome, Firefox, Safari, and Edge.</p>
        </section>

        <section class="box">
            <h2 class="section-title">Group Contribution to Enhancements</h2>
            <dl>
                <dt>Enhancement 1 - Authentication System Development:</dt>
                <dd>Developed by the team collaboratively - including login system, session management, and access control</dd>

                <dt>Enhancement 2 - Database Configuration & EOI Table:</dt>
                <dd>Developed by Omar Osman - settings.php configuration and SQL table design</dd>

                <dt>Enhancement 3 - CSS System:</dt>
                <dd>Developed by team collaboratively - comprehensive styling, animations, and responsive design</dd>

                <dt>Enhancement 4 - Jobs Page & Styling:</dt>
                <dd>Developed by Omar - jobs.html structure and custom CSS integration</dd>

                <dt>Enhancement 5 - Secure Application System + About Page Integration</dt>
                <dd>Developed by Ramy AlCheikh Ali - Implemented a secure job application workflow (apply.php + process_eoi.php) with CSRF protection,
                server-side validation, multi-skill checkbox handling, and created the structured About page (about.php) to present group info and contributions.</dd>

                <dt>Enhancement 6 - Manager access and functionality </dt>
                <dd>Developed by Ahmed - Created a web page for the HR manager to be able to view and access all submitted forms, change their status, delete and more. Implemented a secure login page for
                    the manager ensuring that only managers from the company will be able to access it, and that it can't be accessed through sql injections. and improved upon the index.php</dd>

                <dt>Documentation:</dt>
                <dd>Documented by Ramy AlCheikh Ali in this enhancements page with contributions from all team members</dd>

                <dt>Testing:</dt>
                <dd>Tested by all team members to ensure functionality, security, and usability</dd>
            </dl>
        </section>

    </div>

    <?php include 'footer.inc'; ?>