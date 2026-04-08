<?php
$user = currentUser();
if (!$user) return;
?>

<aside class="sidebar">
    <h3>Menu</h3>

    <?php if ($user['role'] === 'student'): ?>
        <a href="/Uniworksmohinhhoa/student/dashboard.php">Student Dashboard</a>
        <a href="/Uniworksmohinhhoa/student/profile.php">Profile</a>
        <a href="/Uniworksmohinhhoa/student/jobs.php">Jobs</a>
        <a href="/Uniworksmohinhhoa/student/applications.php">Applications</a>
        <a href="/Uniworksmohinhhoa/student/evaluation.php">Evaluation</a>
        <a href="/Uniworksmohinhhoa/student/messages.php">Messages</a>
    <?php elseif ($user['role'] === 'company'): ?>
        <a href="/Uniworksmohinhhoa/company/dashboard.php">Company Dashboard</a>
        <a href="/Uniworksmohinhhoa/company/profile.php">Profile</a>
        <a href="/Uniworksmohinhhoa/company/manage_jobs.php">Manage Jobs</a>
        <a href="/Uniworksmohinhhoa/company/applications.php">Applications</a>
        <a href="/Uniworksmohinhhoa/company/messages.php">Messages</a>
    <?php elseif ($user['role'] === 'admin'): ?>
        <a href="/Uniworksmohinhhoa/admin/dashboard.php">Admin Dashboard</a>
        <a href="/Uniworksmohinhhoa/admin/users.php">Users</a>
        <a href="/Uniworksmohinhhoa/admin/applications.php">Applications</a>
        <a href="/Uniworksmohinhhoa/admin/monitoring.php">Monitoring</a>
        <a href="/Uniworksmohinhhoa/admin/reports.php">Reports</a>
    <?php endif; ?>
</aside>