<?php
require_once '../includes/auth.php';
require_once '../config/db.php';
require_once '../includes/functions.php';
requireRole('company');

$user = currentUser();
$app_id = (int)($_GET['id'] ?? 0);
$flash = getFlash();

$stmt = $pdo->prepare("SELECT * FROM companies WHERE user_id = ?");
$stmt->execute([$user['id']]);
$company = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$company) {
    redirect('/Uniworksmohinhhoa/company/profile.php?setup=1');
}

$stmt = $pdo->prepare("
    SELECT 
        a.*,
        u.id AS user_id,
        u.full_name,
        u.email,
        s.student_code,
        s.class_name,
        s.gpa,
        m.name AS major_name,
        j.title
    FROM applications a
    INNER JOIN students s ON a.student_id = s.id
    INNER JOIN users u ON s.user_id = u.id
    LEFT JOIN majors m ON s.major_id = m.id
    INNER JOIN jobs j ON a.job_id = j.id
    WHERE a.id = ?
      AND j.company_id = ?
");
$stmt->execute([$app_id, $company['id']]);
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
        <?php if ($flash): ?>
            <div class="flash <?= htmlspecialchars($flash['type']) ?>" style="margin-bottom:18px;">
                <?= htmlspecialchars($flash['message']) ?>
            </div>
        <?php endif; ?>

        <div class="company-detail-grid">
            <section class="company-card">
                <div class="company-card__title">
                    <h3><?= htmlspecialchars($app['full_name']) ?></h3>
                    <span class="company-badge <?= htmlspecialchars($app['status']) ?>"><?= htmlspecialchars($app['status']) ?></span>
                </div>

                <p><strong>Email:</strong> <?= htmlspecialchars($app['email']) ?></p>
                <p><strong>Student Code:</strong> <?= htmlspecialchars($app['student_code']) ?></p>
                <p><strong>Major:</strong> <?= htmlspecialchars($app['major_name'] ?? '') ?></p>
                <p><strong>Class:</strong> <?= htmlspecialchars($app['class_name'] ?? '') ?></p>
                <p><strong>GPA:</strong> <?= htmlspecialchars($app['gpa'] ?? '') ?></p>
                <p><strong>Applied for:</strong> <?= htmlspecialchars($app['title']) ?></p>
                <p><strong>Date Applied:</strong> <?= htmlspecialchars($app['applied_at']) ?></p>

                <div style="margin-top:18px;">
                    <a class="company-btn-outline" href="/Uniworksmohinhhoa/<?= htmlspecialchars($app['cv_url']) ?>" target="_blank">View CV</a>
                </div>
            </section>

            <aside class="company-card">
                <h3 style="margin-bottom:14px;">Review Actions</h3>

                <form action="/Uniworksmohinhhoa/actions/company/review_application_action.php" method="POST" style="margin-bottom:12px;">
                    <input type="hidden" name="application_id" value="<?= $app['id'] ?>">
                    <input type="hidden" name="status" value="approved">
                    <button type="submit" class="company-btn" style="width:100%;">Approve</button>
                </form>

                <form action="/Uniworksmohinhhoa/actions/company/review_application_action.php" method="POST" style="margin-bottom:12px;">
                    <input type="hidden" name="application_id" value="<?= $app['id'] ?>">
                    <input type="hidden" name="status" value="rejected">
                    <button type="submit" class="company-btn-outline" style="width:100%;">Reject</button>
                </form>

                <a href="/Uniworksmohinhhoa/company/evaluate.php?application_id=<?= $app['id'] ?>" class="company-btn-outline" style="width:100%;">Evaluate Student</a>
            </aside>
        </div>
    </main>
</div>

<?php include '../includes/footer.php'; ?>