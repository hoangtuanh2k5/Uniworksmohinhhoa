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

$stmt = $pdo->prepare("
    SELECT j.id, j.title, c.company_name
    FROM jobs j
    INNER JOIN companies c ON j.company_id = c.id
    WHERE j.id = ? AND j.status = 'open'
");
$stmt->execute([$job_id]);
$job = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$job) {
    redirect('/Uniworksmohinhhoa/student/jobs.php');
}

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
                <a href="/Uniworksmohinhhoa/student/messages.php">Messages</a>
                <a href="/Uniworksmohinhhoa/student/profile.php">Profile</a>
            </nav>
        </div>

        <div class="student-sidebar__footer">
            <a href="/Uniworksmohinhhoa/public/logout.php">↩ Log Out</a>
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

            <p style="margin-bottom:8px;"><strong>Job Title:</strong> <?= htmlspecialchars($job['title']) ?></p>
            <p style="margin-bottom:20px;"><strong>Company:</strong> <?= htmlspecialchars($job['company_name']) ?></p>

            <form action="../actions/student/apply_job_action.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="job_id" value="<?= $job['id'] ?>">

                <div class="student-form-group">
                    <label>Upload CV (PDF, DOC, DOCX)</label>
                    <input type="file" name="cv_file" class="student-form-control" required>
                </div>

                <button type="submit" class="student-btn">Submit Application</button>
            </form>
        </div>
    </main>
</div>

<?php include '../includes/footer.php'; ?>