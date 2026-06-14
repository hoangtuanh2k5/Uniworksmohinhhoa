<?php
require_once '../includes/auth.php';
require_once '../config/db.php';
require_once '../includes/functions.php';
requireRole('student');

$user = currentUser();
$flash = getFlash();

/*
|-------------------------------------------------------
| Lấy student hiện tại
|-------------------------------------------------------
*/
$stmt = $pdo->prepare("SELECT id FROM students WHERE user_id = ?");
$stmt->execute([$user['id']]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    redirect('/Uniworksmohinhhoa/student/profile.php?setup=1');
}

/*
|-------------------------------------------------------
| Lấy evaluations của student
|-------------------------------------------------------
*/
$stmt = $pdo->prepare("
    SELECT
        e.id,
        e.score,
        e.feedback,
        e.created_at,
        ir.start_date,
        ir.end_date,
        ir.status AS internship_status,
        j.title AS job_title,
        c.company_name
    FROM evaluations e
    INNER JOIN internship_registrations ir ON e.registration_id = ir.id
    INNER JOIN applications a ON ir.application_id = a.id
    INNER JOIN jobs j ON a.job_id = j.id
    INNER JOIN companies c ON j.company_id = c.id
    WHERE a.student_id = ?
    ORDER BY e.created_at DESC, e.id DESC
");
$stmt->execute([$student['id']]);
$evaluations = $stmt->fetchAll(PDO::FETCH_ASSOC);

define('NOTIF_PAGE', 'evaluations');
require_once '../includes/notifications.php';
include '../includes/header.php';
?>

<style>
.student-eval-page{
    padding:8px 6px 24px;
}
.student-eval-header{
    margin-bottom:24px;
}
.student-eval-header h1{
    margin:0 0 10px;
    font-size:44px;
    line-height:1.08;
    color:#1a1f36;
    font-weight:800;
}
.student-eval-header p{
    margin:0;
    font-size:18px;
    color:#707894;
    line-height:1.6;
}
.student-eval-grid{
    display:grid;
    gap:24px;
}
.student-eval-card{
    background:#fff;
    border:1px solid #ece9f7;
    border-radius:30px;
    padding:28px;
    box-shadow:0 12px 28px rgba(31,34,51,.05);
}
.student-eval-top{
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    gap:16px;
    margin-bottom:20px;
}
.student-eval-top h2{
    margin:0 0 8px;
    font-size:24px;
    color:#161b34;
    font-weight:800;
}
.student-eval-meta{
    margin:0;
    color:#6f7790;
    line-height:1.7;
    font-size:16px;
}
.student-eval-score{
    min-width:120px;
    height:72px;
    border-radius:22px;
    background:#efd867;
    color:#1f2233;
    display:flex;
    flex-direction:column;
    align-items:center;
    justify-content:center;
    font-weight:800;
}
.student-eval-score span{
    font-size:13px;
    text-transform:uppercase;
    letter-spacing:.04em;
}
.student-eval-score strong{
    font-size:28px;
    line-height:1;
    margin-top:4px;
}
.student-eval-feedback{
    background:#fcfbff;
    border:1px solid #f0ebfb;
    border-radius:22px;
    padding:18px;
}
.student-eval-feedback p{
    margin:0;
    color:#4f5770;
    line-height:1.8;
    white-space:pre-line;
}
.student-eval-footer{
    margin-top:14px;
    color:#8b93ab;
    font-size:14px;
    font-weight:600;
}
.student-empty{
    background:#fff;
    border:1px solid #ece9f7;
    border-radius:30px;
    padding:32px;
    color:#707894;
    box-shadow:0 12px 28px rgba(31,34,51,.05);
}
@media (max-width: 768px){
    .student-eval-header h1{
        font-size:36px;
    }
    .student-eval-header p{
        font-size:16px;
    }
    .student-eval-top{
        flex-direction:column;
    }
}
</style>

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
                <a href="/Uniworksmohinhhoa/student/messages.php">Messages<?php if(!empty($notif['messages']) && $notif['messages']>0): ?><span class="notif-badge"><?= $notif['messages'] ?></span><?php endif; ?></a>
                <a href="/Uniworksmohinhhoa/student/profile.php">Profile</a>
                
                <a href="/Uniworksmohinhhoa/student/report.php">Final Report</a>
                <a href="/Uniworksmohinhhoa/student/evaluation.php" class="active">Evaluation</a>
            </nav>
        </div>

        <div class="student-sidebar__footer">
            <a href="/Uniworksmohinhhoa/public/logout.php">↩ Logout</a>
        </div>
    </aside>

    <main class="student-main">
        <div class="student-eval-page">
            <div class="student-eval-header">
                <h1>My Evaluations</h1>
                <p>Review the feedback submitted by the company after your internship is completed.</p>
            </div>

            <?php if ($flash): ?>
                <div class="flash <?= htmlspecialchars($flash['type']) ?>" style="margin-bottom:18px;">
                    <?= htmlspecialchars($flash['message']) ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($evaluations)): ?>
                <div class="student-eval-grid">
                    <?php foreach ($evaluations as $eval): ?>
                        <div class="student-eval-card">
                            <div class="student-eval-top">
                                <div>
                                    <h2><?= htmlspecialchars($eval['job_title']) ?></h2>
                                    <p class="student-eval-meta">
                                        <?= htmlspecialchars($eval['company_name']) ?><br>
                                        Internship Period: <?= htmlspecialchars($eval['start_date']) ?> → <?= htmlspecialchars($eval['end_date']) ?><br>
                                        Internship Status: <?= htmlspecialchars(ucfirst($eval['internship_status'])) ?>
                                    </p>
                                </div>

                                <div class="student-eval-score">
                                    <span>Score</span>
                                    <strong><?= htmlspecialchars($eval['score']) ?>/10</strong>
                                </div>
                            </div>

                            <div class="student-eval-feedback">
                                <p><?= nl2br(htmlspecialchars($eval['feedback'])) ?></p>
                            </div>

                            <div class="student-eval-footer">
                                Submitted at: <?= htmlspecialchars($eval['created_at']) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="student-empty">
                    No evaluation is available yet. The company can submit an evaluation after your internship is completed.
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php include '../includes/footer.php'; ?>