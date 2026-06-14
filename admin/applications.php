<?php
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/admin_layout.php';

$query = "SELECT
            a.id AS app_id,
            u.full_name,
            s.student_code,
            j.title AS job_title,
            c.company_name,
            a.applied_at,
            a.status,
            a.admin_approved,
            a.cv_url
          FROM applications a
          JOIN students s ON a.student_id = s.id
          JOIN users u ON s.user_id = u.id
          JOIN jobs j ON a.job_id = j.id
          JOIN companies c ON j.company_id = c.id
          ORDER BY a.applied_at DESC";

$result = $conn->query($query);
if (!$result) {
    die('Query failed: ' . $conn->error);
}

$totalApplications = (int) ($conn->query("SELECT COUNT(*) AS count FROM applications")->fetch_assoc()['count'] ?? 0);
$pendingApplications = (int) ($conn->query("SELECT COUNT(*) AS count FROM applications WHERE status = 'pending'")->fetch_assoc()['count'] ?? 0);
$approvedApplications = (int) ($conn->query("SELECT COUNT(*) AS count FROM applications WHERE status = 'approved'")->fetch_assoc()['count'] ?? 0);
$rejectedApplications = (int) ($conn->query("SELECT COUNT(*) AS count FROM applications WHERE status = 'rejected'")->fetch_assoc()['count'] ?? 0);

$alerts = [
    'approved' => ['type' => 'success', 'text' => 'Application approved successfully.'],
    'rejected' => ['type' => 'success', 'text' => 'Application rejected successfully.'],
    'failed' => ['type' => 'error', 'text' => 'Action failed. Please try again.'],
    'invalid_id' => ['type' => 'error', 'text' => 'Invalid application selected.'],
];

ob_start();
?>
<a href="dashboard.php" class="admin-button--soft"><i class="fas fa-arrow-left"></i> Dashboard</a>
<?php
$actionsHtml = ob_get_clean();

admin_render_start(
    'Applications | Placement Hub',
    'applications',
    'Applications',
    'Review internship applications and manage approval decisions',
    $actionsHtml
);
?>

<<<<<<< Updated upstream
<?php if (isset($_GET['msg'], $alerts[$_GET['msg']])): ?>
    <div class="admin-alert admin-alert--<?php echo $alerts[$_GET['msg']]['type']; ?>">
        <?php echo htmlspecialchars($alerts[$_GET['msg']]['text']); ?>
=======
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

.admin-app-page{
    padding: 8px 6px 24px;
}

.admin-app-header{
    margin-bottom: 24px;
}

.admin-app-header h1{
    margin: 0 0 10px;
    font-size: 46px;
    line-height: 1.08;
    color: #161b34;
    font-weight: 800;
}

.admin-app-header p{
    margin: 0;
    font-size: 18px;
    color: #707894;
    line-height: 1.6;
}

.admin-app-card{
    background: #fff;
    border: 1px solid #ece9f7;
    border-radius: 30px;
    box-shadow: 0 12px 28px rgba(31,34,51,.05);
    overflow: hidden;
}

.admin-app-card__top{
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    padding: 24px 28px 18px;
}

.admin-app-card__top h2{
    margin: 0;
    font-size: 24px;
    color: #161b34;
    font-weight: 800;
}

.admin-app-chip{
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 10px 14px;
    border-radius: 999px;
    background: #f6f2ff;
    color: #6259aa;
    font-size: 14px;
    font-weight: 700;
}

.admin-app-table-wrap{
    overflow-x: auto;
}

.admin-app-table{
    width: 100%;
    border-collapse: collapse;
    min-width: 1100px;
}

.admin-app-table th{
    text-align: left;
    padding: 18px 28px;
    background: #faf8ff;
    color: #74809b;
    font-size: 14px;
    font-weight: 800;
    border-bottom: 1px solid #ece9f7;
}

.admin-app-table td{
    padding: 18px 28px;
    border-bottom: 1px solid #f1edf8;
    color: #1f2233;
    font-size: 16px;
    vertical-align: middle;
}

.admin-app-table tr:last-child td{
    border-bottom: none;
}

.admin-student-cell{
    display: flex;
    align-items: center;
    gap: 14px;
    min-width: 0;
}

.admin-student-avatar{
    width: 44px;
    height: 44px;
    border-radius: 14px;
    background: #ffe0cf;
    color: #1f2233;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 18px;
    flex-shrink: 0;
}

.admin-student-name{
    margin: 0;
    font-size: 16px;
    font-weight: 800;
    color: #171c34;
}

.admin-job-text,
.admin-company-text{
    color: #4d566f;
    font-weight: 600;
}

.admin-status-badge{
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 10px 14px;
    border-radius: 999px;
    font-size: 13px;
    font-weight: 800;
    white-space: nowrap;
}

.admin-status-badge.pending{
    background: #f6e8a6;
    color: #a36a00;
}

.admin-status-badge.accepted{
    background: #d8f2df;
    color: #187a3d;
}

