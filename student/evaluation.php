<?php
require_once '../includes/auth.php';
require_once '../config/db.php';
requireRole('student');

$user = currentUser();

$stmt = $pdo->prepare("SELECT id FROM students WHERE user_id = ?");
$stmt->execute([$user['id']]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    redirect('/Uniworksmohinhhoa/student/profile.php?setup=1');
}

$stmt = $pdo->prepare("
    SELECT 
        e.id,
        e.evaluator_role,
        e.score,
        e.feedback,
        e.created_at,
        j.title,
        c.company_name
    FROM evaluations e
    INNER JOIN internship_registrations ir ON e.registration_id = ir.id
    INNER JOIN applications a ON ir.application_id = a.id
    INNER JOIN jobs j ON a.job_id = j.id
    INNER JOIN companies c ON j.company_id = c.id
    WHERE a.student_id = ?
    ORDER BY e.id DESC
");
$stmt->execute([$student['id']]);
$evaluations = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
                <a href="/Uniworksmohinhhoa/student/jobs.php">Internships</a>
                <a href="/Uniworksmohinhhoa/student/messages.php">Messages</a>
                <a href="/Uniworksmohinhhoa/student/profile.php">Profile</a>
            </nav>
        </div>

        <div class="student-sidebar__footer">
            <a href="/Uniworksmohinhhoa/public/logout.php">↩ Sign Out</a>
        </div>
    </aside>

    <main class="student-main">
        <div class="student-topbar">
            <div>
                <h1>Evaluations</h1>
                <p>View the feedback and scores from your internship process.</p>
            </div>
        </div>

        <div style="display:grid; gap:18px;">
            <?php if (empty($evaluations)): ?>
                <div class="student-card">No evaluations available yet.</div>
            <?php else: ?>
                <?php foreach ($evaluations as $eva): ?>
                    <div class="student-card">
                        <h3><?= htmlspecialchars($eva['title']) ?> - <?= htmlspecialchars($eva['company_name']) ?></h3>
                        <p><strong>Evaluator:</strong> <?= htmlspecialchars($eva['evaluator_role']) ?></p>
                        <p><strong>Score:</strong> <?= htmlspecialchars($eva['score']) ?></p>
                        <p><strong>Feedback:</strong> <?= nl2br(htmlspecialchars($eva['feedback'])) ?></p>
                        <p><strong>Date:</strong> <?= htmlspecialchars($eva['created_at']) ?></p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php include '../includes/footer.php'; ?>