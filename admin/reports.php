<?php
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/admin_layout.php';

<<<<<<< Updated upstream
$majorStats = $conn->query("
    SELECT m.name, COUNT(s.id) AS total
    FROM majors m
    LEFT JOIN students s ON m.id = s.major_id
    GROUP BY m.id, m.name
    ORDER BY total DESC, m.name ASC
");
=======
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
| Reports list — with date filter + search
|-------------------------------------------------------
*/
$dateFrom  = trim($_GET['date_from'] ?? '');
$dateTo    = trim($_GET['date_to']   ?? '');
$search    = trim($_GET['search']    ?? '');

$sql = "
    SELECT
        r.id,
        r.content,
        r.file_url,
        r.submitted_at,
        ir.status AS registration_status,
        u.full_name AS student_name,
        s.student_code,
        c.company_name,
        j.title AS job_title
    FROM reports r
    INNER JOIN internship_registrations ir ON r.registration_id = ir.id
    INNER JOIN applications a ON ir.application_id = a.id
    INNER JOIN students s ON a.student_id = s.id
    INNER JOIN users u ON s.user_id = u.id
    INNER JOIN jobs j ON a.job_id = j.id
    INNER JOIN companies c ON j.company_id = c.id
    WHERE 1=1
";
$params = [];

if ($dateFrom !== '') {
    $sql     .= " AND DATE(r.submitted_at) >= ?";
    $params[] = $dateFrom;
}
if ($dateTo !== '') {
    $sql     .= " AND DATE(r.submitted_at) <= ?";
    $params[] = $dateTo;
}
if ($search !== '') {
    $sql     .= " AND (u.full_name LIKE ? OR c.company_name LIKE ? OR j.title LIKE ? OR s.student_code LIKE ?)";
    $like     = '%' . $search . '%';
    $params[] = $like; $params[] = $like; $params[] = $like; $params[] = $like;
}

$sql .= " ORDER BY r.submitted_at DESC, r.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
>>>>>>> Stashed changes

$appStats = $conn->query("SELECT status, COUNT(*) AS count FROM applications GROUP BY status");
$maxStudentsPerMajor = 1;
$majorRows = [];
$appRows = [];

if ($majorStats instanceof mysqli_result) {
    while ($row = $majorStats->fetch_assoc()) {
        $majorRows[] = $row;
        $maxStudentsPerMajor = max($maxStudentsPerMajor, (int) $row['total']);
    }
}

if ($appStats instanceof mysqli_result) {
    while ($row = $appStats->fetch_assoc()) {
        $appRows[] = $row;
    }
}

ob_start();
?>
<button type="button" onclick="window.print()" class="admin-button--soft"><i class="fas fa-print"></i> Export</button>
<?php
$actionsHtml = ob_get_clean();

admin_render_start(
    'Reports | Placement Hub',
    'reports',
    'Reports & Analytics',
    'Overview of academic distribution and application pipeline status',
    $actionsHtml
);
?>

<section class="admin-grid admin-grid--reports">
    <article class="admin-card">
        <div class="admin-card__head">
            <div>
                <h3>Students by Major</h3>
                <span class="admin-card__eyebrow">Distribution of students across academic majors</span>
            </div>
<<<<<<< Updated upstream
=======

            <nav class="admin-nav">
                <a href="/Uniworksmohinhhoa/admin/dashboard.php">Dashboard</a>
                <a href="/Uniworksmohinhhoa/admin/users.php">Users</a>
                <a href="/Uniworksmohinhhoa/admin/company_approvals.php">Companies</a>
                <a href="/Uniworksmohinhhoa/admin/applications.php">Applications</a>
                <a href="/Uniworksmohinhhoa/admin/monitoring.php">Monitoring</a>
                <a href="/Uniworksmohinhhoa/admin/reports.php" class="active">Reports</a>
            </nav>
>>>>>>> Stashed changes
        </div>

        <div class="admin-bars">
            <?php if ($majorRows !== []): ?>
                <?php foreach ($majorRows as $major): ?>
                    <?php $percent = min(100, ((int) $major['total'] / $maxStudentsPerMajor) * 100); ?>
                    <div class="admin-bar">
                        <div class="admin-bar__top">
                            <span><?php echo htmlspecialchars($major['name']); ?></span>
                            <strong><?php echo (int) $major['total']; ?> students</strong>
                        </div>
                        <div class="admin-bar__track">
                            <div class="admin-bar__fill" style="width: <?php echo round($percent, 2); ?>%;"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="admin-empty">No major statistics available.</div>
            <?php endif; ?>
        </div>
    </article>

<<<<<<< Updated upstream
    <article class="admin-card">
        <div class="admin-card__head">
            <div>
                <h3>Application Status Summary</h3>
                <span class="admin-card__eyebrow">Current counts by review state</span>
=======
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

                <!-- Filter bar -->
                <form method="GET" action=""
                      style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;padding:0 28px 20px;">
                    <div style="flex:1;min-width:180px;">
                        <label style="display:block;font-size:12px;font-weight:800;color:#74809b;margin-bottom:6px;">Search</label>
                        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
                               placeholder="Student, company, job, code..."
                               style="width:100%;height:40px;border:1px solid #ddd9ef;border-radius:12px;padding:0 13px;font-size:13px;outline:none;font-family:inherit;background:#fff;">
                    </div>
                    <div>
                        <label style="display:block;font-size:12px;font-weight:800;color:#74809b;margin-bottom:6px;">From Date</label>
                        <input type="date" name="date_from" value="<?= htmlspecialchars($dateFrom) ?>"
                               style="height:40px;border:1px solid #ddd9ef;border-radius:12px;padding:0 12px;font-size:13px;outline:none;font-family:inherit;background:#fff;">
                    </div>
                    <div>
                        <label style="display:block;font-size:12px;font-weight:800;color:#74809b;margin-bottom:6px;">To Date</label>
                        <input type="date" name="date_to" value="<?= htmlspecialchars($dateTo) ?>"
                               style="height:40px;border:1px solid #ddd9ef;border-radius:12px;padding:0 12px;font-size:13px;outline:none;font-family:inherit;background:#fff;">
                    </div>
                    <div style="display:flex;gap:8px;align-items:flex-end;">
                        <button type="submit"
                                style="height:40px;padding:0 18px;border:none;border-radius:12px;background:#cfc6ff;color:#1f2233;font-size:13px;font-weight:800;cursor:pointer;">
                            Filter
                        </button>
                        <?php if ($dateFrom !== '' || $dateTo !== '' || $search !== ''): ?>
                            <a href="/Uniworksmohinhhoa/admin/reports.php"
                               style="height:40px;padding:0 14px;border-radius:12px;background:#f6f2ff;color:#5e6680;font-size:13px;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;">
                                Reset
                            </a>
                        <?php endif; ?>
                    </div>
                </form>

                <?php if (!empty($reports)): ?>
                    <div class="admin-reports-table-wrap">
                        <table class="admin-reports-table">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Company</th>
                                    <th>Job</th>
                                    <th>Report Content</th>
                                    <th>File</th>
                                    <th>Submitted At</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reports as $report): ?>
                                    <tr>
                                        <td>
                                            <div class="admin-report-student"><?= htmlspecialchars($report['student_name']) ?></div>
                                            <div style="font-size:12px;color:#8a8fa3;margin-top:2px;"><?= htmlspecialchars($report['student_code']) ?></div>
                                        </td>
                                        <td><span class="admin-report-company"><?= htmlspecialchars($report['company_name']) ?></span></td>
                                        <td><span class="admin-report-job"><?= htmlspecialchars($report['job_title']) ?></span></td>
                                        <td>
                                            <div class="admin-report-content">
                                                <?= nl2br(htmlspecialchars(
                                                    mb_strlen($report['content']) > 200
                                                        ? mb_substr($report['content'], 0, 200) . '...'
                                                        : $report['content']
                                                )) ?>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if (!empty($report['file_url'])): ?>
                                                <a href="/Uniworksmohinhhoa/<?= htmlspecialchars($report['file_url']) ?>"
                                                   target="_blank"
                                                   style="display:inline-flex;align-items:center;gap:5px;padding:7px 12px;border-radius:10px;background:#cfc6f6;color:#17172b;font-size:13px;font-weight:700;text-decoration:none;white-space:nowrap;">
                                                    📄 Download
                                                </a>
                                            <?php else: ?>
                                                <span style="color:#a0a3b1;font-size:13px;">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="white-space:nowrap;font-size:13px;color:#53607d;">
                                            <?= date('M d, Y', strtotime($report['submitted_at'])) ?>
                                            <div style="font-size:11px;color:#a0a3b1;"><?= date('H:i', strtotime($report['submitted_at'])) ?></div>
                                        </td>
                                        <td>
                                            <span class="admin-report-badge <?= in_array($report['registration_status'], ['ongoing','completed']) ? htmlspecialchars($report['registration_status']) : 'default' ?>">
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
                        <?= ($dateFrom || $dateTo || $search) ? 'No reports match your filter.' : 'No reports have been submitted yet.' ?>
                    </div>
                <?php endif; ?>
>>>>>>> Stashed changes
            </div>
        </div>

        <table class="admin-table">
            <thead>
                <tr>
                    <th>Status</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($appRows !== []): ?>
                    <?php foreach ($appRows as $row): ?>
                        <?php $status = admin_status_class($row['status'] ?? 'pending'); ?>
                        <tr>
                            <td>
                                <span class="admin-pill admin-pill--<?php echo htmlspecialchars($status); ?>">
                                    <?php echo htmlspecialchars(ucfirst($status)); ?>
                                </span>
                            </td>
                            <td><?php echo (int) $row['count']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="2" class="admin-empty">No application data available.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </article>
</section>

<?php
admin_render_end();
$conn->close();
