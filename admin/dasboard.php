<?php
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/admin_layout.php';

$totalStudents = (int) ($conn->query("SELECT COUNT(*) AS count FROM students")->fetch_assoc()['count'] ?? 0);
$partnerCompanies = (int) ($conn->query("SELECT COUNT(*) AS count FROM companies")->fetch_assoc()['count'] ?? 0);
$activeInternships = (int) ($conn->query("SELECT COUNT(*) AS count FROM internship_registrations")->fetch_assoc()['count'] ?? 0);
$reportsCount = 320;

$recentAppsSql = "SELECT u.full_name, j.title AS job_title, c.company_name, a.status
                  FROM applications a
                  JOIN students s ON a.student_id = s.id
                  JOIN users u ON s.user_id = u.id
                  JOIN jobs j ON a.job_id = j.id
                  JOIN companies c ON j.company_id = c.id
                  ORDER BY a.applied_at DESC
                  LIMIT 3";
$recentApps = $conn->query($recentAppsSql);

$topCompaniesSql = "SELECT
                        c.company_name,
                        COUNT(j.id) AS open_roles
                    FROM companies c
                    LEFT JOIN jobs j ON j.company_id = c.id
                    GROUP BY c.id, c.company_name
                    ORDER BY open_roles DESC, c.company_name ASC
                    LIMIT 5";
$topCompanies = $conn->query($topCompaniesSql);

$monthlyRows = $conn->query("
    SELECT DATE_FORMAT(applied_at, '%Y-%m') AS month_key, COUNT(*) AS total
    FROM applications
    WHERE applied_at >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH)
    GROUP BY month_key
    ORDER BY month_key ASC
");

$monthlyMap = [];
if ($monthlyRows instanceof mysqli_result) {
    while ($row = $monthlyRows->fetch_assoc()) {
        $monthlyMap[$row['month_key']] = (int) $row['total'];
    }
}

$chartLabels = [];
$chartValues = [];
for ($i = 5; $i >= 0; $i--) {
    $timestamp = strtotime("-{$i} month");
    $key = date('Y-m', $timestamp);
    $chartLabels[] = strtoupper(date('M', $timestamp));
    $chartValues[] = $monthlyMap[$key] ?? 0;
}

$maxValue = max(max($chartValues), 1);
$points = [];
$chartWidth = 520;
$chartHeight = 210;
$paddingX = 24;
$paddingY = 18;
$count = count($chartValues);
$stepX = $count > 1 ? ($chartWidth - ($paddingX * 2)) / ($count - 1) : 0;

foreach ($chartValues as $index => $value) {
    $x = $paddingX + ($stepX * $index);
    $ratio = $value / $maxValue;
    $y = $chartHeight - $paddingY - ($ratio * ($chartHeight - ($paddingY * 2)));
    $points[] = round($x, 2) . ',' . round($y, 2);
}

admin_render_start(
    'Admin Dashboard | Placement Hub',
    'dashboard',
    'System Statistics',
    'Real-time overview of platform activity across all sectors'
);
?>

<section class="admin-grid admin-grid--stats">
    <article class="admin-kpi admin-kpi--yellow">
        <div class="admin-kpi__top">
            <span class="admin-kpi__icon"><i class="fas fa-user-graduate"></i></span>
            <span class="admin-kpi__trend">+12%</span>
        </div>
        <div>
            <div class="admin-kpi__label">Total Students</div>
            <div class="admin-kpi__value"><?php echo number_format($totalStudents); ?></div>
        </div>
    </article>

    <article class="admin-kpi admin-kpi--purple">
        <div class="admin-kpi__top">
            <span class="admin-kpi__icon"><i class="fas fa-building"></i></span>
            <span class="admin-kpi__trend">+5%</span>
        </div>
        <div>
            <div class="admin-kpi__label">Partner Companies</div>
            <div class="admin-kpi__value"><?php echo number_format($partnerCompanies); ?></div>
        </div>
    </article>

    <article class="admin-kpi admin-kpi--yellow">
        <div class="admin-kpi__top">
            <span class="admin-kpi__icon"><i class="fas fa-briefcase"></i></span>
            <span class="admin-kpi__trend">+8%</span>
        </div>
        <div>
            <div class="admin-kpi__label">Active Internships</div>
            <div class="admin-kpi__value"><?php echo number_format($activeInternships); ?></div>
        </div>
    </article>

    <article class="admin-kpi admin-kpi--purple">
        <div class="admin-kpi__top">
            <span class="admin-kpi__icon"><i class="fas fa-chart-line"></i></span>
            <span class="admin-kpi__trend">+15%</span>
        </div>
        <div>
            <div class="admin-kpi__label">Reports Generated</div>
            <div class="admin-kpi__value"><?php echo number_format($reportsCount); ?></div>
        </div>
    </article>
