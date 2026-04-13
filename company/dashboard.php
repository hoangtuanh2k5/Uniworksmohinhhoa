<?php
require_once '../includes/auth.php';
require_once '../config/db.php';
require_once '../includes/functions.php';
requireRole('company');

$user = currentUser();
$flash = getFlash();

$stmt = $pdo->prepare("SELECT * FROM companies WHERE user_id = ?");
$stmt->execute([$user['id']]);
$company = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$company) {
    redirect('/Uniworksmohinhhoa/company/profile.php?setup=1');
}

$stmt = $pdo->prepare("SELECT COUNT(*) FROM jobs WHERE company_id = ? AND status = 'open'");
$stmt->execute([$company['id']]);
$activeJobs = $stmt->fetchColumn();

$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM applications a
    INNER JOIN jobs j ON a.job_id = j.id
    WHERE j.company_id = ?
");
$stmt->execute([$company['id']]);
$totalApplicants = $stmt->fetchColumn();

$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM applications a
    INNER JOIN jobs j ON a.job_id = j.id
    WHERE j.company_id = ? AND a.status = 'pending'
");
$stmt->execute([$company['id']]);
$pendingPipeline = $stmt->fetchColumn();

/* dynamic chart 30 days / 4 weeks */
$stmt = $pdo->prepare("
    SELECT 
        FLOOR(DATEDIFF(CURDATE(), DATE(a.applied_at)) / 7) AS week_offset,
        COUNT(*) AS total
    FROM applications a
    INNER JOIN jobs j ON a.job_id = j.id
    WHERE j.company_id = ?
      AND a.applied_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    GROUP BY FLOOR(DATEDIFF(CURDATE(), DATE(a.applied_at)) / 7)
");
$stmt->execute([$company['id']]);
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
        a.id,
        u.full_name,
        j.title,
        a.status,
        a.applied_at
    FROM applications a
    INNER JOIN students s ON a.student_id = s.id
    INNER JOIN users u ON s.user_id = u.id
    INNER JOIN jobs j ON a.job_id = j.id
    WHERE j.company_id = ?
    ORDER BY a.id DESC
    LIMIT 5
");
$stmt->execute([$company['id']]);
$recentApplicants = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("
    SELECT 
        SUM(CASE WHEN a.status = 'pending' THEN 1 ELSE 0 END) AS pending_total,
        SUM(CASE WHEN a.status = 'approved' THEN 1 ELSE 0 END) AS approved_total,
        SUM(CASE WHEN a.status = 'rejected' THEN 1 ELSE 0 END) AS rejected_total
    FROM applications a
    INNER JOIN jobs j ON a.job_id = j.id
    WHERE j.company_id = ?
");
$stmt->execute([$company['id']]);
$pipeline = $stmt->fetch(PDO::FETCH_ASSOC);

include '../includes/header.php';
?>

<div class="company-shell">
    <aside class="company-sidebar">
        <div>
            <div class="company-brand">
                <div class="company-brand__logo">✦</div>
                <div class="company-brand__text">
                    <h3><?= htmlspecialchars($company['company_name']) ?></h3>
                    <p>Recruiter Portal</p>
                </div>
            </div>

            <nav class="company-nav">
                <a href="/Uniworksmohinhhoa/company/dashboard.php" class="active">Dashboard</a>
                <a href="/Uniworksmohinhhoa/company/applications.php">Applicants</a>
                <a href="/Uniworksmohinhhoa/company/manage_job.php">Jobs</a>
                <a href="/Uniworksmohinhhoa/company/profile.php">Profile</a>
            </nav>
        </div>

        <div class="company-sidebar__footer">
            <a href="/Uniworksmohinhhoa/public/logout.php">↩ Logout</a>
        </div>
    </aside>

    <main class="company-main">
        <div class="company-topbar">
            <div>
                <h1>Recruitment Overview</h1>
                <p>Track your active internships and hiring progress.</p>
            </div>
            <a href="/Uniworksmohinhhoa/company/create_job.php" class="company-btn">+ Post New Job</a>
        </div>

        <?php if ($flash): ?>
            <div class="flash <?= htmlspecialchars($flash['type']) ?>" style="margin-bottom:18px;">
                <?= htmlspecialchars($flash['message']) ?>
            </div>
        <?php endif; ?>

        <section class="company-stats">
            <div class="company-stat-card yellow">
                <h4>Active Internships</h4>
                <strong><?= $activeJobs ?></strong>
            </div>
            <div class="company-stat-card purple">
                <h4>Total Applicants</h4>
                <strong><?= $totalApplicants ?></strong>
            </div>
            <div class="company-stat-card yellow">
                <h4>Hiring Pipeline</h4>
                <strong><?= $pendingPipeline ?></strong>
            </div>
        </section>

        <section class="company-grid-2">
            <div class="company-card">
                <div class="company-card__title">
                    <h3>Application Trends</h3>
                    <span class="company-muted">Last 30 days</span>
                </div>

                <div class="company-chart">
                    <?php
                    $weekLabels = ['WEEK 1', 'WEEK 2', 'WEEK 3', 'WEEK 4'];
                    $weekValues = array_values(array_reverse($weeklyApplications));
                    $weekHeights = array_reverse($chartHeights);
                    ?>
                    <?php for ($i = 0; $i < 4; $i++): ?>
                        <div>
                            <div class="company-chart__bar" style="height: <?= $weekHeights[$i] ?>px;"></div>
                            <div class="company-chart__label"><?= $weekLabels[$i] ?></div>
                            <div class="company-chart__label" style="margin-top:4px; font-size:12px; color:#666;">
                                <?= $weekValues[$i] ?>
                            </div>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>

            <div class="company-card">
                <div class="company-card__title">
                    <h3>Pipeline Breakdown</h3>
                </div>

                <div class="company-list">
                    <div class="company-list__item">
                        <p>Pending <strong style="float:right;"><?= (int)($pipeline['pending_total'] ?? 0) ?></strong></p>
                        <div class="company-progress"><span style="width: <?= max(5, ((int)($pipeline['pending_total'] ?? 0)) * 10) ?>%;"></span></div>
                    </div>
                    <div class="company-list__item">
                        <p>Approved <strong style="float:right;"><?= (int)($pipeline['approved_total'] ?? 0) ?></strong></p>
                        <div class="company-progress"><span style="width: <?= max(5, ((int)($pipeline['approved_total'] ?? 0)) * 10) ?>%;"></span></div>
                    </div>
                    <div class="company-list__item">
                        <p>Rejected <strong style="float:right;"><?= (int)($pipeline['rejected_total'] ?? 0) ?></strong></p>
                        <div class="company-progress"><span style="width: <?= max(5, ((int)($pipeline['rejected_total'] ?? 0)) * 10) ?>%;"></span></div>
                    </div>
                </div>
            </div>
        </section>

        <section class="company-card">
            <div class="company-card__title">
                <h3>Recent Applicants</h3>
                <a href="/Uniworksmohinhhoa/company/applications.php" class="company-muted">See all</a>
            </div>

            <table class="company-table">
                <thead>
                    <tr>
                        <th>Applicant</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Date Applied</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentApplicants)): ?>
                        <tr><td colspan="4">No applicants yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($recentApplicants as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['full_name']) ?></td>
                                <td><?= htmlspecialchars($item['title']) ?></td>
                                <td>
                                    <span class="company-badge <?= htmlspecialchars($item['status']) ?>">
                                        <?= htmlspecialchars($item['status']) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($item['applied_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </main>
</div>

<?php include '../includes/footer.php'; ?>