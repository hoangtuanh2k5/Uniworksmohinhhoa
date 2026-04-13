<?php
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/admin_layout.php';

$majorStats = $conn->query("
    SELECT m.name, COUNT(s.id) AS total
    FROM majors m
    LEFT JOIN students s ON m.id = s.major_id
    GROUP BY m.id, m.name
    ORDER BY total DESC, m.name ASC
");

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

    <article class="admin-card">
        <div class="admin-card__head">
            <div>
                <h3>Application Status Summary</h3>
                <span class="admin-card__eyebrow">Current counts by review state</span>
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