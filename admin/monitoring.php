<?php
require_once '../includes/auth.php';
require_once '../config/db.php';
require_once '../includes/functions.php';
requireRole('admin');

$user = currentUser();
$flash = getFlash();

$totalOngoing = (int)$pdo->query("
    SELECT COUNT(*) 
    FROM internship_registrations
    WHERE status = 'ongoing'
")->fetchColumn();

$totalCompleted = (int)$pdo->query("
    SELECT COUNT(*) 
    FROM internship_registrations
    WHERE status = 'completed'
")->fetchColumn();

$totalReports = (int)$pdo->query("
    SELECT COUNT(*) 
    FROM reports
")->fetchColumn();

$stmt = $pdo->query("
    SELECT 
        ir.id,
        ir.start_date,
        ir.end_date,
        ir.status,
        u.full_name AS student_name,
        c.company_name,
        j.title AS job_title,
        e.score,
        e.feedback,
        e.created_at AS evaluation_date
    FROM internship_registrations ir
    INNER JOIN applications a ON ir.application_id = a.id
    INNER JOIN students s ON a.student_id = s.id
    INNER JOIN users u ON s.user_id = u.id
    INNER JOIN jobs j ON a.job_id = j.id
    INNER JOIN companies c ON j.company_id = c.id
    LEFT JOIN evaluations e ON e.registration_id = ir.id
    ORDER BY ir.id DESC
");
$internships = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once '../includes/notifications.php';
include '../includes/header.php';
?>

<style>
.admin-brand{
    padding:8px 8px 28px;
    margin-bottom:16px;
}

.admin-brand h2{
    margin:0;
    font-size:24px;
    line-height:1.25;
    color:#1b2038;
    font-weight:800;
}

.admin-nav{
    display:flex;
    flex-direction:column;
    gap:12px;
    margin-top:10px;
}

.admin-monitor-page{
    padding:8px 6px 24px;
}

.admin-monitor-header{
    margin-bottom:24px;
}

.admin-monitor-header h1{
    margin:0 0 10px;
    font-size:46px;
    line-height:1.08;
    color:#161b34;
    font-weight:800;
}

.admin-monitor-header p{
    margin:0;
    font-size:18px;
    color:#707894;
    line-height:1.6;
}

.admin-monitor-stats{
    display:grid;
    grid-template-columns:repeat(3, minmax(0, 1fr));
    gap:20px;
    margin-bottom:26px;
}

.admin-monitor-card{
    border-radius:28px;
    padding:24px;
    box-shadow:0 12px 28px rgba(31,34,51,.05);
}

.admin-monitor-card.yellow{
    background:#efd867;
}

.admin-monitor-card.purple{
    background:#cfc6ff;
}

.admin-monitor-card h3{
    margin:0 0 12px;
    font-size:18px;
    color:#42506f;
    font-weight:700;
}

.admin-monitor-card strong{
    font-size:44px;
    color:#11162d;
    font-weight:800;
}

.admin-monitor-panel{
    background:#fff;
    border:1px solid #ece9f7;
    border-radius:30px;
    box-shadow:0 12px 28px rgba(31,34,51,.05);
    overflow:hidden;
}

.admin-monitor-panel__top{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:16px;
    padding:24px 28px 18px;
}

.admin-monitor-panel__top h2{
    margin:0;
    font-size:24px;
    color:#161b34;
    font-weight:800;
}

.admin-monitor-chip{
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

.admin-monitor-table-wrap{
    overflow-x:auto;
}

.admin-monitor-table{
    width:100%;
    border-collapse:collapse;
    min-width:1500px;
}

.admin-monitor-table th{
    text-align:left;
    padding:18px 24px;
    background:#faf8ff;
    color:#74809b;
    font-size:14px;
    font-weight:800;
    border-bottom:1px solid #ece9f7;
}

.admin-monitor-table td{
    padding:18px 24px;
    border-bottom:1px solid #f1edf8;
    color:#1f2233;
    font-size:15px;
    vertical-align:middle;
}

.admin-monitor-table tr:last-child td{
    border-bottom:none;
}

.admin-monitor-student{
    font-weight:800;
    color:#171c34;
}

.admin-monitor-company,
.admin-monitor-job,
.admin-monitor-date{
    color:#53607d;
    font-weight:600;
}

.admin-monitor-badge{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    padding:10px 14px;
    border-radius:999px;
    font-size:13px;
    font-weight:800;
    white-space:nowrap;
}

.admin-monitor-badge.ongoing{
    background:#dce7ff;
    color:#265ad9;
}

.admin-monitor-badge.completed{
    background:#d8f2df;
    color:#187a3d;
}

.admin-monitor-badge.default{
    background:#f1edff;
    color:#6157aa;
}

.admin-monitor-eval{
    min-width:220px;
}

.admin-monitor-score{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    min-width:78px;
    height:34px;
    padding:0 12px;
    border-radius:999px;
    background:#fff7d6;
    color:#8a6a00;
    font-size:13px;
    font-weight:800;
    margin-bottom:8px;
}

.admin-monitor-feedback{
    color:#4f5770;
    line-height:1.65;
    font-size:14px;
    max-width:260px;
}

.admin-monitor-feedback.empty{
    color:#8c93aa;
    font-weight:600;
}

.admin-monitor-eval-date{
    margin-top:8px;
    color:#8b93ab;
    font-size:13px;
    font-weight:600;
}

.admin-monitor-action{
    display:flex;
    gap:10px;
    flex-wrap:wrap;
}

.admin-monitor-btn{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    min-width:130px;
    height:42px;
    padding:0 14px;
    border:none;
    border-radius:14px;
    font-size:14px;
    font-weight:800;
    text-decoration:none;
    cursor:pointer;
    transition:.2s ease;
}

.admin-monitor-btn:hover{
    transform:translateY(-1px);
}

.admin-monitor-btn.complete{
    background:#cfc6ff;
    color:#1f2233;
}

.admin-monitor-btn.complete:hover{
    background:#c1b3ff;
}

.admin-monitor-note{
    color:#7c849c;
    font-size:14px;
    font-weight:700;
}

.admin-monitor-empty{
    padding:40px 28px;
    text-align:center;
    color:#7a8198;
}

@media (max-width: 1000px){
    .admin-monitor-stats{
        grid-template-columns:1fr;
    }

    .admin-monitor-header h1{
        font-size:38px;
    }

    .admin-monitor-header p{
        font-size:16px;
    }

    .admin-monitor-panel__top{
        flex-direction:column;
        align-items:flex-start;
    }
}
</style>

<div class="admin-shell">
    <aside class="admin-sidebar">
        <div>
            <div class="admin-brand">
                <h2>Admin Panel</h2>
            </div>

            <nav class="admin-nav">
                <a href="/Uniworksmohinhhoa/admin/dashboard.php">Dashboard</a>
                <a href="/Uniworksmohinhhoa/admin/users.php">Users</a>
                <a href="/Uniworksmohinhhoa/admin/applications.php">Applications</a>
                <a href="/Uniworksmohinhhoa/admin/monitoring.php" class="active">Monitoring</a>
                <a href="/Uniworksmohinhhoa/admin/reports.php">Reports<?php if(!empty($notif['reports']) && $notif['reports']>0): ?><span class="notif-badge"><?= $notif['reports'] ?></span><?php endif; ?></a>
            </nav>
        </div>

        <div class="admin-sidebar__footer">
            <a href="/Uniworksmohinhhoa/public/logout.php">Logout</a>
        </div>
    </aside>

    <main class="admin-main">
        <div class="admin-monitor-page">
            <div class="admin-monitor-header">
                <h1>Internship Monitoring</h1>
                <p>Track internship progress, completion status, and company evaluations in one place.</p>
            </div>

            <?php if ($flash): ?>
                <div class="flash <?= htmlspecialchars($flash['type']) ?>" style="margin-bottom:18px;">
                    <?= htmlspecialchars($flash['message']) ?>
                </div>
            <?php endif; ?>

            <div class="admin-monitor-stats">
                <div class="admin-monitor-card yellow">
                    <h3>Ongoing Internships</h3>
                    <strong><?= $totalOngoing ?></strong>
                </div>

                <div class="admin-monitor-card purple">
                    <h3>Completed Internships</h3>
                    <strong><?= $totalCompleted ?></strong>
                </div>

                <div class="admin-monitor-card yellow">
                    <h3>Submitted Reports</h3>
                    <strong><?= $totalReports ?></strong>
                </div>
            </div>

            <div class="admin-monitor-panel">
                <div class="admin-monitor-panel__top">
                    <h2>Internship Progress List</h2>
                    <div class="admin-monitor-chip">
                        Total: <?= count($internships) ?> records
                    </div>
                </div>

                <?php if (!empty($internships)): ?>
                    <div class="admin-monitor-table-wrap">
                        <table class="admin-monitor-table">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Company</th>
                                    <th>Job</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Status</th>
                                    <th>Evaluation</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($internships as $item): ?>
                                    <tr>
                                        <td>
                                            <span class="admin-monitor-student">
                                                <?= htmlspecialchars($item['student_name']) ?>
                                            </span>
                                        </td>

                                        <td>
                                            <span class="admin-monitor-company">
                                                <?= htmlspecialchars($item['company_name']) ?>
                                            </span>
                                        </td>

                                        <td>
                                            <span class="admin-monitor-job">
                                                <?= htmlspecialchars($item['job_title']) ?>
                                            </span>
                                        </td>

                                        <td>
                                            <span class="admin-monitor-date">
                                                <?= htmlspecialchars($item['start_date']) ?>
                                            </span>
                                        </td>

                                        <td>
                                            <span class="admin-monitor-date">
                                                <?= htmlspecialchars($item['end_date']) ?>
                                            </span>
                                        </td>

                                        <td>
                                            <span class="admin-monitor-badge <?= in_array($item['status'], ['ongoing','completed']) ? htmlspecialchars($item['status']) : 'default' ?>">
                                                <?= htmlspecialchars(ucfirst($item['status'])) ?>
                                            </span>
                                        </td>

                                        <td>
                                            <div class="admin-monitor-eval">
                                                <?php if (!empty($item['score'])): ?>
                                                    <div class="admin-monitor-score">
                                                        <?= htmlspecialchars($item['score']) ?>/10
                                                    </div>

                                                    <div class="admin-monitor-feedback">
                                                        <?= nl2br(htmlspecialchars(mb_strlen($item['feedback']) > 80 ? mb_substr($item['feedback'], 0, 80) . '...' : $item['feedback'])) ?>
                                                    </div>

                                                    <div class="admin-monitor-eval-date">
                                                        <?= htmlspecialchars($item['evaluation_date']) ?>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="admin-monitor-feedback empty">
                                                        Not evaluated
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </td>

                                        <td>
                                            <?php if ($item['status'] === 'ongoing'): ?>
                                                <div class="admin-monitor-action">
                                                    <form action="/Uniworksmohinhhoa/actions/admin/mark_internship_completed_action.php" method="POST" style="display:inline;">
                                                        <input type="hidden" name="registration_id" value="<?= $item['id'] ?>">
                                                        <button type="submit" class="admin-monitor-btn complete">Mark Completed</button>
                                                    </form>
                                                </div>
                                            <?php else: ?>
                                                <span class="admin-monitor-note">Completed</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="admin-monitor-empty">
                        No internship registrations found.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<?php include '../includes/footer.php'; ?>