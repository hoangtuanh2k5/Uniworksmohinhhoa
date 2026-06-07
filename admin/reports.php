<?php
require_once '../includes/auth.php';
require_once '../config/db.php';
require_once '../includes/functions.php';
requireRole('admin');

$user = currentUser();
$flash = getFlash();

/*
|-------------------------------------------------------
| Summary cards
|-------------------------------------------------------
*/
$totalCompanies = (int)$pdo->query("
    SELECT COUNT(*) 
    FROM companies
")->fetchColumn();

$totalCompletedInternships = (int)$pdo->query("
    SELECT COUNT(*) 
    FROM internship_registrations
    WHERE status = 'completed'
")->fetchColumn();

$totalOngoingInternships = (int)$pdo->query("
    SELECT COUNT(*) 
    FROM internship_registrations
    WHERE status = 'ongoing'
")->fetchColumn();

$totalReports = (int)$pdo->query("
    SELECT COUNT(*)
    FROM reports
")->fetchColumn();

/*
|-------------------------------------------------------
| Reports list
|-------------------------------------------------------
*/
$stmt = $pdo->query("
    SELECT
        r.id,
        r.content,
        r.submitted_at,
        ir.status AS registration_status,
        u.full_name AS student_name,
        c.company_name,
        j.title AS job_title
    FROM reports r
    INNER JOIN internship_registrations ir ON r.registration_id = ir.id
    INNER JOIN applications a ON ir.application_id = a.id
    INNER JOIN students s ON a.student_id = s.id
    INNER JOIN users u ON s.user_id = u.id
    INNER JOIN jobs j ON a.job_id = j.id
    INNER JOIN companies c ON j.company_id = c.id
    ORDER BY r.submitted_at DESC, r.id DESC
");
$reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

.admin-reports-page{
    padding:8px 6px 24px;
}

.admin-reports-header{
    margin-bottom:24px;
}

.admin-reports-header h1{
    margin:0 0 10px;
    font-size:46px;
    line-height:1.08;
    color:#161b34;
    font-weight:800;
}

.admin-reports-header p{
    margin:0;
    font-size:18px;
    color:#707894;
    line-height:1.6;
}

.admin-reports-stats{
    display:grid;
    grid-template-columns:repeat(4, minmax(0, 1fr));
    gap:20px;
    margin-bottom:26px;
}

.admin-reports-stat{
    border-radius:28px;
    padding:24px;
    box-shadow:0 12px 28px rgba(31,34,51,.05);
}

.admin-reports-stat.yellow{
    background:#efd867;
}

.admin-reports-stat.purple{
    background:#cfc6ff;
}

.admin-reports-stat h3{
    margin:0 0 12px;
    font-size:17px;
    color:#4d5876;
    font-weight:700;
}

.admin-reports-stat strong{
    font-size:42px;
    line-height:1;
    color:#11162d;
    font-weight:800;
}

.admin-reports-panel{
    background:#fff;
    border:1px solid #ece9f7;
    border-radius:30px;
    box-shadow:0 12px 28px rgba(31,34,51,.05);
    overflow:hidden;
}

.admin-reports-panel__top{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:16px;
    padding:24px 28px 18px;
}

.admin-reports-panel__top h2{
    margin:0;
    font-size:24px;
    color:#161b34;
    font-weight:800;
}

.admin-reports-chip{
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

.admin-reports-table-wrap{
    overflow-x:auto;
}

.admin-reports-table{
    width:100%;
    border-collapse:collapse;
    min-width:1200px;
}

.admin-reports-table th{
    text-align:left;
    padding:18px 28px;
    background:#faf8ff;
    color:#74809b;
    font-size:14px;
    font-weight:800;
    border-bottom:1px solid #ece9f7;
}

.admin-reports-table td{
    padding:18px 28px;
    border-bottom:1px solid #f1edf8;
    color:#1f2233;
    font-size:15px;
    vertical-align:top;
}

.admin-reports-table tr:last-child td{
    border-bottom:none;
}

.admin-report-student{
    font-weight:800;
    color:#171c34;
}

.admin-report-company,
.admin-report-job{
    color:#56607b;
    font-weight:600;
}

.admin-report-content{
    max-width:420px;
    color:#4f5770;
    line-height:1.7;
    white-space:pre-line;
}

.admin-report-badge{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    padding:9px 12px;
    border-radius:999px;
    font-size:13px;
    font-weight:800;
    white-space:nowrap;
}

.admin-report-badge.ongoing{
    background:#dce7ff;
    color:#265ad9;
}

.admin-report-badge.completed{
    background:#d8f2df;
    color:#187a3d;
}

.admin-report-badge.default{
    background:#f1edff;
    color:#6157aa;
}

.admin-empty{
    padding:40px 28px;
    text-align:center;
    color:#7a8198;
}

@media (max-width: 1100px){
    .admin-reports-stats{
        grid-template-columns:repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 768px){
    .admin-reports-header h1{
        font-size:38px;
    }

    .admin-reports-header p{
        font-size:16px;
    }

    .admin-reports-stats{
        grid-template-columns:1fr;
    }

    .admin-reports-panel__top{
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
                <a href="/Uniworksmohinhhoa/admin/monitoring.php">Monitoring</a>
                <a href="/Uniworksmohinhhoa/admin/reports.php" class="active">Reports</a>
            </nav>
        </div>

        <div class="admin-sidebar__footer">
            <a href="/Uniworksmohinhhoa/public/logout.php">Logout</a>
        </div>
    </aside>

    <main class="admin-main">
        <div class="admin-reports-page">
            <div class="admin-reports-header">
                <h1>Reports</h1>
                <p>Review submitted internship reports and monitor overall internship completion.</p>
            </div>

            <?php if ($flash): ?>
                <div class="flash <?= htmlspecialchars($flash['type']) ?>" style="margin-bottom:18px;">
                    <?= htmlspecialchars($flash['message']) ?>
                </div>
            <?php endif; ?>

            <div class="admin-reports-stats">
                <div class="admin-reports-stat yellow">
                    <h3>Completed Internships</h3>
                    <strong><?= $totalCompletedInternships ?></strong>
                </div>

                <div class="admin-reports-stat purple">
                    <h3>Total Companies</h3>
                    <strong><?= $totalCompanies ?></strong>
                </div>

                <div class="admin-reports-stat yellow">
                    <h3>Ongoing Internships</h3>
                    <strong><?= $totalOngoingInternships ?></strong>
                </div>

                <div class="admin-reports-stat purple">
                    <h3>Submitted Reports</h3>
                    <strong><?= $totalReports ?></strong>
                </div>
            </div>

            <div class="admin-reports-panel">
                <div class="admin-reports-panel__top">
                    <h2>Submitted Reports List</h2>
                    <div class="admin-reports-chip">
                        Total: <?= count($reports) ?> reports
                    </div>
                </div>

                <?php if (!empty($reports)): ?>
                    <div class="admin-reports-table-wrap">
                        <table class="admin-reports-table">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Company</th>
                                    <th>Job</th>
                                    <th>Report Content</th>
                                    <th>Submitted At</th>
                                    <th>Internship Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reports as $report): ?>
                                    <tr>
                                        <td>
                                            <span class="admin-report-student">
                                                <?= htmlspecialchars($report['student_name']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="admin-report-company">
                                                <?= htmlspecialchars($report['company_name']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="admin-report-job">
                                                <?= htmlspecialchars($report['job_title']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="admin-report-content">
                                                <?= nl2br(htmlspecialchars($report['content'])) ?>
                                            </div>
                                        </td>
                                        <td><?= htmlspecialchars($report['submitted_at']) ?></td>
                                        <td>
                                            <span class="admin-report-badge <?= in_array($report['registration_status'], ['ongoing', 'completed']) ? htmlspecialchars($report['registration_status']) : 'default' ?>">
                                                <?= htmlspecialchars(ucfirst($report['registration_status'])) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="admin-empty">
                        No reports have been submitted yet.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<?php include '../includes/footer.php'; ?>