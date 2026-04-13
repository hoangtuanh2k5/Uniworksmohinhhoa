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

$periods = $pdo->query("SELECT * FROM internship_periods ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

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
                <h1>Create New Job</h1>
                <p>Post a new internship opportunity for students.</p>
            </div>
        </div>

        <div class="company-form-card">
            <?php if ($flash): ?>
                <div class="flash <?= htmlspecialchars($flash['type']) ?>" style="margin-bottom:18px;">
                    <?= htmlspecialchars($flash['message']) ?>
                </div>
            <?php endif; ?>

            <form action="../actions/company/create_job_action.php" method="POST">
                <div class="company-form-group">
                    <label>Title</label>
                    <input type="text" name="title" class="company-form-control" required>
                </div>

                <div class="company-form-group">
                    <label>Internship Period</label>
                    <select name="period_id" class="company-form-control" required>
                        <option value="">Select period</option>
                        <?php foreach ($periods as $period): ?>
                            <option value="<?= $period['id'] ?>"><?= htmlspecialchars($period['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="company-form-group">
                    <label>Description</label>
                    <textarea name="description" class="company-form-control" required></textarea>
                </div>

                <div class="company-form-group">
                    <label>Requirements</label>
                    <textarea name="requirements" class="company-form-control" required></textarea>
                </div>

                <div class="company-form-group">
                    <label>Slots</label>
                    <input type="number" name="slots" class="company-form-control" min="1" required>
                </div>

                <div class="company-form-group">
                    <label>Deadline</label>
                    <input type="date" name="deadline" class="company-form-control" required>
                </div>

                <button type="submit" class="company-btn">Create Job</button>
            </form>
        </div>
    </main>
</div>

<?php include '../includes/footer.php'; ?>