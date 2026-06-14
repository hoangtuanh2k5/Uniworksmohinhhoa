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
| Lấy applications + internship registrations
|-------------------------------------------------------
*/
$stmt = $pdo->prepare("
    SELECT
        a.id,
        a.cv_url,
        a.status,
        a.admin_approved,
        a.company_approved,
        a.applied_at,
        j.title AS job_title,
        c.company_name,
        ir.id AS registration_id,
        ir.start_date,
        ir.end_date,
        ir.status AS internship_status
    FROM applications a
    INNER JOIN jobs j ON a.job_id = j.id
    INNER JOIN companies c ON j.company_id = c.id
    LEFT JOIN internship_registrations ir ON ir.application_id = a.id
    WHERE a.student_id = ?
    ORDER BY a.id DESC
");
$stmt->execute([$student['id']]);
$applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

function studentApplicationStatus(array $app): string {
    if ((int)$app['admin_approved'] === 0) {
        return 'Waiting for school approval';
    }

    if ((int)$app['admin_approved'] === -1) {
        return 'Rejected by school';
    }

    if ((int)$app['admin_approved'] === 1 && (int)$app['company_approved'] === 0) {
        return 'Waiting for company decision';
    }

    if ((int)$app['company_approved'] === 1) {
        return 'Accepted by company';
    }

    if ((int)$app['company_approved'] === -1) {
        return 'Rejected by company';
    }

    return ucfirst(str_replace('_', ' ', $app['status'] ?? 'pending'));
}

function studentApplicationBadgeClass(array $app): string {
    if ((int)$app['admin_approved'] === 0) {
        return 'pending';
    }

    if ((int)$app['admin_approved'] === -1) {
        return 'rejected';
    }

    if ((int)$app['admin_approved'] === 1 && (int)$app['company_approved'] === 0) {
        return 'reviewing';
    }

    if ((int)$app['company_approved'] === 1) {
        return 'accepted';
    }

    if ((int)$app['company_approved'] === -1) {
        return 'rejected';
    }

    return 'default';
}

function studentInternshipBadgeClass(?string $status): string {
    $status = strtolower((string)$status);

    if ($status === 'ongoing') {
        return 'ongoing';
    }

    if ($status === 'completed') {
        return 'completed';
    }

    return 'default';
}

require_once '../includes/notifications.php';
include '../includes/header.php';
?>

<style>
.student-app-page{
    padding:8px 6px 24px;
}

.student-app-header{
    margin-bottom:24px;
}

.student-app-header h1{
    margin:0 0 10px;
    font-size:44px;
    line-height:1.08;
    color:#1a1f36;
    font-weight:800;
}

.student-app-header p{
    margin:0;
    font-size:18px;
    color:#707894;
    line-height:1.6;
}

.student-app-card{
    background:#fff;
    border:1px solid #ece9f7;
    border-radius:30px;
    box-shadow:0 12px 28px rgba(31,34,51,.05);
    overflow:hidden;
}

.student-app-card__top{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:16px;
    padding:24px 28px 18px;
}

.student-app-card__top h2{
    margin:0;
    font-size:24px;
    color:#161b34;
    font-weight:800;
}

.student-app-chip{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    padding:10px 14px;
    border-radius:999px;
    background:#f6f2ff;
    color:#6259aa;
    font-size:14px;
    font-weight:700;
}

.student-app-table-wrap{
    overflow-x:auto;
}

.student-app-table{
    width:100%;
    border-collapse:collapse;
    min-width:1320px;
}

.student-app-table th{
    text-align:left;
    padding:18px 24px;
    background:#faf8ff;
    color:#74809b;
    font-size:14px;
    font-weight:800;
    border-bottom:1px solid #ece9f7;
}

.student-app-table td{
    padding:18px 24px;
    border-bottom:1px solid #f1edf8;
    color:#1f2233;
    font-size:15px;
    vertical-align:middle;
}

.student-app-table tr:last-child td{
    border-bottom:none;
}

.student-app-company{
    font-weight:800;
    color:#171c34;
}

.student-app-job{
    color:#4f5770;
    font-weight:700;
}

.student-app-date{
    color:#53607d;
    font-weight:600;
}

.student-app-muted{
    color:#8c93aa;
    font-size:14px;
    font-weight:600;
}

.student-status-badge,
.student-internship-badge{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    padding:9px 12px;
    border-radius:999px;
    font-size:13px;
    font-weight:800;
    white-space:nowrap;
}

.student-status-badge.pending{
    background:#f6e8a6;
    color:#a36a00;
}

.student-status-badge.accepted{
    background:#d8f2df;
    color:#187a3d;
}

.student-status-badge.reviewing{
    background:#dce7ff;
    color:#265ad9;
}

.student-status-badge.rejected{
    background:#ffe1e1;
    color:#ad3e3e;
}

.student-status-badge.default{
    background:#f1edff;
    color:#6157aa;
}

.student-internship-badge.ongoing{
    background:#dce7ff;
    color:#265ad9;
}

.student-internship-badge.completed{
    background:#d8f2df;
    color:#187a3d;
}

.student-internship-badge.default{
    background:#f1edff;
    color:#6157aa;
}

.student-link-btn{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    text-decoration:none;
    border:none;
    cursor:pointer;
    border-radius:14px;
    padding:10px 16px;
    font-weight:700;
    font-size:14px;
    transition:.2s ease;
    white-space:nowrap;
}

.student-link-btn:hover{
    transform:translateY(-1px);
}

.student-link-btn.cv{
    background:#f0d86a;
    color:#1f2233;
}

.student-link-btn.cv:hover{
    background:#e7cf5d;
}

.student-link-btn.detail{
    background:#cfc0ff;
    color:#1f2233;
}

.student-link-btn.detail:hover{
    background:#c1afff;
}

.student-empty{
    padding:40px 28px;
    text-align:center;
    color:#7a8198;
}

@media (max-width: 768px){
    .student-app-header h1{
        font-size:36px;
    }

    .student-app-header p{
        font-size:16px;
    }

    .student-app-card__top{
        flex-direction:column;
        align-items:flex-start;
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
                <a href="/Uniworksmohinhhoa/student/applications.php" class="active">Applications</a>
                <a href="/Uniworksmohinhhoa/student/jobs.php">Internships</a>
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
        <div class="student-app-page">
            <div class="student-app-header">
                <h1>My Applications</h1>
                <p>Track your application progress and internship timeline in one place.</p>
            </div>

            <?php if ($flash): ?>
                <div class="flash <?= htmlspecialchars($flash['type']) ?>" style="margin-bottom:18px;">
                    <?= htmlspecialchars($flash['message']) ?>
                </div>
            <?php endif; ?>

            <div class="student-app-card">
                <div class="student-app-card__top">
                    <h2>Application Progress</h2>
                    <div class="student-app-chip">
                        Total: <?= count($applications) ?> applications
                    </div>
                </div>

                <?php if (!empty($applications)): ?>
                    <div class="student-app-table-wrap">
                        <table class="student-app-table">
                            <thead>
                                <tr>
                                    <th>Company</th>
                                    <th>Job</th>
                                    <th>Applied At</th>
                                    <th>Application Status</th>
                                    <th>CV</th>
                                    <th>Internship Start</th>
                                    <th>Internship End</th>
                                    <th>Duration</th>
                                    <th>Internship Progress</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($applications as $app): ?>
                                    <tr>
                                        <td>
                                            <span class="student-app-company">
                                                <?= htmlspecialchars($app['company_name']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="student-app-job">
                                                <?= htmlspecialchars($app['job_title']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="student-app-date">
                                                <?= htmlspecialchars($app['applied_at']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="student-status-badge <?= studentApplicationBadgeClass($app) ?>">
                                                <?= htmlspecialchars(studentApplicationStatus($app)) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if (!empty($app['cv_url'])): ?>
                                                <a 
                                                    href="/Uniworksmohinhhoa/<?= htmlspecialchars($app['cv_url']) ?>" 
                                                    target="_blank" 
                                                    class="student-link-btn cv"
                                                >
                                                    View CV
                                                </a>
                                            <?php else: ?>
                                                <span class="student-app-muted">No CV</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($app['start_date'])): ?>
                                                <span class="student-app-date"><?= htmlspecialchars($app['start_date']) ?></span>
                                            <?php else: ?>
                                                <span class="student-app-muted">Not started</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($app['end_date'])): ?>
                                                <span class="student-app-date"><?= htmlspecialchars($app['end_date']) ?></span>
                                            <?php else: ?>
                                                <span class="student-app-muted">Not set</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($app['registration_id'])): ?>
                                                <span class="student-app-date">3 months</span>
                                            <?php else: ?>
                                                <span class="student-app-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($app['internship_status'])): ?>
                                                <span class="student-internship-badge <?= studentInternshipBadgeClass($app['internship_status']) ?>">
                                                    <?= htmlspecialchars(ucfirst($app['internship_status'])) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="student-app-muted">Not started</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="student-empty">
                        You have not applied to any internship yet.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<?php include '../includes/footer.php'; ?>