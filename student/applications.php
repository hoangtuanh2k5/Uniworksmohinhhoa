<?php
require_once '../includes/auth.php';
require_once '../config/db.php';
requireRole('student');

$user = currentUser();

$stmt = $pdo->prepare("SELECT id FROM students WHERE user_id = ?");
$stmt->execute([$user['id']]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    redirect('/Uniworksmohinhhoa/student/profile.php?setup=1');
}

$stmt = $pdo->prepare("
    SELECT 
        a.id,
        a.applied_at,
        a.status,
        a.admin_approved,
        a.cv_url,
        j.title,
        c.company_name
    FROM applications a
    INNER JOIN jobs j ON a.job_id = j.id
    INNER JOIN companies c ON j.company_id = c.id
    WHERE a.student_id = ?
    ORDER BY a.id DESC
");
$stmt->execute([$student['id']]);
$applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php';
?>

<div class="student-shell">
    <aside class="student-sidebar">
        <div>
            <div class="student-brand">
                <div class="student-brand__logo">✦</div>
                <div class="student-brand__text">
                    <h3><?= htmlspecialchars($user['full_name']) ?></h3>
                    <p>Aspiring Student</p>
                </div>
            </div>

            <nav class="student-nav">
                <a href="/Uniworksmohinhhoa/student/dashboard.php">Dashboard</a>
                <a href="/Uniworksmohinhhoa/student/applications.php" class="active">Applications</a>
                <a href="/Uniworksmohinhhoa/student/jobs.php">Internships</a>
                <a href="/Uniworksmohinhhoa/student/messages.php">Messages</a>
                <a href="/Uniworksmohinhhoa/student/profile.php">Profile</a>
            </nav>
        </div>

        <div class="student-sidebar__footer">
            <a href="/Uniworksmohinhhoa/public/logout.php">↩ Log out</a>
        </div>
    </aside>

    <main class="student-main">
        <div class="student-topbar">
            <div>
                <h1>Application Status</h1>
                <p>Track all of your submitted applications.</p>
            </div>
        </div>

        <div class="student-card">
            <table class="student-table">
                <thead>
                    <tr>
                        <th>Job</th>
                        <th>Company</th>
                        <th>Status</th>
                        <th>Admin Approved</th>
                        <th>Applied At</th>
                        <th>CV</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($applications as $app): ?>
                        <tr>
                            <td><?= htmlspecialchars($app['title']) ?></td>
                            <td><?= htmlspecialchars($app['company_name']) ?></td>
                            <td>
                                <span class="student-badge <?= htmlspecialchars($app['status']) ?>">
                                    <?= htmlspecialchars($app['status']) ?>
                                </span>
                            </td>
                            <td><?= $app['admin_approved'] ? 'Yes' : 'No' ?></td>
                            <td><?= htmlspecialchars($app['applied_at']) ?></td>
                            <td>
                                <a href="/Uniworksmohinhhoa/<?= htmlspecialchars($app['cv_url']) ?>" target="_blank">View</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<?php include '../includes/footer.php'; ?>