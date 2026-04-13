<?php
require_once '../includes/auth.php';
require_once '../config/db.php';
require_once '../includes/functions.php';
requireRole('company');

$user = currentUser();
$application_id = (int)($_GET['application_id'] ?? 0);
$flash = getFlash();

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
        u.full_name,
        j.title
    FROM applications a
    INNER JOIN students s ON a.student_id = s.id
    INNER JOIN users u ON s.user_id = u.id
    INNER JOIN jobs j ON a.job_id = j.id
    WHERE a.id = ? AND j.company_id = ?
");
$stmt->execute([$application_id, $company['id']]);
$app = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$app) {
    redirect('/Uniworksmohinhhoa/company/applications.php');
}

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
                <h1>Evaluate Student</h1>
                <p>Give feedback for <?= htmlspecialchars($app['full_name']) ?>.</p>
            </div>
        </div>

        <div class="company-form-card">
            <?php if ($flash): ?>
                <div class="flash <?= htmlspecialchars($flash['type']) ?>" style="margin-bottom:18px;">
                    <?= htmlspecialchars($flash['message']) ?>
                </div>
            <?php endif; ?>

            <p style="margin-bottom:10px;"><strong>Student:</strong> <?= htmlspecialchars($app['full_name']) ?></p>
            <p style="margin-bottom:20px;"><strong>Job:</strong> <?= htmlspecialchars($app['title']) ?></p>

            <form action="../actions/company/evaluate_action.php" method="POST">
                <input type="hidden" name="application_id" value="<?= $app['id'] ?>">

                <div class="company-form-group">
                    <label>Score</label>
                    <input type="number" name="score" class="company-form-control" min="0" max="10" step="0.1" required>
                </div>

                <div class="company-form-group">
                    <label>Feedback</label>
                    <textarea name="feedback" class="company-form-control" required></textarea>
                </div>

                <button type="submit" class="company-btn">Submit Evaluation</button>
            </form>
        </div>
    </main>
</div>

<?php include '../includes/footer.php'; ?>