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

<?php if (isset($_GET['msg'], $alerts[$_GET['msg']])): ?>
    <div class="admin-alert admin-alert--<?php echo $alerts[$_GET['msg']]['type']; ?>">
        <?php echo htmlspecialchars($alerts[$_GET['msg']]['text']); ?>
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