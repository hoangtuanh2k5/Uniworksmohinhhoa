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
    SELECT 
        a.id,
        a.status,
        a.applied_at,
        a.cv_url,
        u.full_name,
        s.student_code,
        s.gpa,
        j.title
    FROM applications a
    INNER JOIN students s ON a.student_id = s.id
    INNER JOIN users u ON s.user_id = u.id
    INNER JOIN jobs j ON a.job_id = j.id
    WHERE j.company_id = ?
    ORDER BY a.id DESC
");
$stmt->execute([$company['id']]);
$applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
                <a href="/Uniworksmohinhhoa/company/applications.php" class="active">Applicants</a>
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
                <h1>Student Applicants</h1>
                <p>Review and manage submitted applications.</p>
            </div>
        </div>

        <div class="company-stats">
            <div class="company-stat-card purple">
                <h4>Total Applications</h4>
                <strong><?= count($applications) ?></strong>
            </div>
            <div class="company-stat-card yellow">
                <h4>Pending</h4>
                <strong><?= count(array_filter($applications, fn($a) => $a['status'] === 'pending')) ?></strong>
            </div>
            <div class="company-stat-card purple">
                <h4>Approved</h4>
                <strong><?= count(array_filter($applications, fn($a) => $a['status'] === 'approved')) ?></strong>
            </div>
        </div>

        <div class="company-card">
            <table class="company-table">
                <thead>
                    <tr>
                        <th>Applicant</th>
                        <th>Student Code</th>
                        <th>Job</th>
                        <th>GPA</th>
                        <th>Status</th>
                        <th>Date Applied</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($applications)): ?>
                        <tr><td colspan="7">No applications yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($applications as $app): ?>
                            <tr>
                                <td><?= htmlspecialchars($app['full_name']) ?></td>
                                <td><?= htmlspecialchars($app['student_code']) ?></td>
                                <td><?= htmlspecialchars($app['title']) ?></td>
                                <td><?= htmlspecialchars($app['gpa']) ?></td>
                                <td>
                                    <span class="company-badge <?= htmlspecialchars($app['status']) ?>">
                                        <?= htmlspecialchars($app['status']) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($app['applied_at']) ?></td>
                                <td class="company-actions">
                                    <a class="company-btn-outline" href="/Uniworksmohinhhoa/company/candidate_detail.php?id=<?= $app['id'] ?>">View Profile</a>
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