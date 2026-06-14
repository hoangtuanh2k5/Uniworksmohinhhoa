<?php
require_once '../includes/auth.php';
require_once '../config/db.php';
require_once '../includes/functions.php';
requireCompanyComplete($pdo);

$user = currentUser();
$flash = getFlash();

$stmt = $pdo->prepare("
    SELECT c.*
    FROM companies c
    WHERE c.user_id = ?
");
$stmt->execute([$user['id']]);
$company = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$company) {
    redirect('/Uniworksmohinhhoa/company/profile.php?setup=1');
}

$stmt = $pdo->prepare("SELECT COUNT(*) FROM jobs WHERE company_id = ?");
$stmt->execute([$company['id']]);
$totalJobs = $stmt->fetchColumn();

$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM applications a
    INNER JOIN jobs j ON a.job_id = j.id
    WHERE j.company_id = ? AND a.admin_approved = 1
");
$stmt->execute([$company['id']]);
$totalApplicants = $stmt->fetchColumn();

$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM applications a
    INNER JOIN jobs j ON a.job_id = j.id
    WHERE j.company_id = ?
      AND a.status = 'pending'
      AND a.admin_approved = 1
      AND a.company_approved = 0
");
$stmt->execute([$company['id']]);
$pendingReview = $stmt->fetchColumn();

$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM applications a
    INNER JOIN jobs j ON a.job_id = j.id
    WHERE j.company_id = ?
      AND a.status = 'approved'
      AND a.admin_approved = 1
      AND a.company_approved = 1
");
$stmt->execute([$company['id']]);
$acceptedCandidates = $stmt->fetchColumn();

$stmt = $pdo->prepare("
    SELECT 
        a.id,
        u.full_name,
        s.student_code,
        m.name AS major_name,
        j.title,
        a.status,
        a.admin_approved,
        a.company_approved,
        a.applied_at
    FROM applications a
    INNER JOIN jobs j ON a.job_id = j.id
    INNER JOIN students s ON a.student_id = s.id
    INNER JOIN users u ON s.user_id = u.id
    LEFT JOIN majors m ON s.major_id = m.id
    WHERE j.company_id = ?
    ORDER BY a.id DESC
    LIMIT 5
");
$stmt->execute([$company['id']]);
$recentApplicants = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once '../includes/notifications.php';
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
                <a href="/Uniworksmohinhhoa/company/applications.php">Applicants<?php if(!empty($notif['reports']) && $notif['reports']>0): ?><span class="notif-badge"><?= $notif['reports'] ?></span><?php endif; ?></a>
                <a href="/Uniworksmohinhhoa/company/manage_job.php">Jobs</a>
                <a href="/Uniworksmohinhhoa/company/messages.php">Messages<?php if(!empty($notif['messages']) && $notif['messages']>0): ?><span class="notif-badge"><?= $notif['messages'] ?></span><?php endif; ?></a>
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
                <p>Track your internships and applicant pipeline.</p>
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
                <h4>Active Jobs</h4>
                <strong><?= $totalJobs ?></strong>
            </div>
            <div class="company-stat-card purple">
                <h4>Total Applicants</h4>
                <strong><?= $totalApplicants ?></strong>
            </div>
            <div class="company-stat-card yellow">
                <h4>Pending Review</h4>
                <strong><?= $pendingReview ?></strong>
            </div>
            <div class="company-stat-card purple">
                <h4>Accepted</h4>
                <strong><?= $acceptedCandidates ?></strong>
            </div>
        </section>

        <section class="company-grid-2">
            <div class="company-card">
                <div class="company-card__title">
                    <h3>Recent Applicants</h3>
                    <a href="/Uniworksmohinhhoa/company/applications.php">See all</a>
                </div>

                <?php if (empty($recentApplicants)): ?>
                    <p class="company-muted">No applicants yet.</p>
                <?php else: ?>
                    <table class="company-table">
                        <thead>
                            <tr>
                                <th>Applicant</th>
                                <th>Role</th>
                                <th>Major</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentApplicants as $item): ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['full_name']) ?></td>
                                    <td><?= htmlspecialchars($item['title']) ?></td>
                                    <td><?= htmlspecialchars($item['major_name'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($item['applied_at']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <div class="company-card">
                <h3 style="margin-bottom:8px;">Pipeline Summary</h3>
                <p class="company-muted" style="margin-bottom:18px;">Applications already approved by school are ready for company decision.</p>

                <div class="company-progress-item">
                    <span>Waiting for review</span>
                    <strong><?= $pendingReview ?></strong>
                </div>
                <div class="company-progress-item">
                    <span>Accepted</span>
                    <strong><?= $acceptedCandidates ?></strong>
                </div>
                <div class="company-progress-item">
                    <span>Total jobs</span>
                    <strong><?= $totalJobs ?></strong>
                </div>
            </div>
        </section>
    </main>
</div>

<?php include '../includes/footer.php'; ?>