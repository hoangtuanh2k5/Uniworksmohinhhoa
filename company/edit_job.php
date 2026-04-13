<?php
require_once '../includes/auth.php';
require_once '../config/db.php';
require_once '../includes/functions.php';
requireRole('company');

$user = currentUser();
$job_id = (int)($_GET['id'] ?? 0);
$flash = getFlash();

$stmt = $pdo->prepare("SELECT * FROM companies WHERE user_id = ?");
$stmt->execute([$user['id']]);
$company = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$company) {
    redirect('/Uniworksmohinhhoa/company/profile.php?setup=1');
}

$stmt = $pdo->prepare("
    SELECT *
    FROM jobs
    WHERE id = ? AND company_id = ?
");
$stmt->execute([$job_id, $company['id']]);
$job = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$job) {
    redirect('/Uniworksmohinhhoa/company/manage_job.php');
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
                <h1>Edit Job</h1>
                <p>Update this internship posting.</p>
            </div>
        </div>

        <div class="company-form-card">
            <?php if ($flash): ?>
                <div class="flash <?= htmlspecialchars($flash['type']) ?>" style="margin-bottom:18px;">
                    <?= htmlspecialchars($flash['message']) ?>
                </div>
            <?php endif; ?>

            <form action="../actions/company/update_job_action.php" method="POST">
                <input type="hidden" name="job_id" value="<?= $job['id'] ?>">

                <div class="company-form-group">
                    <label>Title</label>
                    <input type="text" name="title" class="company-form-control" value="<?= htmlspecialchars($job['title']) ?>" required>
                </div>

                <div class="company-form-group">
                    <label>Internship Period</label>
                    <select name="period_id" class="company-form-control" required>
                        <?php foreach ($periods as $period): ?>
                            <option value="<?= $period['id'] ?>" <?= $job['period_id'] == $period['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($period['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="company-form-group">
                    <label>Description</label>
                    <textarea name="description" class="company-form-control" required><?= htmlspecialchars($job['description']) ?></textarea>
                </div>

                <div class="company-form-group">
                    <label>Requirements</label>
                    <textarea name="requirements" class="company-form-control" required><?= htmlspecialchars($job['requirements']) ?></textarea>
                </div>

                <div class="company-form-group">
                    <label>Slots</label>
                    <input type="number" name="slots" class="company-form-control" value="<?= htmlspecialchars($job['slots']) ?>" min="1" required>
                </div>

                <div class="company-form-group">
                    <label>Deadline</label>
                    <input type="date" name="deadline" class="company-form-control" value="<?= htmlspecialchars($job['deadline']) ?>" required>
                </div>

                <div class="company-form-group">
                    <label>Status</label>
                    <select name="status" class="company-form-control" required>
                        <option value="open" <?= $job['status'] === 'open' ? 'selected' : '' ?>>open</option>
                        <option value="closed" <?= $job['status'] === 'closed' ? 'selected' : '' ?>>closed</option>
                    </select>
                </div>

                <button type="submit" class="company-btn">Update Job</button>
            </form>
        </div>
    </main>
</div>

<?php include '../includes/footer.php'; ?>