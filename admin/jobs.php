<?php
require_once '../includes/auth.php';
require_once '../config/db.php';
require_once '../includes/functions.php';
requireRole('admin');

$flash = getFlash();

// Filter
$filterStatus  = $_GET['status']     ?? '';
$filterCompany = trim($_GET['company'] ?? '');

// Auto-close expired jobs
$pdo->exec("UPDATE jobs SET status = 'closed' WHERE status = 'open' AND deadline < CURDATE()");

// Query jobs + applicant count
$sql = "
    SELECT
        j.id,
        j.title,
        j.slots,
        j.deadline,
        j.status,
        c.company_name,
        ip.name AS period_name,
        COUNT(a.id) AS total_applicants,
        SUM(CASE WHEN a.admin_approved = 0 THEN 1 ELSE 0 END) AS pending_admin,
        SUM(CASE WHEN a.admin_approved = 1 AND a.company_approved = 0 THEN 1 ELSE 0 END) AS pending_company,
        SUM(CASE WHEN a.company_approved = 1 THEN 1 ELSE 0 END) AS accepted
    FROM jobs j
    INNER JOIN companies c ON j.company_id = c.id
    INNER JOIN internship_periods ip ON j.period_id = ip.id
    LEFT JOIN applications a ON a.job_id = j.id
    WHERE 1=1
";
$params = [];

if ($filterStatus !== '') {
    $sql    .= " AND j.status = ?";
    $params[] = $filterStatus;
}
if ($filterCompany !== '') {
    $sql    .= " AND c.company_name LIKE ?";
    $params[] = '%' . $filterCompany . '%';
}

