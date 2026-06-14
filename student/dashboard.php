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

$stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM applications 
    WHERE student_id = ? AND status = 'pending'
");
$stmt->execute([$student['id']]);
$pendingApplications = $stmt->fetchColumn();

$stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM applications 
    WHERE student_id = ? AND status = 'approved' AND admin_approved = 1 AND company_approved = 1
");
$stmt->execute([$student['id']]);
$approvedApplications = $stmt->fetchColumn();

$stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM applications 
    WHERE student_id = ? AND status = 'rejected'
");
$stmt->execute([$student['id']]);
$rejectedApplications = $stmt->fetchColumn();

/* chart 30 ngày / 4 tuần */
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

$weeklyApplications = [3 => 0, 2 => 0, 1 => 0, 0 => 0];
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
        a.status,
        a.admin_approved,
        a.company_approved,
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

function dashboardStatus(array $app): string {
    if ($app['status'] === 'pending' && (int)$app['admin_approved'] === 0) return 'Waiting for school approval';
    if ($app['status'] === 'pending' && (int)$app['admin_approved'] === 1 && (int)$app['company_approved'] === 0) return 'Waiting for company decision';
    if ($app['status'] === 'approved') return 'Approved';
    if ($app['status'] === 'rejected' && (int)$app['admin_approved'] === 0) return 'Rejected by school';
    if ($app['status'] === 'rejected' && (int)$app['admin_approved'] === 1) return 'Rejected by company';
    return ucfirst($app['status']);
}

require_once '../includes/notifications.php';
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
                <a href="/Uniworksmohinhhoa/student/messages.php">Messages<?php if(!empty($notif['messages']) && $notif['messages']>0): ?><span class="notif-badge"><?= $notif['messages'] ?></span><?php endif; ?></a>
                <a href="/Uniworksmohinhhoa/student/profile.php">Profile</a>
                <a href="/Uniworksmohinhhoa/student/report.php">Final Report</a>
                <a href="/Uniworksmohinhhoa/student/evaluation.php">Evaluation<?php if(!empty($notif['evaluations']) && $notif['evaluations']>0): ?><span class="notif-badge"><?= $notif['evaluations'] ?></span><?php endif; ?></a>
            </nav>
        </div>

        <div class="student-sidebar__footer">
            <a href="/Uniworksmohinhhoa/public/logout.php">↩ Logout</a>
        </div>
    </aside>

    <main class="student-main">
        <div class="student-topbar">
            <div>
                <h1>Welcome back, <?= htmlspecialchars($user['full_name']) ?>! 👋</h1>
                <p>Here’s what’s happening with your internship applications.</p>
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
                <h4>Pending</h4>
                <strong><?= $pendingApplications ?></strong>
            </div>
            <div class="student-stat-card yellow">
                <h4>Approved</h4>
                <strong><?= $approvedApplications ?></strong>
            </div>
            <div class="student-stat-card purple">
                <h4>Rejected</h4>
                <strong><?= $rejectedApplications ?></strong>
            </div>
        </section>

        <!-- Application Status full width -->
        <section style="margin-top: 28px;">
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
        </section>

        <!-- Recent Applications xuống dưới -->
        <section style="margin-top: 28px;">
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
                                <p><?= htmlspecialchars(dashboardStatus($item)) ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </main>
</div>

<?php include '../includes/footer.php'; ?>