<?php
require_once '../includes/auth.php';
require_once '../config/db.php';
require_once '../includes/functions.php';
requireRole('student');

$user = currentUser();
$flash = getFlash();

$stmt = $pdo->prepare("SELECT * FROM students WHERE user_id = ?");
$stmt->execute([$user['id']]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    redirect('/Uniworksmohinhhoa/student/profile.php?setup=1');
}

$stmt = $pdo->prepare("SELECT COUNT(*) FROM applications WHERE student_id = ?");
$stmt->execute([$student['id']]);
$totalApplied = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM applications WHERE student_id = ? AND status = 'pending'");
$stmt->execute([$student['id']]);
$inReview = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM applications WHERE student_id = ? AND status = 'approved'");
$stmt->execute([$student['id']]);
$offers = $stmt->fetchColumn();
$stmt = $pdo->prepare("
    SELECT 
        FLOOR(DATEDIFF(CURDATE(), DATE(applied_at)) / 7) AS week_offset,
        COUNT(*) AS total
    FROM applications
    WHERE student_id = ?
      AND applied_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    GROUP BY FLOOR(DATEDIFF(CURDATE(), DATE(applied_at)) / 7)
");
$stmt->execute([$student['id']]);
$weeklyRaw = $stmt->fetchAll(PDO::FETCH_ASSOC);

$weeklyApplications = [
    3 => 0,
    2 => 0,
    1 => 0,
    0 => 0
];

foreach ($weeklyRaw as $row) {
    $offset = (int)$row['week_offset'];
    if ($offset >= 0 && $offset <= 3) {
        $weeklyApplications[$offset] = (int)$row['total'];
    }
}

$maxWeekly = max($weeklyApplications);
if ($maxWeekly < 1) {
    $maxWeekly = 1;
}

$chartHeights = [];
foreach ($weeklyApplications as $count) {
    $chartHeights[] = 60 + (($count / $maxWeekly) * 120);
}
$stmt = $pdo->prepare("
    SELECT 
        a.applied_at,
        j.title,
        c.company_name
    FROM applications a
    INNER JOIN jobs j ON a.job_id = j.id
    INNER JOIN companies c ON j.company_id = c.id
    WHERE a.student_id = ?
    ORDER BY a.id DESC
    LIMIT 4
");
$stmt->execute([$student['id']]);
$recentApplications = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->query("
    SELECT 
        j.id,
        j.title,
        c.company_name,
        j.description,
        j.deadline
    FROM jobs j
    INNER JOIN companies c ON j.company_id = c.id
    WHERE j.status = 'open'
    ORDER BY j.id DESC
    LIMIT 2
");
$recommendedJobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
                <a href="/Uniworksmohinhhoa/student/dashboard.php" class="active">Dashboard</a>
                <a href="/Uniworksmohinhhoa/student/applications.php">Applications</a>
                <a href="/Uniworksmohinhhoa/student/jobs.php">Internships</a>
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
                <h1>Welcome back, <?= htmlspecialchars($user['full_name']) ?>! 👋</h1>
                <p>Here’s what’s happening with your applications today.</p>
            </div>

            <a href="/Uniworksmohinhhoa/student/jobs.php" class="student-btn">+ New Application</a>
        </div>

        <?php if ($flash): ?>
            <div class="flash <?= htmlspecialchars($flash['type']) ?>" style="margin-bottom:18px;">
                <?= htmlspecialchars($flash['message']) ?>
            </div>
        <?php endif; ?>

       <section class="student-stats">
    <div class="student-stat-card yellow">
        <h4>Total Applied</h4>
        <strong><?= $totalApplied ?></strong>
    </div>
    <div class="student-stat-card purple">
        <h4>In Review</h4>
        <strong><?= $inReview ?></strong>
    </div>
    <div class="student-stat-card yellow">
        <h4>Offers</h4>
        <strong><?= $offers ?></strong>
    </div>
</section>

        <section class="student-grid-2">
            <div class="student-card">
                <div class="student-card__title">
                    <h3>Application Status</h3>
                    <span class="student-muted">Last 30 days</span>
                </div>

               <div class="student-chart">
    <?php
    $weekLabels = ['WEEK 1', 'WEEK 2', 'WEEK 3', 'WEEK 4'];
    $weekValues = array_values(array_reverse($weeklyApplications));
    $weekHeights = array_reverse($chartHeights);
    ?>

    <?php for ($i = 0; $i < 4; $i++): ?>
        <div>
            <div class="student-chart__bar" style="height: <?= $weekHeights[$i] ?>px;"></div>
            <div class="student-chart__label"><?= $weekLabels[$i] ?></div>
            <div class="student-chart__label" style="margin-top:4px; font-size:12px; color:#666;">
                <?= $weekValues[$i] ?>
            </div>
        </div>
    <?php endfor; ?>
</div>
            </div>

            <div class="student-card">
                <div class="student-card__title">
                    <h3>Recent Applications</h3>
                </div>

                <div class="student-list">
                    <?php if (empty($recentApplications)): ?>
                        <p class="student-muted">No applications yet.</p>
                    <?php else: ?>
                        <?php foreach ($recentApplications as $item): ?>
                            <div class="student-list__item">
                                <h4><?= htmlspecialchars($item['title']) ?></h4>
                                <p><?= htmlspecialchars($item['company_name']) ?></p>
                                <p><?= htmlspecialchars($item['applied_at']) ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <section class="student-grid-2">
            <div class="student-card">
                <div class="student-card__title">
                    <h3>Recommended for You</h3>
                    <a href="/Uniworksmohinhhoa/student/jobs.php" class="student-muted">See all</a>
                </div>

                <div class="student-grid-jobs">
                    <?php foreach ($recommendedJobs as $index => $job): ?>
                        <div class="student-job-card <?= $index % 2 === 0 ? 'purple' : 'yellow' ?>">
                            <h3><?= htmlspecialchars($job['title']) ?></h3>
                            <p><?= htmlspecialchars($job['company_name']) ?></p>
                            <p class="student-job-card__meta">Deadline: <?= htmlspecialchars($job['deadline']) ?></p>
                            <p><?= htmlspecialchars(substr($job['description'], 0, 90)) ?>...</p>

                            <div class="student-job-card__bottom">
                                <span class="student-job-card__salary">Apply</span>
                                <a href="/Uniworksmohinhhoa/student/job_detail.php?id=<?= $job['id'] ?>" class="student-btn-outline">View</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="student-card" style="background:#30208f; color:#fff;">
                <div class="student-card__title">
                    <h3 style="color:#fff;">Profile Strength</h3>
                </div>
                <p style="color:#d8d3ff; margin-bottom:20px;">Complete your profile to get more internship matches.</p>
                <strong style="font-size:34px; display:block; margin-bottom:14px;">85%</strong>
                <div style="height:8px; background:rgba(255,255,255,0.25); border-radius:999px; overflow:hidden; margin-bottom:18px;">
                    <div style="width:85%; height:100%; background:#fff;"></div>
                </div>
                <a href="/Uniworksmohinhhoa/student/profile.php" class="student-btn-outline" style="background:#fff;">Improve Profile</a>
            </div>
        </section>
    </main>
</div>

<?php include '../includes/footer.php'; ?>