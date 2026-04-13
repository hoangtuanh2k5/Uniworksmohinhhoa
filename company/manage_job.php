<?php
require_once '../includes/auth.php';
require_once '../config/db.php';
requireRole('company');

$user = currentUser();

$stmt = $pdo->prepare("SELECT * FROM companies WHERE user_id = ?");
$stmt->execute([$user['id']]);
$company = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$company) {
    redirect('/Uniworksmohinhhoa/company/profile.php?setup=1');
}

$stmt = $pdo->prepare("
    SELECT j.*, ip.name AS period_name
    FROM jobs j
    INNER JOIN internship_periods ip ON j.period_id = ip.id
    WHERE j.company_id = ?
    ORDER BY j.id DESC
");
$stmt->execute([$company['id']]);
$jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
                <a href="/Uniworksmohinhhoa/company/applications.php">Applicants</a>
                <a href="/Uniworksmohinhhoa/company/manage_job.php" class="active">Jobs</a>
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
                <h1>Manage Jobs</h1>
                <p>Review and update your internship postings.</p>
            </div>
            <a href="/Uniworksmohinhhoa/company/create_job.php" class="company-btn">+ New Job</a>
        </div>

        <div class="company-card">
            <table class="company-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Period</th>
                        <th>Slots</th>
                        <th>Deadline</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($jobs)): ?>
                        <tr><td colspan="6">No jobs posted yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($jobs as $job): ?>
                            <tr>
                                <td><?= htmlspecialchars($job['title']) ?></td>
                                <td><?= htmlspecialchars($job['period_name']) ?></td>
                                <td><?= htmlspecialchars($job['slots']) ?></td>
                                <td><?= htmlspecialchars($job['deadline']) ?></td>
                                <td><?= htmlspecialchars($job['status']) ?></td>
                                <td class="company-actions">
                                    <a class="company-btn-outline" href="/Uniworksmohinhhoa/company/edit_job.php?id=<?= $job['id'] ?>">Edit</a>
                                    <form action="/Uniworksmohinhhoa/actions/company/delete_job_action.php" method="POST" onsubmit="return confirm('Delete this job?');">
                                        <input type="hidden" name="job_id" value="<?= $job['id'] ?>">
                                        <button type="submit" class="company-btn-outline">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<?php include '../includes/footer.php'; ?>