</section>

<section class="admin-grid admin-grid--dashboard" style="margin-top: 22px;">
    <article class="admin-card">
        <div class="admin-card__head">
            <div>
                <h3>Placement Trends</h3>
                <span class="admin-card__eyebrow">Monthly application activity across the last 6 months</span>
            </div>
            <select class="admin-select" aria-label="Chart timeframe">
                <option selected>Last 6 Months</option>
            </select>
        </div>

        <div class="admin-chart">
            <svg viewBox="0 0 520 210" role="img" aria-label="Applications trend chart">
                <polyline fill="none" stroke="#9ccb67" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" points="<?php echo htmlspecialchars(implode(' ', $points)); ?>"></polyline>
                <?php foreach ($points as $point): ?>
                    <?php [$cx, $cy] = array_map('floatval', explode(',', $point)); ?>
                    <circle cx="<?php echo $cx; ?>" cy="<?php echo $cy; ?>" r="4.5" fill="#4d8b29"></circle>
                    <circle cx="<?php echo $cx; ?>" cy="<?php echo $cy; ?>" r="2" fill="#ffffff"></circle>
                <?php endforeach; ?>
            </svg>
            <div class="admin-chart__labels">
                <?php foreach ($chartLabels as $label): ?>
                    <span><?php echo htmlspecialchars($label); ?></span>
                <?php endforeach; ?>
            </div>
        </div>
    </article>

    <article class="admin-card">
        <div class="admin-card__head">
            <div>
                <h3>Recent Applications</h3>
                <span class="admin-card__eyebrow">Latest submissions waiting for review</span>
            </div>
        </div>

        <div class="admin-list">
            <?php if ($recentApps instanceof mysqli_result && $recentApps->num_rows > 0): ?>
                <?php while ($app = $recentApps->fetch_assoc()): ?>
                    <?php $statusClass = admin_status_class($app['status'] ?? 'pending'); ?>
                    <div class="admin-list-item">
                        <div class="admin-person">
                            <div class="admin-avatar"><?php echo htmlspecialchars(admin_initials($app['full_name'] ?? '')); ?></div>
                            <div class="admin-person__meta">
                                <strong><?php echo htmlspecialchars($app['full_name']); ?></strong>
                                <span><?php echo htmlspecialchars($app['job_title'] . ' at ' . $app['company_name']); ?></span>
                            </div>
                        </div>
                        <span class="admin-pill admin-pill--<?php echo htmlspecialchars($statusClass); ?>">
                            <?php echo htmlspecialchars(ucfirst($statusClass)); ?>
                        </span>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="admin-empty">No recent applications found.</div>
            <?php endif; ?>
        </div>

        <div style="margin-top: 18px;">
            <a href="applications.php" class="admin-button--soft" style="width: 100%;">View All Applications</a>
        </div>
    </article>
</section>

<section class="admin-card" style="margin-top: 22px;">
    <div class="admin-card__head">
        <div>
            <h3>Top Partner Companies</h3>
            <span class="admin-card__eyebrow">Companies with the most open roles on the platform</span>
        </div>
        <a href="reports.php" class="admin-button--soft">Manage Partners</a>
    </div>

    <table class="admin-table">
        <thead>
            <tr>
                <th>Company</th>
                <th>Open Roles</th>
                <th>Success Rate</th>
                <th>Active Since</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($topCompanies instanceof mysqli_result && $topCompanies->num_rows > 0): ?>
                <?php while ($company = $topCompanies->fetch_assoc()): ?>
                    <?php
                    $openRoles = (int) $company['open_roles'];
                    $successRate = min(98, 70 + ($openRoles * 3));
                    $activeSince = 'Current';
                    ?>
                    <tr>
                        <td>
                            <div class="admin-table__company">
                                <span class="admin-table__icon"><i class="fas fa-building"></i></span>
                                <div>
                                    <strong><?php echo htmlspecialchars($company['company_name']); ?></strong>
                                </div>
                            </div>
                        </td>
                        <td><?php echo number_format($openRoles); ?></td>
                        <td><?php echo $successRate; ?>%</td>
                        <td><?php echo htmlspecialchars($activeSince); ?></td>
                        <td><span class="admin-pill admin-pill--active">Active</span></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="admin-empty">No partner companies found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</section>

<?php
admin_render_end();
$conn->close();
