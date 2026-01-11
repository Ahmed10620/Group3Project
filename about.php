<?php
/**
 * About Us Page
 * Displays team member information and contributions
 * 
 * Part of COS10026 Web Technology Project Part 2
 * ORA TECHNOLOGIES - Group Project
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

    


<?php include 'header.inc'; ?>

<div class="content-container">

    <section class="group-info box">
        <h2 class="section-title">Our Group Information</h2>

        <!-- Nested List (Group name + Student IDs) -->
        <ul>
            <li><strong>Group Name:</strong> ORA
                <ul>
                    <li>Student ID 1: 106203698</li>
                    <li>Student ID 2: 106194145</li>
                    <li>Student ID 3: 106205432</li>
                </ul>
            </li>
        </ul>
    </section>

    <section class="contribution-section box">
        <h2 class="section-title">Members Contribution</h2>

        <!-- Definition List -->
        <dl>
            <dt>Ramy AlCheikh Ali</dt>
            <dd>Contributed to the group by; Creating apply.html, about.html, apply.php, about.php, process_eoi.php and part of the css code, and finally communicated with group members ensuring they are doing their work and no issues are arising. Maintained constant communication with the rest of the team and proper management on jira.</dd>

            <dt>Omar Osman</dt>
            <dd>Contributed by creating the jobs.html page, jobs.php, settings.php, enhancements.php, eoi_table.sql and helped developed the full CSS styling, ensuring the layout, design, and functionality were consistent with the rest of the project. Maintained constant communication with the rest of the team and proper management on jira.</dd>

            <dt>Ahmed Sami Abdelmaksoud</dt>
            <dd>Completed the index.html, manage.php and all the manager parts, header.inc, footer.inc, and did the css for the home page, header and footer and part of the word styling, ensuring the layout, design, and functionality were consistent with the rest of the project. Maintained constant communication with the rest of the team and proper management on jira.</dd>
        </dl>
    </section>

    <section class="photo-section box">
        <h2 class="section-title">Group Photo</h2>

        <!-- Figure Element -->
        <figure>
            <img src="images/group_photo.jpg" alt="Group Photo" class="group-photo">
            <figcaption>Our Team Photo</figcaption>
        </figure>
    </section>

    <section class="interests-section box">
        <h2 class="section-title">Members Interests</h2>

        <!-- Table with merged cells -->
        <table>
            <caption>Group Members Interests</caption>

            <tr>
                <th>Name</th>
                <th colspan="2">Interests</th>
            </tr>

            <tr>
                <td>Ramy AlCheikh Ali</td>
                <td>Loves movies such as Interstellar and the Star Wars Movies</td>
                <td>Loves video games such as Rocket League, The Last of Us, and Rust</td>
            </tr>

            <tr>
                <td>Omar Osman</td>
                <td>Has an interest in playing video games such as Overwatch, Yakuza collection, and PC Building Simulator</td>
                <td>Enjoys watching Arabic comedic movies such as Bittersweet, Zaky Chan, and X-Large</td>
            </tr>

            <tr>
                <td>Ahmed Sami Abdelmaksoud</td>
                <td>Enjoys watching movies and shows, as well as sports such as F1 and Basketball</td>
                <td>Enjoys playing games such Persona, Cyberpunk and Yakuza</td>
            </tr>
        </table>
    </section>

    <section class="extra box">
        <h2 class="section-title">More About Us</h2>
        <p>We are a group of students born and raised in Qatar, where each of us graduated from different high schools across the country. Our shared interest in technology and cybersecurity led us to pursue a Bachelor's degree in Computer Science with a major in Cybersecurity at Barzan University College. Outside of academics, we all enjoy gaming, which is a common hobby that brought us together and continues to strengthen our teamwork and creativity.</p>
    </section>
</div>

<?php include 'footer.inc'; ?>

</body>
</html>