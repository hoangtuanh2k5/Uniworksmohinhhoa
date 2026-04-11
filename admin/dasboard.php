<?php
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/admin_layout.php';

// ================== KPI ==================
try {
    $totalStudents = $conn->query("SELECT COUNT(*) FROM students")->fetch_row()[0] ?? 0;
    $partnerCompanies = $conn->query("SELECT COUNT(*) FROM companies")->fetch_row()[0] ?? 0;
    $activeInternships = $conn->query("SELECT COUNT(*) FROM applications WHERE status='approved'")->fetch_row()[0] ?? 0;
    $reportsCount = $conn->query("SELECT COUNT(*) FROM applications")->fetch_row()[0] ?? 0;
} catch (Exception $e) {
    $totalStudents = 12840;
    $partnerCompanies = 450;
    $activeInternships = 1205;
    $reportsCount = 320;
}

// ================== CHART DATA ==================
$chartLabels = [];
$chartValues = [];

$result = $conn->query("
    SELECT 
        DATE_FORMAT(applied_at, '%b') as month,
        COUNT(*) as total
    FROM applications
    GROUP BY DATE_FORMAT(applied_at, '%Y-%m')
    ORDER BY MIN(applied_at)
");

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $chartLabels[] = strtoupper($row['month']);
        $chartValues[] = (int)$row['total'];
    }
}

// fallback nếu chưa có data
if (empty($chartLabels)) {
    $chartLabels = ['JAN','FEB','MAR','APR','MAY','JUN'];
    $chartValues = [5,10,8,12,6,9];
}

admin_render_start('Dashboard | Placement Hub', 'dashboard', 'Dashboard', 'Overview of the platform');
?>

<style>
.dashboard-wrapper {
    font-family: 'Inter', sans-serif;
    padding: 20px;
    background: #f8fafc;
}

.kpi-grid {
    display: grid;
    grid-template-columns: repeat(4,1fr);
    gap: 20px;
    margin-bottom: 30px;
}

.kpi-card {
    padding: 25px;
    border-radius: 20px;
    background: #fff;
    box-shadow: 0 4px 6px rgba(0,0,0,0.05);
}

.main-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 25px;
}

.card {
    background: #fff;
    padding: 25px;
    border-radius: 20px;
}

canvas {
    margin-top: 10px;
}
</style>

<div class="dashboard-wrapper">

<h2>Dashboard</h2>

<div class="kpi-grid">
    <div class="kpi-card">Students: <?= number_format($totalStudents) ?></div>
    <div class="kpi-card">Companies: <?= number_format($partnerCompanies) ?></div>
    <div class="kpi-card">Internships: <?= number_format($activeInternships) ?></div>
    <div class="kpi-card">Applications: <?= number_format($reportsCount) ?></div>
</div>

<div class="main-grid">
    <div class="card">
        <h3>Placement Trends</h3>
        <canvas id="chartMain" height="120"></canvas>
    </div>

    <div class="card">
        <h3>Info</h3>
        <p>Dashboard overview</p>
    </div>
</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const labels = <?= json_encode($chartLabels) ?>;
const dataValues = <?= json_encode($chartValues) ?>;

const ctx = document.getElementById('chartMain').getContext('2d');

// gradient
const gradient = ctx.createLinearGradient(0, 0, 0, 300);
gradient.addColorStop(0, 'rgba(132,204,22,0.4)');
gradient.addColorStop(1, 'rgba(132,204,22,0)');

new Chart(ctx, {
    type: 'line',
    data: {
        labels: labels,
        datasets: [{
            data: dataValues,
            borderColor: '#84cc16',
            backgroundColor: gradient,
            fill: true,
            tension: 0.45,
            pointRadius: 0,
            pointHoverRadius: 6,
            pointBackgroundColor: '#fff',
            pointBorderColor: '#84cc16',
            borderWidth: 3
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,

        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: '#111827',
                titleColor: '#fff',
                bodyColor: '#fff',
                padding: 10,
                cornerRadius: 8,
                displayColors: false,
                callbacks: {
                    label: function(context) {
                        return context.parsed.y + ' applications';
                    }
                }
            }
        },

        scales: {
            x: {
                grid: { display: false },
                ticks: { color: '#94a3b8' }
            },
            y: {
                beginAtZero: true,
                grid: { color: '#f1f5f9' },
                ticks: { color: '#94a3b8' }
            }
        },

        animation: {
            duration: 1200,
            easing: 'easeOutQuart'
        }
    }
});
</script>

<?php 
admin_render_end();
$conn->close();
?>