.admin-status-badge.reviewing{
    background: #dce7ff;
    color: #265ad9;
}

.admin-status-badge.rejected{
    background: #ffe1e1;
    color: #ad3e3e;
}

.admin-status-badge.default{
    background: #f1edff;
    color: #6157aa;
}

.admin-action-group{
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.admin-action-btn{
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 98px;
    height: 42px;
    padding: 0 16px;
    border: none;
    border-radius: 14px;
    text-decoration: none;
    font-size: 14px;
    font-weight: 800;
    cursor: pointer;
    transition: .2s ease;
}

.admin-action-btn:hover{
    transform: translateY(-1px);
}

.admin-action-btn.profile{
    background:#f6f2ff;
    color:#5f57a8;
}

.admin-action-btn.profile:hover{
    background:#eee6ff;
}

.admin-action-btn.approve{
    background: #cfc6ff;
    color: #1f2233;
}

.admin-action-btn.approve:hover{
    background: #c1b3ff;
}

.admin-action-btn.reject{
    background: #efd867;
    color: #1f2233;
}

.admin-action-btn.reject:hover{
    background: #e7cf5d;
}

.admin-action-note{
    color: #7c849c;
    font-size: 14px;
    font-weight: 700;
}

.admin-empty{
    padding: 40px 28px;
    text-align: center;
    color: #7a8198;
}

@media (max-width: 768px){
    .admin-app-header h1{
        font-size: 38px;
    }

    .admin-app-header p{
        font-size: 16px;
    }

    .admin-app-card__top{
        flex-direction: column;
        align-items: flex-start;
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
                <a href="/Uniworksmohinhhoa/admin/company_approvals.php">Companies</a>
                <a href="/Uniworksmohinhhoa/admin/applications.php" class="active">Applications</a>
                <a href="/Uniworksmohinhhoa/admin/monitoring.php">Monitoring</a>
                <a href="/Uniworksmohinhhoa/admin/reports.php">Reports</a>
            </nav>
        </div>

        <div class="admin-sidebar__footer">
            <a href="/Uniworksmohinhhoa/public/logout.php">Logout</a>
        </div>
    </aside>

    <main class="admin-main">
        <div class="admin-app-page">
            <div class="admin-app-header">
                <h1>Applications</h1>
                <p>Review student internship applications before they are forwarded to companies.</p>
            </div>

            <?php if ($flash): ?>
                <div class="flash <?= htmlspecialchars($flash['type']) ?>" style="margin-bottom:18px;">
                    <?= htmlspecialchars($flash['message']) ?>
                </div>
            <?php endif; ?>

            <div class="admin-app-card">
                <div class="admin-app-card__top">
                    <h2>Application Review Queue</h2>
                    <div class="admin-app-chip">
                        Total: <?= count($applications) ?> applications
                    </div>
                </div>

                <?php if (!empty($applications)): ?>
                    <div class="admin-app-table-wrap">
                        <table class="admin-app-table">
                            <thead>
                                <tr>
                                    <th>App ID</th>
                                    <th>Student</th>
                                    <th>Job</th>
                                    <th>Company</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($applications as $app): ?>
                                    <tr>
                                        <td><span style="font-size:12px;font-weight:700;color:#7a8096;background:#f4f1ff;padding:4px 10px;border-radius:999px;">APP-<?= $app['id'] ?></span></td>
                                        <td>
                                            <div class="admin-student-cell">
                                                <div class="admin-student-avatar">
                                                    <?= htmlspecialchars(strtoupper(substr($app['student_name'], 0, 1))) ?>
                                                </div>
                                                <p class="admin-student-name"><?= htmlspecialchars($app['student_name']) ?></p>
                                            </div>
                                        </td>

                                        <td>
                                            <span class="admin-job-text"><?= htmlspecialchars($app['job_title']) ?></span>
                                        </td>

                                        <td>
                                            <span class="admin-company-text"><?= htmlspecialchars($app['company_name']) ?></span>
                                        </td>

                                        <td>
                                            <span class="admin-status-badge <?= adminStatusClass($app) ?>">
                                                <?= htmlspecialchars(adminApplicationStatus($app)) ?>
                                            </span>
                                        </td>

                                       <td>
    <div class="admin-action-group">
        <a 
            href="/Uniworksmohinhhoa/admin/student_detail.php?id=<?= $app['student_id'] ?>" 
            class="admin-action-btn profile"
        >
            View Profile
        </a>

        <?php if ((int)$app['admin_approved'] === 0): ?>
            <form action="/Uniworksmohinhhoa/actions/admin/approve_application_action.php" method="POST" style="display:inline;">
                <input type="hidden" name="application_id" value="<?= $app['id'] ?>">
                <button type="submit" class="admin-action-btn approve">Approve</button>
            </form>

            <form action="/Uniworksmohinhhoa/actions/admin/reject_application_action.php" method="POST" style="display:inline;">
                <input type="hidden" name="application_id" value="<?= $app['id'] ?>">
                <button type="submit" class="admin-action-btn reject">Reject</button>
            </form>
        <?php else: ?>
            <span class="admin-action-note">Reviewed</span>
        <?php endif; ?>
>>>>>>> Stashed changes
    </div>
<?php elseif (isset($_GET['error'], $alerts[$_GET['error']])): ?>
    <div class="admin-alert admin-alert--<?php echo $alerts[$_GET['error']]['type']; ?>">
        <?php echo htmlspecialchars($alerts[$_GET['error']]['text']); ?>
    </div>
<?php endif; ?>

<section class="admin-grid admin-grid--stats">
    <article class="admin-kpi admin-kpi--yellow">
        <div class="admin-kpi__top">
            <span class="admin-kpi__icon"><i class="fas fa-file-lines"></i></span>
            <span class="admin-kpi__trend">Live</span>
        </div>
        <div>
            <div class="admin-kpi__label">Total Applications</div>
            <div class="admin-kpi__value"><?php echo number_format($totalApplications); ?></div>
        </div>
    </article>

    <article class="admin-kpi admin-kpi--purple">
        <div class="admin-kpi__top">
            <span class="admin-kpi__icon"><i class="fas fa-hourglass-half"></i></span>
            <span class="admin-kpi__trend"><?php echo number_format($pendingApplications); ?></span>
        </div>
        <div>
            <div class="admin-kpi__label">Pending Review</div>
            <div class="admin-kpi__value"><?php echo number_format($pendingApplications); ?></div>
        </div>
    </article>

    <article class="admin-kpi admin-kpi--yellow">
        <div class="admin-kpi__top">
            <span class="admin-kpi__icon"><i class="fas fa-circle-check"></i></span>
            <span class="admin-kpi__trend"><?php echo number_format($approvedApplications); ?></span>
        </div>
        <div>
            <div class="admin-kpi__label">Approved</div>
            <div class="admin-kpi__value"><?php echo number_format($approvedApplications); ?></div>
        </div>
    </article>

    <article class="admin-kpi admin-kpi--purple">
        <div class="admin-kpi__top">
            <span class="admin-kpi__icon"><i class="fas fa-ban"></i></span>
            <span class="admin-kpi__trend"><?php echo number_format($rejectedApplications); ?></span>
        </div>
        <div>
            <div class="admin-kpi__label">Rejected</div>
            <div class="admin-kpi__value"><?php echo number_format($rejectedApplications); ?></div>
        </div>
    </article>
</section>

<section class="admin-card" style="margin-top: 22px;">
    <div class="admin-card__head">
        <div>
            <h3>Application Queue</h3>
            <span class="admin-card__eyebrow">Student applications sorted by most recent submission date</span>
        </div>
    </div>

    <table class="admin-table">
        <thead>
            <tr>
                <th>Student</th>
                <th>Position</th>
                <th>Company</th>
                <th>Applied</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <?php
                    $status = admin_status_class($row['status'] ?? 'pending');
                    $appId = (int) $row['app_id'];
                    ?>
                    <tr>
                        <td>
                            <div class="admin-person">
                                <div class="admin-avatar"><?php echo htmlspecialchars(admin_initials($row['full_name'] ?? '')); ?></div>
                                <div class="admin-person__meta">
                                    <strong><?php echo htmlspecialchars($row['full_name']); ?></strong>
                                    <span><?php echo htmlspecialchars($row['student_code']); ?></span>
                                </div>
                            </div>
                        </td>
                        <td><?php echo htmlspecialchars($row['job_title']); ?></td>
                        <td><?php echo htmlspecialchars($row['company_name']); ?></td>
                        <td><?php echo !empty($row['applied_at']) ? htmlspecialchars(date('d/m/Y', strtotime($row['applied_at']))) : 'N/A'; ?></td>
                        <td>
                            <span class="admin-pill admin-pill--<?php echo htmlspecialchars($status); ?>">
                                <?php echo htmlspecialchars(ucfirst($status)); ?>
                            </span>
                        </td>
                        <td>
                            <div class="admin-actions">
                                <?php if (!empty($row['cv_url'])): ?>
                                    <a href="<?php echo htmlspecialchars($row['cv_url']); ?>" target="_blank" rel="noopener noreferrer" class="admin-action-link">
                                        <i class="fas fa-file-pdf"></i> CV
                                    </a>
                                <?php endif; ?>

                                <?php if ($status === 'pending' || $status === 'reviewed'): ?>
                                    <a href="../actions/admin/approve_application_action.php?id=<?php echo $appId; ?>" class="admin-action-link admin-action-link--approve" onclick="return confirm('Approve this application?');">
                                        <i class="fas fa-check"></i> Approve
                                    </a>
                                    <a href="../actions/admin/reject_application_action.php?id=<?php echo $appId; ?>" class="admin-action-link admin-action-link--danger" onclick="return confirm('Reject this application?');">
                                        <i class="fas fa-xmark"></i> Reject
                                    </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="admin-empty">No applications available.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</section>

<?php
admin_render_end();
$result->free();
$conn->close();
