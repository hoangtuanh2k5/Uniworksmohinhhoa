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
    $data = [
        'application_id' => 1,
        'status' => 'approved',
        'student_code' => 'SE001',
        'student_name' => 'Thị Trâm Nguyễn Kiều',
        'job_title' => 'Software Engineer Intern',
        'registration_id' => 1
    ];

    $evaluation = [
        'score' => '92.50',
        'feedback' => "Strong technical foundation and good communication.\nShows initiative and learns quickly."
    ];

    $success = null;
    $error = null;
} else {
    if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'company') {
        safeRedirect('../public/login.php');
    }

    $user = $_SESSION['user'];
    $appId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if ($appId <= 0) {
        if (function_exists('setFlash')) {
            setFlash('error', 'Invalid application id.');
        }
        safeRedirect('applications.php');
    }

    $stmt = $pdo->prepare("SELECT * FROM companies WHERE user_id = ?");
    $stmt->execute([$user['id']]);
    $company = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$company) {
        if (function_exists('setFlash')) {
            setFlash('error', 'Company profile not found.');
        }
        safeRedirect('../public/login.php');
    }

    $stmt = $pdo->prepare("
        SELECT a.id AS application_id, a.status,
               s.student_code,
               u.full_name AS student_name,
               j.title AS job_title,
               ir.id AS registration_id
        FROM applications a
        JOIN students s ON a.student_id = s.id
        JOIN users u ON s.user_id = u.id
        JOIN jobs j ON a.job_id = j.id
        LEFT JOIN internship_registrations ir ON ir.application_id = a.id
        WHERE a.id = ? AND j.company_id = ?
    ");
    $stmt->execute([$appId, $company['id']]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$data) {
        if (function_exists('setFlash')) {
            setFlash('error', 'Application not found.');
        }
        safeRedirect('applications.php');
    }

    $evaluation = null;
    if (!empty($data['registration_id'])) {
        $stmt = $pdo->prepare("
            SELECT *
            FROM evaluations
            WHERE registration_id = ? AND evaluator_role = 'company'
            LIMIT 1
        ");
        $stmt->execute([$data['registration_id']]);
        $evaluation = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    $success = function_exists('getFlash') ? getFlash('success') : null;
    $error = function_exists('getFlash') ? getFlash('error') : null;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Evaluate Candidate</title>
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
                <a class="active" href="applications.php">Applicants</a>
                <a href="manage_job.php">Jobs</a>
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
                <a href="/Uniworksmohinhhoa/company/applications.php" class="active">Applicants</a>
                <a href="/Uniworksmohinhhoa/company/manage_jobs.php">Jobs</a>
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
                <a class="btn btn-primary" href="applications.php">Back to Applicants</a>
            </div>
        </div>

        <?php if (!empty($success)): ?>
            <div class="flash success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="flash error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <h1 class="page-title">Evaluate Candidate</h1>
        <p class="page-subtitle">Write your assessment for this student.</p>

        <div class="card">
            <p style="margin-bottom:10px;"><strong>Student:</strong> <?php echo htmlspecialchars($data['student_name']); ?></p>
            <p style="margin-bottom:10px;"><strong>Student Code:</strong> <?php echo htmlspecialchars($data['student_code']); ?></p>
            <p style="margin-bottom:18px;"><strong>Position:</strong> <?php echo htmlspecialchars($data['job_title']); ?></p>

            <?php if (empty($data['registration_id'])): ?>
                <div class="flash error">This candidate cannot be evaluated yet. Please approve the application first.</div>
            <?php else: ?>
                <form action="../actions/company/evaluate_action.php" method="POST">
                    <input type="hidden" name="registration_id" value="<?php echo htmlspecialchars($data['registration_id']); ?>">
                    <input type="hidden" name="application_id" value="<?php echo htmlspecialchars($data['application_id']); ?>">

                    <div class="form-grid">
                        <div class="form-group">
                            <label>Score</label>
                            <input
                                type="number"
                                name="score"
                                min="0"
                                max="100"
                                step="0.01"
                                value="<?php echo htmlspecialchars($evaluation['score'] ?? ''); ?>"
                                required
                            >
                        </div>

                        <div class="form-group full">
                            <label>Feedback</label>
                            <textarea name="feedback" required><?php echo htmlspecialchars($evaluation['feedback'] ?? ''); ?></textarea>
                        </div>
                    </div>

                    <?php if ($previewMode): ?>
                        <button class="btn btn-primary" type="button">Save Evaluation</button>
                    <?php else: ?>
                        <button class="btn btn-primary" type="submit">Save Evaluation</button>
                    <?php endif; ?>
                </form>
            <?php endif; ?>
<<<<<<< Updated upstream
=======

            <div class="company-eval-layout">
                <section class="company-eval-card">
                    <h2>Student & Internship Information</h2>

                    <div class="company-eval-info">
                        <div class="company-eval-box">
                            <span>Student</span>
                            <strong><?= htmlspecialchars($item['student_name']) ?></strong>
                        </div>

                        <div class="company-eval-box">
                            <span>Student Code</span>
                            <strong><?= htmlspecialchars($item['student_code'] ?: 'Updating') ?></strong>
                        </div>

                        <div class="company-eval-box">
                            <span>Class</span>
                            <strong><?= htmlspecialchars($item['class_name'] ?: 'Updating') ?></strong>
                        </div>

                        <div class="company-eval-box">
                            <span>GPA</span>
                            <strong><?= htmlspecialchars(number_format((float)$item['gpa'], 2)) ?></strong>
                        </div>

                        <div class="company-eval-box">
                            <span>Job</span>
                            <strong><?= htmlspecialchars($item['job_title']) ?></strong>
                        </div>

                        <div class="company-eval-box">
                            <span>Internship Status</span>
                            <strong>
                                <span class="company-eval-badge <?= in_array($item['internship_status'], ['ongoing','completed']) ? htmlspecialchars($item['internship_status']) : 'default' ?>">
                                    <?= htmlspecialchars(ucfirst($item['internship_status'])) ?>
                                </span>
                            </strong>
                        </div>

                        <div class="company-eval-box">
                            <span>Start Date</span>
                            <strong><?= htmlspecialchars($item['start_date']) ?></strong>
                        </div>

                        <div class="company-eval-box">
                            <span>End Date</span>
                            <strong><?= htmlspecialchars($item['end_date']) ?></strong>
                        </div>
                    </div>
                </section>

                <aside class="company-eval-note">
                    <h3>Evaluation Rule</h3>
                    <p>
                        The company can only submit an evaluation after the internship status becomes <strong>completed</strong>. Each internship registration can only have one evaluation record.
                    </p>
                </aside>
            </div>

            <div style="height:24px;"></div>

            <div class="company-eval-card">
                <h2>Evaluation Form</h2>

                <?php if (!empty($item['evaluation_id'])): ?>
                    <div class="company-eval-note" style="background:#f6f2ff; border-color:#e8ddff; margin-bottom:18px;">
                        <h3>Evaluation Submitted</h3>
                        <p>You have already submitted the evaluation for this student. You can review it below.</p>
                    </div>

                    <div class="company-eval-readonly">
                        <p><strong>Score:</strong> <?= htmlspecialchars($item['score']) ?>/10</p>
                        <p><strong>Feedback:</strong><br><?= nl2br(htmlspecialchars($item['feedback'])) ?></p>
                    </div>

                    <div class="company-eval-meta">
                        Submitted at: <?= htmlspecialchars($item['created_at']) ?>
                    </div>

                    <div class="company-eval-actions" style="margin-top:20px;">
                        <a href="/Uniworksmohinhhoa/company/applications.php" class="company-eval-btn ghost">Back</a>
                    </div>
                <?php elseif ($item['internship_status'] !== 'completed'): ?>
                    <div class="company-eval-note" style="background:#f6f2ff; border-color:#e8ddff;">
                        <h3>Internship Not Completed Yet</h3>
                        <p>You can only submit the evaluation after the internship has been marked as <strong>completed</strong>.</p>
                    </div>

                    <div class="company-eval-actions" style="margin-top:20px;">
                        <a href="/Uniworksmohinhhoa/company/applications.php" class="company-eval-btn ghost">Back</a>
                    </div>
                <?php else: ?>
                    <form action="/Uniworksmohinhhoa/actions/company/evaluate_action.php" method="POST" enctype="multipart/form-data" class="company-eval-form">
                        <input type="hidden" name="registration_id" value="<?= $item['registration_id'] ?>">

                        <div>
                            <label for="score">Score (0 - 10)</label>
                            <input type="number" id="score" name="score" min="0" max="10" step="0.1" required>
                        </div>

                        <div>
                            <label for="feedback">Feedback</label>
                            <textarea id="feedback" name="feedback" placeholder="Write your evaluation for the student..." required></textarea>
                        </div>

                        <div>
                            <label for="eval_file">Attach Evaluation File <span style="font-weight:400;color:#8a8fa3;">(PDF / DOC / DOCX / JPG / PNG, max 10MB — optional)</span></label>
                            <input type="file" id="eval_file" name="eval_file"
                                   accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                                   style="width:100%;border:1.5px solid #ddd9ef;border-radius:14px;padding:12px 16px;font-size:15px;background:#fff;outline:none;box-sizing:border-box;">
                        </div>

                        <div class="company-eval-actions">
                            <a href="/Uniworksmohinhhoa/company/evaluations.php" class="company-eval-btn ghost">Back</a>
                            <button type="submit" class="company-eval-btn primary">Submit Evaluation</button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
>>>>>>> Stashed changes
        </div>
    </main>
</div>
</body>
</html>