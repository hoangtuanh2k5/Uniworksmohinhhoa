<?php
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/admin_layout.php';

/* ===================== KPI ===================== */
$totalStudents = (int) ($conn->query("SELECT COUNT(*) AS count FROM students")->fetch_assoc()['count'] ?? 0);
$partnerCompanies = (int) ($conn->query("SELECT COUNT(*) AS count FROM companies")->fetch_assoc()['count'] ?? 0);
$activeInternships = (int) ($conn->query("SELECT COUNT(*) AS count FROM internship_registrations")->fetch_assoc()['count'] ?? 0);
$reportsCount = 320;

/* ===================== RECENT ===================== */
$recentAppsSql = "SELECT u.full_name, j.title AS job_title, c.company_name, a.status
FROM applications a
JOIN students s ON a.student_id = s.id
JOIN users u ON s.user_id = u.id
JOIN jobs j ON a.job_id = j.id
JOIN companies c ON j.company_id = c.id
ORDER BY a.applied_at DESC
LIMIT 3";
$recentApps = $conn->query($recentAppsSql);

/* ===================== TOP COMPANY ===================== */
$topCompaniesSql = "SELECT c.company_name, COUNT(j.id) AS open_roles
FROM companies c
LEFT JOIN jobs j ON j.company_id = c.id
GROUP BY c.id, c.company_name
ORDER BY open_roles DESC
LIMIT 5";
$topCompanies = $conn->query($topCompaniesSql);

/* ===================== CHART DATA ===================== */
$monthlyRows = $conn->query("
    SELECT DATE_FORMAT(applied_at, '%Y-%m') AS month_key, COUNT(*) AS total
    FROM applications
    WHERE applied_at >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH)
    GROUP BY month_key
    ORDER BY month_key ASC
");

$monthlyMap = [];
while ($row = $monthlyRows->fetch_assoc()) {
    $monthlyMap[$row['month_key']] = (int)$row['total'];
}

$chartLabels = [];
$chartValues = [];

for ($i = 5; $i >= 0; $i--) {
    $timestamp = strtotime("-{$i} month");
    $key = date('Y-m', $timestamp);

    $chartLabels[] = strtoupper(date('M', $timestamp));
    $chartValues[] = $monthlyMap[$key] ?? 0;
}

/* ===================== RENDER ===================== */
admin_render_start(
    'Admin Dashboard',
    'dashboard',
    'System Statistics',
    'Real-time overview'
);
?>

<!-- KPI -->
<section class="admin-grid admin-grid--stats">
    <article class="admin-kpi admin-kpi--yellow">
        <div>Total Students</div>
        <h2><?php echo number_format($totalStudents); ?></h2>
    </article>

    <article class="admin-kpi admin-kpi--purple">
        <div>Companies</div>
        <h2><?php echo number_format($partnerCompanies); ?></h2>
    </article>

    <article class="admin-kpi admin-kpi--yellow">
        <div>Internships</div>
        <h2><?php echo number_format($activeInternships); ?></h2>
    </article>

    <article class="admin-kpi admin-kpi--purple">
        <div>Reports</div>
        <h2><?php echo number_format($reportsCount); ?></h2>
    </article>
</section>

<!-- CHART + RECENT -->
<section class="admin-grid" style="margin-top:20px;grid-template-columns:2fr 1fr;gap:20px;">

    <!-- CHART -->
    <div class="admin-card">
        <h3>Placement Trends</h3>
        <div class="admin-chart">
            <canvas id="myChart"></canvas>
        </div>
    </div>

    <!-- RECENT -->
    <div class="admin-card">
        <h3>Recent Applications</h3>

        <?php while ($app = $recentApps->fetch_assoc()): ?>
            <div class="admin-list-item">
                <strong><?php echo $app['full_name']; ?></strong><br>
                <small><?php echo $app['job_title'] . ' - ' . $app['company_name']; ?></small>
            </div>
        <?php endwhile; ?>
    </div>
</section>

<!-- TABLE -->
<section class="admin-card" style="margin-top:20px;">
    <h3>Top Companies</h3>

    <table class="admin-table">
        <tr>
            <th>Name</th>
            <th>Roles</th>
        </tr>

        <?php while ($c = $topCompanies->fetch_assoc()): ?>
            <tr>
                <td><?php echo $c['company_name']; ?></td>
                <td><?php echo $c['open_roles']; ?></td>
            </tr>
        <?php endwhile; ?>
    </table>
</section>

<!-- CHART JS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
const labels = <?php echo json_encode($chartLabels); ?>;
const dataValues = <?php echo json_encode($chartValues); ?>;

new Chart(document.getElementById('myChart'), {
    type: 'line',
    data: {
        labels: labels,
        datasets: [{
            data: dataValues,
            borderColor: '#84cc16',
            backgroundColor: 'rgba(132,204,22,0.15)',
            tension: 0.4,
            fill: true,
            pointRadius: 5,
            pointBackgroundColor: '#fff',
            pointBorderColor: '#84cc16'
        }]
    },
    options: {
        plugins: {
            legend: { display: false }
        },
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            x: { grid: { display: false } },
            y: { beginAtZero: true }
        }
    }
});
</script>

<style>
.admin-chart {
    height: 260px;
}
.admin-card {
    padding:20px;
    background:#fff;
    border-radius:12px;
}
.admin-list-item {
    padding:10px 0;
    border-bottom:1px solid #eee;
}
.admin-table {
    width:100%;
}
.admin-table td, .admin-table th {
    padding:10px;
}
</style>

<?php
admin_render_end();
$conn->close();
?>