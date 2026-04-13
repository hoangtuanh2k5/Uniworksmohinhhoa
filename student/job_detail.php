<?php
require_once '../includes/auth.php';
require_once '../config/db.php';
require_once '../includes/functions.php';
requireRole('student');

$user = currentUser();
$job_id = (int)($_GET['id'] ?? 0);
$flash = getFlash();

if ($job_id <= 0) {
    redirect('/Uniworksmohinhhoa/student/jobs.php');
}

$stmt = $pdo->prepare("
    SELECT 
        j.*,
        c.company_name,
        c.address,
        c.website,
        c.industry_type,
        ip.name AS period_name
    FROM jobs j
    INNER JOIN companies c ON j.company_id = c.id
    INNER JOIN internship_periods ip ON j.period_id = ip.id
    WHERE j.id = ?
");
$stmt->execute([$job_id]);
$job = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$job) {
    redirect('/Uniworksmohinhhoa/student/jobs.php');
}

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
        <?php if ($flash): ?>
            <div class="flash <?= htmlspecialchars($flash['type']) ?>" style="margin-bottom:18px;">
                <?= htmlspecialchars($flash['message']) ?>
            </div>
        <?php endif; ?>

        <div class="student-detail-grid">
            <section class="student-detail-section">
                <h2><?= htmlspecialchars($job['title']) ?></h2>
                <p><strong>Company:</strong> <?= htmlspecialchars($job['company_name']) ?></p>
                <p><strong>Industry:</strong> <?= htmlspecialchars($job['industry_type']) ?></p>
                <p><strong>Internship Period:</strong> <?= htmlspecialchars($job['period_name']) ?></p>
                <p><strong>Deadline:</strong> <?= htmlspecialchars($job['deadline']) ?></p>
                <p><strong>Slots:</strong> <?= htmlspecialchars($job['slots']) ?></p>

                <div class="student-apply-box">
                    <a href="/Uniworksmohinhhoa/student/apply.php?job_id=<?= $job['id'] ?>" class="student-btn">Apply Now</a>
                    <a href="/Uniworksmohinhhoa/student/messages.php" class="student-btn-outline">Message Recruiter</a>
                </div>

                <hr style="margin:20px 0; border:none; border-top:1px solid #efeff5;">

                <h3>Job Description</h3>
                <p><?= nl2br(htmlspecialchars($job['description'])) ?></p>

                <h3 style="margin-top:20px;">Requirements</h3>
                <p><?= nl2br(htmlspecialchars($job['requirements'])) ?></p>
            </section>

            <aside class="student-detail-section">
                <h3>Company</h3>
                <p><strong>Name:</strong> <?= htmlspecialchars($job['company_name']) ?></p>
                <p><strong>Website:</strong> <?= htmlspecialchars($job['website']) ?></p>
                <p><strong>Address:</strong> <?= htmlspecialchars($job['address']) ?></p>
            </aside>
        </div>
    </main>
</div>

<?php include '../includes/footer.php'; ?>