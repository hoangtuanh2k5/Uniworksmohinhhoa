<?php
require_once '../includes/auth.php';
require_once '../config/db.php';
require_once '../includes/functions.php';
requireRole('student');

$user = currentUser();
$job_id = (int)($_GET['job_id'] ?? 0);
$flash = getFlash();

if ($job_id <= 0) {
    redirect('/Uniworksmohinhhoa/student/jobs.php');
}

$stmt = $pdo->prepare("SELECT id FROM students WHERE user_id = ?");
$stmt->execute([$user['id']]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    redirect('/Uniworksmohinhhoa/student/profile.php?setup=1');
}

// Kiểm tra đã hoàn thành internship chưa
$stmtDone = $pdo->prepare("
    SELECT ir.id FROM internship_registrations ir
    INNER JOIN applications a ON ir.application_id = a.id
    WHERE a.student_id = ? AND ir.status = 'completed'
    LIMIT 1
");
$stmtDone->execute([$student['id']]);
$alreadyCompleted = $stmtDone->fetch();

$stmt = $pdo->prepare("
    SELECT 
        j.id,
        j.title,
        c.company_name
    FROM jobs j
    INNER JOIN companies c ON j.company_id = c.id
    WHERE j.id = ?
");
$stmt->execute([$job_id]);
$job = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$job) {
    setFlash('error', 'Job not found.');
    redirect('/Uniworksmohinhhoa/student/jobs.php');
}

require_once '../includes/notifications.php';
include '../includes/header.php';
?>

<div class="student-shell">
    <aside class="student-sidebar">
        <div>
            <div class="student-brand">
                <div class="student-brand__logo">✦</div>
                <div class="student-brand__text">
                    <h3><?= htmlspecialchars($user['full_name']) ?></h3>
                    <p>Aspiring Student</p>
                </div>
            </div>

            <nav class="student-nav">
                <a href="/Uniworksmohinhhoa/student/dashboard.php">Dashboard</a>
                <a href="/Uniworksmohinhhoa/student/applications.php">Applications</a>
                <a href="/Uniworksmohinhhoa/student/jobs.php" class="active">Internships</a>
                <a href="/Uniworksmohinhhoa/student/messages.php">Messages<?php if(!empty($notif['messages']) && $notif['messages']>0): ?><span class="notif-badge"><?= $notif['messages'] ?></span><?php endif; ?></a>
                <a href="/Uniworksmohinhhoa/student/profile.php">Profile</a>
                <a href="/Uniworksmohinhhoa/student/report.php">Final Report</a>
                <a href="/Uniworksmohinhhoa/student/evaluation.php">Evaluation<?php if(!empty($notif['evaluations']) && $notif['evaluations']>0): ?><span class="notif-badge"><?= $notif['evaluations'] ?></span><?php endif; ?></a>
            </nav>
        </div>

        <div class="student-sidebar__footer">
            <a href="/Uniworksmohinhhoa/public/logout.php">↩ Logout</a>
        </div>
    </aside>

    <main class="student-main">
        <div class="student-form-card">
            <h2>Apply for Internship</h2>

            <?php if ($flash): ?>
                <div class="flash <?= htmlspecialchars($flash['type']) ?>" style="margin-bottom:18px;">
                    <?= htmlspecialchars($flash['message']) ?>
                </div>
            <?php endif; ?>

            <p style="margin-bottom:8px;">
                <strong>Job Title:</strong> <?= htmlspecialchars($job['title']) ?>
            </p>

            <p style="margin-bottom:20px;">
                <strong>Company:</strong> <?= htmlspecialchars($job['company_name']) ?>
            </p>

            <?php if ($alreadyCompleted): ?>
                <div class="flash error" style="margin-bottom:18px;">
                    You have already completed an internship. You are not allowed to apply for new jobs.
                </div>
            <?php else: ?>
            <form action="/Uniworksmohinhhoa/actions/student/apply_job_action.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="job_id" value="<?= $job['id'] ?>">

                <div class="student-form-group">
    <label for="cv_file">Upload CV (PDF, DOC, DOCX)</label>
    <input
        id="cv_file"
        type="file"
        name="cv_file"
        accept=".pdf,.doc,.docx"
        required
        style="display:block; width:100%; padding:12px; background:#f8f9fc; border:1px solid #d9ddea; border-radius:16px;"
    >
</div>
                <button type="submit" class="student-btn">Submit Application</button>
            </form>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php include '../includes/footer.php'; ?>