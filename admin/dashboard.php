<?php
require_once '../includes/auth.php';
require_once '../config/db.php';
require_once '../includes/functions.php';
requireRole('admin');

$user = currentUser();

/*
|-------------------------------------------------------
| Dashboard statistics
|-------------------------------------------------------
*/
$totalUsers = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalStudents = (int)$pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
$totalCompanies = (int)$pdo->query("SELECT COUNT(*) FROM companies")->fetchColumn();
$pendingApplications = (int)$pdo->query("SELECT COUNT(*) FROM applications WHERE status = 'pending'")->fetchColumn();

/*
|-------------------------------------------------------
| Recent applications
|-------------------------------------------------------
*/
$recentApplicationsStmt = $pdo->query("
    SELECT 
        a.id,
        a.status,
        u.full_name AS student_name,
        j.title AS job_title,
        c.company_name
    FROM applications a
    INNER JOIN students s ON a.student_id = s.id
    INNER JOIN users u ON s.user_id = u.id
    INNER JOIN jobs j ON a.job_id = j.id
    INNER JOIN companies c ON j.company_id = c.id
    ORDER BY a.id DESC
    LIMIT 4
");
$recentApplications = $recentApplicationsStmt->fetchAll(PDO::FETCH_ASSOC);

/*
|-------------------------------------------------------
| Top partner companies
|-------------------------------------------------------
*/
$companiesStmt = $pdo->query("
    SELECT 
        c.company_name,
        COUNT(j.id) AS open_roles
    FROM companies c
    LEFT JOIN jobs j ON c.id = j.company_id
    GROUP BY c.id, c.company_name
    ORDER BY open_roles DESC, c.id DESC
    LIMIT 5
");
$topCompanies = $companiesStmt->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php';
?>

<style>
.admin-dashboard-page{
    padding: 8px 6px 24px;
}

.admin-sidebar{
    display:flex;
    flex-direction:column;
    justify-content:space-between;
    padding:20px 14px;
}

.admin-brand{
    padding:8px 8px 20px;
    margin-bottom:8px;
}

.admin-brand h2{
    margin:0;
    font-size:24px;
    line-height:1.2;
    color:#1b2038;
    font-weight:800;
}

.admin-nav{
    display:flex;
    flex-direction:column;
    gap:10px;
    margin-top:4px;
}

.admin-nav a{
    display:block;
    padding:14px 16px;
    border-radius:16px;
    font-weight:700;
    text-decoration:none;
}

.admin-topbar{
    display:block;
    margin-bottom:24px;
}

.admin-search-box{
    width:100%;
    max-width:820px;
    display:flex;
    align-items:center;
    gap:12px;
    background:#fff;
    border:1px solid #ece9f7;
    border-radius:20px;
    padding:14px 18px;
    box-shadow:0 8px 24px rgba(31,34,51,.04);
}

.admin-search-box span{
    color:#8d94ab;
    font-size:18px;
}

.admin-search-box input{
    width:100%;
    border:none;
    outline:none;
    background:transparent;
    font-size:16px;
    color:#1f2233;
}

.admin-hero{
    margin-bottom:26px;
}

.admin-hero h1{
    margin:0 0 10px;
    font-size:54px;
    line-height:1.05;
    color:#161b34;
    font-weight:800;
}

.admin-hero p{
    margin:0;
    font-size:20px;
    color:#6f7790;
    line-height:1.6;
}

.admin-stats-grid{
    display:grid;
    grid-template-columns:repeat(4, minmax(0, 1fr));
    gap:20px;
    margin-bottom:28px;
}

.admin-stat-card{
    border-radius:28px;
    padding:26px;
    box-shadow:0 12px 28px rgba(31,34,51,.05);
    border:1px solid rgba(0,0,0,.03);
}

.admin-stat-card.yellow{
    background:#efd867;
}

.admin-stat-card.purple{
    background:#cfc6ff;
}

.admin-stat-top{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:28px;
}

.admin-stat-icon{
    width:52px;
    height:52px;
    border-radius:18px;
    display:flex;
    align-items:center;
    justify-content:center;
    background:rgba(255,255,255,.42);
    font-size:24px;
    color:#25304f;
    font-weight:700;
}

.admin-stat-growth{
    font-size:14px;
    font-weight:800;
    color:#16a34a;
}

.admin-stat-label{
    margin:0 0 10px;
    color:#53607d;
    font-size:16px;
    font-weight:600;
}

.admin-stat-value{
    margin:0;
    font-size:50px;
    line-height:1;
    color:#11162d;
    font-weight:800;
}

.admin-dashboard-grid{
    display:grid;
    grid-template-columns: 1.8fr .9fr;
    gap:24px;
    margin-bottom:26px;
}

.admin-panel{
    background:#fff;
    border:1px solid #ece9f7;
    border-radius:30px;
    padding:28px;
    box-shadow:0 12px 28px rgba(31,34,51,.05);
}

.admin-panel-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:16px;
    margin-bottom:18px;
}

.admin-panel-header h2{
    margin:0 0 6px;
    color:#161b34;
    font-size:24px;
    font-weight:800;
}

.admin-panel-header p{
    margin:0;
    color:#7a8198;
    font-size:15px;
}

.admin-filter-chip{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    padding:12px 16px;
    border-radius:16px;
    background:#f7f4ff;
    color:#555f7d;
    font-size:14px;
    font-weight:700;
    white-space:nowrap;
}

.admin-chart-area{
    height:320px;
    position:relative;
    border-radius:22px;
    background:
        linear-gradient(to top, transparent 0, transparent calc(100% - 1px), rgba(220,225,239,.65) calc(100% - 1px)),
        repeating-linear-gradient(
            to top,
            #ffffff,
            #ffffff 52px,
            #f5f2ff 52px,
            #f5f2ff 53px
        );
    overflow:hidden;
    padding:18px 18px 26px;
}

.admin-chart-svg{
    width:100%;
    height:100%;
}

.admin-side-list{
    display:flex;
    flex-direction:column;
    gap:14px;
}

.admin-application-item{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:14px;
    padding:14px 0;
    border-bottom:1px solid #f1edf8;
}

.admin-application-item:last-child{
    border-bottom:none;
}

.admin-app-left{
    display:flex;
    align-items:center;
    gap:14px;
    min-width:0;
}

.admin-app-avatar{
    width:48px;
    height:48px;
    border-radius:16px;
    background:#ffe0cf;
    color:#1f2233;
    display:flex;
    align-items:center;
    justify-content:center;
    font-weight:800;
    font-size:18px;
    flex-shrink:0;
}

.admin-app-name{
    margin:0 0 4px;
    font-size:16px;
    font-weight:800;
    color:#171c34;
}

.admin-app-meta{
    margin:0;
    font-size:14px;
    color:#77809a;
    white-space:nowrap;
    overflow:hidden;
    text-overflow:ellipsis;
    max-width:220px;
}

.admin-status-badge{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    padding:9px 12px;
    border-radius:999px;
    font-size:13px;
    font-weight:800;
    white-space:nowrap;
}

.admin-status-badge.pending{
    background:#f6e8a6;
    color:#a36a00;
}

.admin-status-badge.accepted{
    background:#d8f2df;
    color:#187a3d;
}

.admin-status-badge.reviewing{
    background:#dce7ff;
    color:#265ad9;
}

.admin-status-badge.default{
    background:#f1edff;
    color:#6157aa;
}

.admin-panel-btn{
    margin-top:18px;
    width:100%;
    height:52px;
    border:none;
    border-radius:18px;
    background:#f6f2ff;
    color:#3d4560;
    font-size:16px;
    font-weight:800;
    text-decoration:none;
    display:flex;
    align-items:center;
    justify-content:center;
    transition:.2s ease;
}

.admin-panel-btn:hover{
    background:#eee7ff;
}

.admin-table-panel{
    background:#fff;
    border:1px solid #ece9f7;
    border-radius:30px;
    box-shadow:0 12px 28px rgba(31,34,51,.05);
    overflow:hidden;
}

.admin-table-head{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:16px;
    padding:26px 28px 18px;
}

.admin-table-head h2{
    margin:0;
    color:#161b34;
    font-size:24px;
    font-weight:800;
}

.admin-table-link{
    color:#c6b6ff;
    text-decoration:none;
    font-weight:800;
    font-size:16px;
}

.admin-table-link:hover{
    color:#b5a0ff;
}

.admin-simple-table{
    width:100%;
    border-collapse:collapse;
}

.admin-simple-table th{
    text-align:left;
    padding:18px 28px;
    background:#faf8ff;
    color:#74809b;
    font-size:14px;
    font-weight:800;
    border-bottom:1px solid #ece9f7;
}

.admin-simple-table td{
    padding:20px 28px;
    border-bottom:1px solid #f1edf8;
    color:#1f2233;
    font-size:16px;
}

.admin-simple-table tr:last-child td{
    border-bottom:none;
}

.admin-company-cell{
    display:flex;
    align-items:center;
    gap:14px;
    font-weight:700;
}

.admin-company-logo{
    width:36px;
    height:36px;
    border-radius:12px;
    background:#f6f2ff;
    color:#b9a9ff;
    display:flex;
    align-items:center;
    justify-content:center;
    font-weight:800;
}

.admin-dot{
    width:10px;
    height:10px;
    border-radius:50%;
    background:#22c55e;
    display:inline-block;
    margin-right:10px;
}

.admin-quick-actions{
    margin-top:24px;
    display:flex;
    gap:14px;
    flex-wrap:wrap;
}

.admin-quick-btn{
    padding:14px 18px;
    border-radius:16px;
    text-decoration:none;
    font-weight:800;
    font-size:15px;
    transition:.2s ease;
}

.admin-quick-btn.purple{
    background:#cfc6ff;
    color:#1f2233;
}

.admin-quick-btn.yellow{
    background:#efd867;
    color:#1f2233;
}

.admin-quick-btn.ghost{
    background:#f7f4ff;
    color:#4f5770;
}

.admin-quick-btn:hover{
    transform:translateY(-1px);
}

@media (max-width: 1200px){
    .admin-stats-grid{
        grid-template-columns:repeat(2, minmax(0, 1fr));
    }

    .admin-dashboard-grid{
        grid-template-columns:1fr;
    }
}

@media (max-width: 768px){
    .admin-search-box{
        max-width:none;
    }

    .admin-hero h1{
        font-size:40px;
    }

    .admin-hero p{
        font-size:17px;
    }

    .admin-stats-grid{
        grid-template-columns:1fr;
    }

    .admin-table-head{
        flex-direction:column;
        align-items:flex-start;
    }

    .admin-simple-table{
        display:block;
        overflow-x:auto;
        white-space:nowrap;
    }
}
</style>

<?php
function adminBadgeClass(?string $status): string {
    $status = strtolower((string)$status);

    if (in_array($status, ['pending', 'waiting_school_approval'], true)) {
        return 'pending';
    }

    if (in_array($status, ['approved', 'accepted', 'company_accepted'], true)) {
        return 'accepted';
    }

    if (in_array($status, ['reviewing', 'school_approved'], true)) {
        return 'reviewing';
    }

    return 'default';
}
?>

<div class="admin-shell">
    <aside class="admin-sidebar">
        <div>
            <div class="admin-brand">
                <h2>Admin Panel</h2>
            </div>

            <nav class="admin-nav">
                <a href="/Uniworksmohinhhoa/admin/dashboard.php" class="active">Dashboard</a>
                <a href="/Uniworksmohinhhoa/admin/users.php">Users</a>
                <a href="/Uniworksmohinhhoa/admin/applications.php">Applications</a>
                <a href="/Uniworksmohinhhoa/admin/monitoring.php">Monitoring</a>
                <a href="/Uniworksmohinhhoa/admin/reports.php">Reports</a>
            </nav>
        </div>

        <div class="admin-sidebar__footer">
            <a href="/Uniworksmohinhhoa/public/logout.php">Logout</a>
        </div>
    </aside>

    <main class="admin-main">
        <div class="admin-dashboard-page">
            <div class="admin-topbar">
                <div class="admin-search-box">
                    <span>⌕</span>
                    <input type="text" placeholder="Search data, students, or companies..." />
                </div>
            </div>

            <div class="admin-hero">
                <h1>System Statistics</h1>
                <p>Real-time overview of platform activity across users, applications, and internship progress.</p>

                <div class="admin-quick-actions">
                    <a href="/Uniworksmohinhhoa/admin/users.php" class="admin-quick-btn purple">Manage Users</a>
                    <a href="/Uniworksmohinhhoa/admin/applications.php" class="admin-quick-btn yellow">Review Applications</a>
                    <a href="/Uniworksmohinhhoa/admin/reports.php" class="admin-quick-btn ghost">View Reports</a>
                </div>
            </div>

            <div class="admin-stats-grid">
                <div class="admin-stat-card yellow">
                    <div class="admin-stat-top">
                        <div class="admin-stat-icon">🎓</div>
                        <div class="admin-stat-growth">+12%</div>
                    </div>
                    <p class="admin-stat-label">Total Students</p>
                    <h3 class="admin-stat-value"><?= number_format($totalStudents) ?></h3>
                </div>

                <div class="admin-stat-card purple">
                    <div class="admin-stat-top">
                        <div class="admin-stat-icon">🏢</div>
                        <div class="admin-stat-growth">+5%</div>
                    </div>
                    <p class="admin-stat-label">Partner Companies</p>
                    <h3 class="admin-stat-value"><?= number_format($totalCompanies) ?></h3>
                </div>

                <div class="admin-stat-card yellow">
                    <div class="admin-stat-top">
                        <div class="admin-stat-icon">📋</div>
                        <div class="admin-stat-growth">+8%</div>
                    </div>
                    <p class="admin-stat-label">Total Users</p>
                    <h3 class="admin-stat-value"><?= number_format($totalUsers) ?></h3>
                </div>

                <div class="admin-stat-card purple">
                    <div class="admin-stat-top">
                        <div class="admin-stat-icon">🗂️</div>
                        <div class="admin-stat-growth">+15%</div>
                    </div>
                    <p class="admin-stat-label">Pending Applications</p>
                    <h3 class="admin-stat-value"><?= number_format($pendingApplications) ?></h3>
                </div>
            </div>

            <div class="admin-dashboard-grid">
                <section class="admin-panel">
                    <div class="admin-panel-header">
                        <div>
                            <h2>Placement Trends</h2>
                            <p>Simple growth overview across recent months</p>
                        </div>

                        <div class="admin-filter-chip">Last 6 Months</div>
                    </div>

                    <div class="admin-chart-area">
                        <svg viewBox="0 0 700 300" class="admin-chart-svg" preserveAspectRatio="none">
                            <polyline
                                fill="none"
                                stroke="#7fb34d"
                                stroke-width="3"
                                points="40,190 150,165 260,135 370,195 480,135 590,105"
                            />
                            <circle cx="40" cy="190" r="5" fill="#3c6a2b"></circle>
                            <circle cx="150" cy="165" r="5" fill="#3c6a2b"></circle>
                            <circle cx="260" cy="135" r="5" fill="#3c6a2b"></circle>
                            <circle cx="370" cy="195" r="5" fill="#3c6a2b"></circle>
                            <circle cx="480" cy="135" r="5" fill="#3c6a2b"></circle>
                            <circle cx="590" cy="105" r="5" fill="#3c6a2b"></circle>

                            <text x="35" y="285" font-size="13" fill="#8b96b2">JAN</text>
                            <text x="145" y="285" font-size="13" fill="#8b96b2">FEB</text>
                            <text x="255" y="285" font-size="13" fill="#8b96b2">MAR</text>
                            <text x="365" y="285" font-size="13" fill="#8b96b2">APR</text>
                            <text x="475" y="285" font-size="13" fill="#8b96b2">MAY</text>
                            <text x="585" y="285" font-size="13" fill="#8b96b2">JUN</text>
                        </svg>
                    </div>
                </section>

                <aside class="admin-panel">
                    <div class="admin-panel-header">
                        <div>
                            <h2>Recent Applications</h2>
                            <p>Latest student application activity</p>
                        </div>
                    </div>

                    <div class="admin-side-list">
                        <?php if (!empty($recentApplications)): ?>
                            <?php foreach ($recentApplications as $item): ?>
                                <div class="admin-application-item">
                                    <div class="admin-app-left">
                                        <div class="admin-app-avatar">
                                            <?= htmlspecialchars(strtoupper(substr($item['student_name'], 0, 1))) ?>
                                        </div>

                                        <div>
                                            <p class="admin-app-name"><?= htmlspecialchars($item['student_name']) ?></p>
                                            <p class="admin-app-meta">
                                                <?= htmlspecialchars($item['job_title']) ?> at <?= htmlspecialchars($item['company_name']) ?>
                                            </p>
                                        </div>
                                    </div>

                                    <span class="admin-status-badge <?= adminBadgeClass($item['status']) ?>">
                                        <?= htmlspecialchars(ucwords(str_replace('_', ' ', $item['status']))) ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p style="margin:0; color:#7a8198;">No recent applications found.</p>
                        <?php endif; ?>
                    </div>

                    <a href="/Uniworksmohinhhoa/admin/applications.php" class="admin-panel-btn">View All Applications</a>
                </aside>
            </div>

            <section class="admin-table-panel">
                <div class="admin-table-head">
                    <h2>Top Partner Companies</h2>
                    <a href="/Uniworksmohinhhoa/admin/users.php" class="admin-table-link">Manage Partners →</a>
                </div>

                <table class="admin-simple-table">
                    <thead>
                        <tr>
                            <th>Company</th>
                            <th>Open Roles</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($topCompanies)): ?>
                            <?php foreach ($topCompanies as $partner): ?>
                                <tr>
                                    <td>
                                        <div class="admin-company-cell">
                                            <div class="admin-company-logo">
                                                <?= htmlspecialchars(strtoupper(substr($partner['company_name'], 0, 1))) ?>
                                            </div>
                                            <?= htmlspecialchars($partner['company_name']) ?>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($partner['open_roles']) ?></td>
                                    <td><span class="admin-dot"></span> Active</td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3">No company data available.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </section>
        </div>
    </main>
</div>

<?php include '../includes/footer.php'; ?>