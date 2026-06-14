<?php
require_once '../includes/auth.php';
require_once '../config/db.php';
requireCompanyComplete($pdo);

$user = currentUser();

$stmt = $pdo->prepare("SELECT id, company_name FROM companies WHERE user_id = ?");
$stmt->execute([$user['id']]);
$company = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$company) {
    redirect('/Uniworksmohinhhoa/company/profile.php?setup=1');
}

// Auto-close jobs past deadline
$pdo->prepare("UPDATE jobs SET status = 'closed' WHERE status = 'open' AND deadline < CURDATE() AND company_id = ?")
    ->execute([$company['id']]);

$stmt = $pdo->prepare("
    SELECT j.*, ip.name AS period_name
    FROM jobs j
    INNER JOIN internship_periods ip ON j.period_id = ip.id
    WHERE j.company_id = ?
    ORDER BY j.id DESC
");
$stmt->execute([$company['id']]);
$jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
                <a href="/Uniworksmohinhhoa/company/dashboard.php">Dashboard</a>
                <a href="/Uniworksmohinhhoa/company/applications.php">Applicants<?php if(!empty($notif['reports']) && $notif['reports']>0): ?><span class="notif-badge"><?= $notif['reports'] ?></span><?php endif; ?></a>
                <a href="/Uniworksmohinhhoa/company/manage_job.php" class="active">Jobs</a>
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
                <h1>Manage Job Posts</h1>
                <p>Create, edit, and manage your internship posts.</p>
            </div>
            <a href="/Uniworksmohinhhoa/company/create_job.php" class="company-btn">+ Post New Job</a>
        </div>

        <div class="company-card">
            <?php if (empty($jobs)): ?>
                <p class="company-muted">No jobs posted yet.</p>
            <?php else: ?>
                <table class="company-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Period</th>
                            <th>Deadline</th>
                            <th>Slots</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($jobs as $job): ?>
                            <tr>
                                <td><?= htmlspecialchars($job['title']) ?></td>
                                <td><?= htmlspecialchars($job['period_name']) ?></td>
                                <td><?= htmlspecialchars($job['deadline']) ?></td>
                                <td><?= htmlspecialchars($job['slots']) ?></td>
                                <td><?= htmlspecialchars($job['status']) ?></td>
                                <td>
                                    <a href="/Uniworksmohinhhoa/company/edit_job.php?id=<?= $job['id'] ?>">Edit</a> |
                                    <a href="/Uniworksmohinhhoa/actions/company/delete_job_action.php?id=<?= $job['id'] ?>" onclick="return confirm('Delete this job?')">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php include '../includes/footer.php'; ?>