$sql .= " GROUP BY j.id ORDER BY j.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Lấy applicants cho job đang expand (nếu có)
$expandJobId = (int)($_GET['expand'] ?? 0);
$applicants  = [];
if ($expandJobId > 0) {
    $s = $pdo->prepare("
        SELECT
            a.id AS app_id,
            a.cv_url,
            a.admin_approved,
            a.company_approved,
            a.status,
            a.applied_at,
            u.full_name AS student_name,
            s.student_code,
            s.gpa
        FROM applications a
        INNER JOIN students s ON a.student_id = s.id
        INNER JOIN users u ON s.user_id = u.id
        WHERE a.job_id = ?
        ORDER BY a.id DESC
    ");
    $s->execute([$expandJobId]);
    $applicants = $s->fetchAll(PDO::FETCH_ASSOC);
}

require_once '../includes/notifications.php';
include '../includes/header.php';

function appStatusLabel(array $a): string {
    if ((int)$a['admin_approved'] === 0)                                        return 'Waiting Admin';
    if ((int)$a['admin_approved'] === -1)                                       return 'Rejected by Admin';
    if ((int)$a['admin_approved'] === 1 && (int)$a['company_approved'] === 0)  return 'Approved → Waiting Company';
    if ((int)$a['company_approved'] === 1)                                      return 'Accepted';
    if ((int)$a['company_approved'] === -1)                                     return 'Rejected by Company';
    return ucfirst($a['status']);
}
function appStatusColor(array $a): string {
    if ((int)$a['admin_approved'] === 0)                                        return 'background:#f6e8a6;color:#a36a00;';
    if ((int)$a['admin_approved'] === -1)                                       return 'background:#ffe1e1;color:#ad3e3e;';
    if ((int)$a['admin_approved'] === 1 && (int)$a['company_approved'] === 0)  return 'background:#dce7ff;color:#265ad9;';
    if ((int)$a['company_approved'] === 1)                                      return 'background:#d8f2df;color:#187a3d;';
    if ((int)$a['company_approved'] === -1)                                     return 'background:#ffe1e1;color:#ad3e3e;';
    return 'background:#f1edff;color:#6157aa;';
}
?>

<style>
.admin-jobs-page { padding:8px 6px 28px; }
.admin-jobs-header { margin-bottom:22px; }
.admin-jobs-header h1 { margin:0 0 8px; font-size:44px; font-weight:800; color:#161b34; }
.admin-jobs-header p  { margin:0; font-size:17px; color:#707894; }

.admin-jobs-filter {
    display:flex; gap:12px; flex-wrap:wrap; align-items:flex-end;
    background:#fff; border:1px solid #ece9f7; border-radius:20px;
    padding:18px 22px; margin-bottom:22px;
    box-shadow:0 8px 22px rgba(31,34,51,.04);
}
.admin-jobs-filter label { font-size:12px; font-weight:800; color:#74809b; display:block; margin-bottom:5px; }
.admin-jobs-filter input,
.admin-jobs-filter select {
    height:40px; border:1px solid #ddd9ef; border-radius:12px;
    padding:0 12px; font-size:14px; outline:none; background:#fff; color:#1f2233; min-width:160px;
}
.admin-jobs-filter input:focus,
.admin-jobs-filter select:focus { border-color:#c7b7ff; }
.ajf-btn {
    height:40px; padding:0 16px; border:none; border-radius:12px;
    font-size:14px; font-weight:700; cursor:pointer;
}
.ajf-btn.apply { background:#cfc6ff; color:#1f2233; }
.ajf-btn.clear { background:#f6f2ff; color:#5c647d; text-decoration:none; display:inline-flex; align-items:center; }

/* Job cards */
.admin-job-card {
    background:#fff; border:1px solid #ece9f7; border-radius:24px;
    margin-bottom:14px; overflow:hidden;
    box-shadow:0 8px 20px rgba(31,34,51,.04);
}
.admin-job-head {
    display:grid;
    grid-template-columns: 2fr 1.2fr 0.8fr 0.8fr 0.8fr auto;
    gap:14px; align-items:center;
    padding:18px 24px; cursor:pointer;
    transition:background .15s;
}
.admin-job-head:hover { background:#faf8ff; }
.ajh-title { font-size:16px; font-weight:800; color:#1a1f36; }
.ajh-company { font-size:14px; color:#5a6280; font-weight:600; }
.ajh-meta { font-size:13px; color:#7f8496; margin-top:3px; }
.ajh-stat { text-align:center; }
.ajh-stat strong { display:block; font-size:22px; font-weight:800; color:#1a1f36; }
.ajh-stat span { font-size:11px; color:#9093a2; font-weight:600; }
.ajh-badge {
    display:inline-flex; align-items:center; padding:6px 12px;
    border-radius:999px; font-size:12px; font-weight:700;
}
.ajh-badge.open   { background:#d8f2df; color:#187a3d; }
.ajh-badge.closed { background:#f1edff; color:#6157aa; }
.ajh-toggle {
    width:32px; height:32px; border-radius:999px;
    background:#f6f2ff; color:#7f4df3; font-size:14px; font-weight:800;
    display:flex; align-items:center; justify-content:center;
    border:none; cursor:pointer; flex-shrink:0;
}

/* Applicants table */
.admin-job-applicants { border-top:1px solid #ece9f7; }
.aja-header {
    display:flex; justify-content:space-between; align-items:center;
    padding:14px 24px; background:#faf8ff;
}
.aja-header h3 { margin:0; font-size:16px; font-weight:800; color:#161b34; }
.aja-empty { padding:24px; text-align:center; color:#9093a2; }
.aja-table { width:100%; border-collapse:collapse; }
.aja-table th {
    text-align:left; padding:12px 20px;
    font-size:12px; font-weight:800; color:#74809b;
    border-bottom:1px solid #f0edf8;
}
.aja-table td {
    padding:13px 20px; border-bottom:1px solid #f7f4fb;
    font-size:14px; color:#1f2233; vertical-align:middle;
}
.aja-table tr:last-child td { border-bottom:none; }
.aja-name   { font-weight:700; }
.aja-code   { color:#6e7690; }
.aja-status {
    display:inline-flex; align-items:center; padding:5px 10px;
    border-radius:999px; font-size:12px; font-weight:700;
}
.aja-cv { color:#7f4df3; font-weight:700; font-size:13px; text-decoration:none; }
.aja-cv:hover { text-decoration:underline; }
.aja-approve-btn, .aja-reject-btn {
    height:32px; padding:0 12px; border:none; border-radius:10px;
    font-size:13px; font-weight:700; cursor:pointer;
}
.aja-approve-btn { background:#cfc6ff; color:#1f2233; }
.aja-reject-btn  { background:#efd867; color:#1f2233; margin-left:6px; }

@media(max-width:900px){
    .admin-job-head { grid-template-columns:1fr auto; }
    .ajh-stat { display:none; }
}
</style>

<div class="admin-shell">
    <aside class="admin-sidebar">
        <div>
            <div class="admin-brand"><h2>Admin Panel</h2></div>
            <nav class="admin-nav">
                <a href="/Uniworksmohinhhoa/admin/dashboard.php">Dashboard</a>
                <a href="/Uniworksmohinhhoa/admin/users.php">Users</a>
                <a href="/Uniworksmohinhhoa/admin/applications.php">Applications</a>
                <a href="/Uniworksmohinhhoa/admin/jobs.php" class="active">Jobs</a>
                <a href="/Uniworksmohinhhoa/admin/monitoring.php">Monitoring</a>
                <a href="/Uniworksmohinhhoa/admin/reports.php">Reports<?php if(!empty($notif['reports']) && $notif['reports']>0): ?><span class="notif-badge"><?= $notif['reports'] ?></span><?php endif; ?></a>
            </nav>
        </div>
        <div class="admin-sidebar__footer">
            <a href="/Uniworksmohinhhoa/public/logout.php">Logout</a>
        </div>
    </aside>

    <main class="admin-main">
        <div class="admin-jobs-page">
            <div class="admin-jobs-header">
                <h1>Job Management</h1>
                <p>View all internship postings, applicant counts, and approve/reject student applications directly.</p>
            </div>

            <?php if ($flash): ?>
                <div class="flash <?= htmlspecialchars($flash['type']) ?>" style="margin-bottom:18px;">
                    <?= htmlspecialchars($flash['message']) ?>
                </div>
            <?php endif; ?>

            <!-- Filter -->
            <div class="admin-jobs-filter">
                <form method="GET" action="/Uniworksmohinhhoa/admin/jobs.php"
                      style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;width:100%;">
                    <?php if ($expandJobId): ?>
                        <input type="hidden" name="expand" value="<?= $expandJobId ?>">
                    <?php endif; ?>
                    <div>
                        <label>Status</label>
                        <select name="status">
                            <option value="">All</option>
                            <option value="open"   <?= $filterStatus==='open'   ? 'selected':'' ?>>Open</option>
                            <option value="closed" <?= $filterStatus==='closed' ? 'selected':'' ?>>Closed</option>
                        </select>
                    </div>
                    <div>
                        <label>Company</label>
                        <input type="text" name="company" placeholder="Search company..."
                               value="<?= htmlspecialchars($filterCompany) ?>">
                    </div>
                    <button type="submit" class="ajf-btn apply">Filter</button>
                    <?php if ($filterStatus || $filterCompany): ?>
                        <a href="/Uniworksmohinhhoa/admin/jobs.php" class="ajf-btn clear">Clear</a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Jobs list -->
            <?php if (empty($jobs)): ?>
                <div style="padding:40px;text-align:center;color:#9093a2;">No jobs found.</div>
            <?php else: ?>
                <?php foreach ($jobs as $job): ?>
                    <?php $isExpanded = ($expandJobId === (int)$job['id']); ?>
                    <div class="admin-job-card">
                        <!-- Job header row — click to expand -->
                        <a href="/Uniworksmohinhhoa/admin/jobs.php?expand=<?= $isExpanded ? 0 : $job['id'] ?>&status=<?= urlencode($filterStatus) ?>&company=<?= urlencode($filterCompany) ?>"
                           style="text-decoration:none;display:block;">
                        <div class="admin-job-head">
                            <div>
                                <div class="ajh-title"><?= htmlspecialchars($job['title']) ?></div>
                                <div class="ajh-company"><?= htmlspecialchars($job['company_name']) ?></div>
                                <div class="ajh-meta">
                                    <?= htmlspecialchars($job['period_name']) ?>
                                    &nbsp;·&nbsp; Slots: <?= $job['slots'] ?>
                                    &nbsp;·&nbsp; Deadline: <?= date('d M Y', strtotime($job['deadline'])) ?>
                                </div>
                            </div>
                            <div class="ajh-stat">
                                <strong><?= $job['total_applicants'] ?></strong>
                                <span>Total</span>
                            </div>
                            <div class="ajh-stat">
                                <strong style="color:#a36a00;"><?= $job['pending_admin'] ?></strong>
                                <span>Wait Admin</span>
                            </div>
                            <div class="ajh-stat">
                                <strong style="color:#265ad9;"><?= $job['pending_company'] ?></strong>
                                <span>Wait Company</span>
                            </div>
                            <div class="ajh-stat">
                                <strong style="color:#187a3d;"><?= $job['accepted'] ?></strong>
                                <span>Accepted</span>
                            </div>
                            <div style="display:flex;align-items:center;gap:10px;">
                                <span class="ajh-badge <?= $job['status'] ?>">
                                    <?= ucfirst($job['status']) ?>
                                </span>
                                <button class="ajh-toggle"><?= $isExpanded ? '▲' : '▼' ?></button>
                            </div>
                        </div>
                        </a>

                        <?php if ($isExpanded): ?>
                        <!-- Applicants panel -->
                        <div class="admin-job-applicants">
                            <div class="aja-header">
                                <h3>Applicants for "<?= htmlspecialchars($job['title']) ?>"</h3>
                                <span style="font-size:13px;color:#9093a2;"><?= count($applicants) ?> student(s)</span>
                            </div>

                            <?php if (empty($applicants)): ?>
                                <div class="aja-empty">No students have applied for this job yet.</div>
                            <?php else: ?>
                                <div style="overflow-x:auto;">
                                <table class="aja-table">
                                    <thead>
                                        <tr>
                                            <th>Student</th>
                                            <th>Code</th>
                                            <th>GPA</th>
                                            <th>Applied</th>
                                            <th>Status</th>
                                            <th>CV</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($applicants as $ap): ?>
                                            <tr>
                                                <td class="aja-name"><?= htmlspecialchars($ap['student_name']) ?></td>
                                                <td class="aja-code"><?= htmlspecialchars($ap['student_code'] ?? '—') ?></td>
                                                <td><?= $ap['gpa'] ? number_format((float)$ap['gpa'],2) : '—' ?></td>
                                                <td style="color:#6e7690;"><?= date('d M Y', strtotime($ap['applied_at'])) ?></td>
                                                <td>
                                                    <span class="aja-status" style="<?= appStatusColor($ap) ?>">
                                                        <?= htmlspecialchars(appStatusLabel($ap)) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if (!empty($ap['cv_url'])): ?>
                                                        <a href="/Uniworksmohinhhoa/<?= htmlspecialchars($ap['cv_url']) ?>"
                                                           target="_blank" class="aja-cv">📄 CV</a>
                                                    <?php else: ?>
                                                        <span style="color:#9093a2;">—</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ((int)$ap['admin_approved'] === 0): ?>
                                                        <form action="/Uniworksmohinhhoa/actions/admin/approve_application_action.php"
                                                              method="POST" style="display:inline;">
                                                            <input type="hidden" name="application_id" value="<?= $ap['app_id'] ?>">
                                                            <input type="hidden" name="redirect_back"
                                                                   value="/Uniworksmohinhhoa/admin/jobs.php?expand=<?= $job['id'] ?>&status=<?= urlencode($filterStatus) ?>&company=<?= urlencode($filterCompany) ?>">
                                                            <button type="submit" class="aja-approve-btn">Approve</button>
                                                        </form>
                                                        <form action="/Uniworksmohinhhoa/actions/admin/reject_application_action.php"
                                                              method="POST" style="display:inline;">
                                                            <input type="hidden" name="application_id" value="<?= $ap['app_id'] ?>">
                                                            <input type="hidden" name="redirect_back"
                                                                   value="/Uniworksmohinhhoa/admin/jobs.php?expand=<?= $job['id'] ?>&status=<?= urlencode($filterStatus) ?>&company=<?= urlencode($filterCompany) ?>">
                                                            <button type="submit" class="aja-reject-btn">Reject</button>
                                                        </form>
                                                    <?php else: ?>
                                                        <span style="color:#9093a2;font-size:13px;font-weight:600;">Reviewed</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                </div>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php include '../includes/footer.php'; ?>
