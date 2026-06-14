<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';

/*
|--------------------------------------------------------------------------
| PREVIEW MODE
|--------------------------------------------------------------------------
| true  = xem giao diện ngay, không cần login/db đủ dữ liệu
| false = chạy thật với session + database
*/
$previewMode = true;

function safeRedirect($path) {
    header("Location: " . $path);
    exit;
}

if ($previewMode) {
    $periods = [
        [
            'id' => 1,
            'name' => 'Summer 2026',
            'start_date' => '2026-06-01',
            'end_date' => '2026-08-31'
        ],
        [
            'id' => 2,
            'name' => 'Fall 2026',
            'start_date' => '2026-09-01',
            'end_date' => '2026-12-31'
        ]
    ];

    $success = null;
    $error = null;
} else {
    if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'company') {
        safeRedirect('../public/login.php');
    }

    $stmt = $pdo->query("SELECT * FROM internship_periods ORDER BY start_date DESC");
    $periods = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $success = function_exists('getFlash') ? getFlash('success') : null;
    $error = function_exists('getFlash') ? getFlash('error') : null;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Job</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="company-shell">
    <aside class="company-sidebar">
        <div>
            <div class="company-brand">
<<<<<<< Updated upstream
                <h2>Uniworks</h2>
                <p>Recruiter Portal</p>
            </div>

            <nav class="company-nav">
                <a href="dashboard.php">Dashboard</a>
                <a href="applications.php">Applicants</a>
                <a class="active" href="manage_job.php">Jobs</a>
                <a href="messages.php">Messages</a>
                <a href="profile.php">Profile</a>
=======
                <div class="company-brand__logo">
                                <?php if (!empty($user['avatar_url'])): ?>
                                    <img src="/Uniworksmohinhhoa/<?= htmlspecialchars($user['avatar_url']) ?>" alt="avatar" style="width:34px;height:34px;min-width:34px;min-height:34px;max-width:34px;max-height:34px;object-fit:cover;border-radius:10px;display:block;">
                                <?php else: ?>
                                    ✦
                                <?php endif; ?>
                            </div>
                <div class="company-brand__text">
                    <h3><?= htmlspecialchars($company['company_name']) ?></h3>
                    <p>Recruiter Portal</p>
                </div>
            </div>

            <nav class="company-nav">
                <a href="/Uniworksmohinhhoa/company/dashboard.php">Dashboard</a>
                <a href="/Uniworksmohinhhoa/company/applications.php">Applicants</a>
                <a href="/Uniworksmohinhhoa/company/manage_job.php" class="active">Jobs</a>
                <a href="/Uniworksmohinhhoa/company/internship_history.php">History</a>
                <a href="/Uniworksmohinhhoa/company/evaluations.php">Evaluations</a>
                <a href="/Uniworksmohinhhoa/company/messages.php">Messages</a>
                <a href="/Uniworksmohinhhoa/company/profile.php">Profile</a>
>>>>>>> Stashed changes
            </nav>
        </div>

        <div class="company-signout">
            <a href="../public/logout.php">Sign Out</a>
        </div>
    </aside>

    <main class="company-main">
        <div class="topbar">
            <div></div>
            <div class="topbar-actions">
                <a class="btn btn-primary" href="manage_jobs.php">View Jobs</a>
            </div>
        </div>

        <?php if (!empty($success)): ?>
            <div class="flash success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="flash error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <h1 class="page-title">Create New Job</h1>
        <p class="page-subtitle">Post a new internship opportunity for students.</p>

        <div class="card">
            <form action="../actions/company/create_job_action.php" method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Internship Period</label>
                        <select name="period_id" required>
                            <option value="">Select period</option>
                            <?php foreach ($periods as $period): ?>
                                <option value="<?php echo htmlspecialchars($period['id']); ?>">
                                    <?php echo htmlspecialchars($period['name'] . ' (' . $period['start_date'] . ' - ' . $period['end_date'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Job Title</label>
                        <input type="text" name="title" required>
                    </div>

                    <div class="form-group">
                        <label>Slots</label>
                        <input type="number" name="slots" min="1" value="1" required>
                    </div>

                    <div class="form-group">
                        <label>Deadline</label>
                        <input type="date" name="deadline" required>
                    </div>

<<<<<<< Updated upstream
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" required>
                            <option value="open">Open</option>
                            <option value="closed">Closed</option>
                        </select>
                    </div>

                    <div class="form-group full">
                        <label>Description</label>
                        <textarea name="description" required></textarea>
                    </div>

                    <div class="form-group full">
                        <label>Requirements</label>
                        <textarea name="requirements"></textarea>
=======
                        <div class="job-create-field job-create-field--full">
                            <label for="deadline">Deadline</label>
                            <input type="date" id="deadline" name="deadline" required
                                   min="<?= date('Y-m-d') ?>">
                        </div>
>>>>>>> Stashed changes
                    </div>
                </div>

                <?php if ($previewMode): ?>
                    <button class="btn btn-primary" type="button">Create Job</button>
                <?php else: ?>
                    <button class="btn btn-primary" type="submit">Create Job</button>
                <?php endif; ?>
            </form>
        </div>
    </main>
</div>
</body>
</html>