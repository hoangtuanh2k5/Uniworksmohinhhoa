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

$stmt = $pdo->query("
    SELECT 
        j.id,
        j.title,
        j.description,
        j.deadline,
        j.slots,
        c.company_name
    FROM jobs j
    INNER JOIN companies c ON j.company_id = c.id
    WHERE j.status = 'open'
    ORDER BY j.id DESC
");
$jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
                <a href="/Uniworksmohinhhoa/student/applications.php">Applications</a>
                <a href="/Uniworksmohinhhoa/student/jobs.php" class="active">Internships</a>
                <a href="/Uniworksmohinhhoa/student/messages.php">Messages</a>
                <a href="/Uniworksmohinhhoa/student/profile.php">Profile</a>
            </nav>
        </div>

        <div class="student-sidebar__footer">
            <a href="/Uniworksmohinhhoa/public/logout.php">↩ Log Out</a>
        </div>
    </aside>

    <main class="student-main">
        <div class="student-topbar">
            <div>
                <h1>Available Internships</h1>
                <p>Discover and apply to internship opportunities.</p>
            </div>

            <a href="/Uniworksmohinhhoa/student/applications.php" class="student-btn-outline">My Applications</a>
        </div>

        <div class="student-grid-jobs">
            <?php foreach ($jobs as $index => $job): ?>
                <div class="student-job-card <?= $index % 2 === 0 ? 'purple' : 'yellow' ?>">
                    <h3><?= htmlspecialchars($job['title']) ?></h3>
                    <p><?= htmlspecialchars($job['company_name']) ?></p>
                    <p class="student-job-card__meta">Deadline: <?= htmlspecialchars($job['deadline']) ?> • Slots: <?= htmlspecialchars($job['slots']) ?></p>
                    <p><?= htmlspecialchars(substr($job['description'], 0, 120)) ?>...</p>

                    <div class="student-job-card__bottom">
                        <span class="student-job-card__salary">Open</span>
                        <a href="/Uniworksmohinhhoa/student/job_detail.php?id=<?= $job['id'] ?>" class="student-btn">Apply Now</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
</div>

<?php include '../includes/footer.php'; ?>