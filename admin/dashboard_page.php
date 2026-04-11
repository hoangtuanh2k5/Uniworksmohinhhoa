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

admin_render_start(
    'Dashboard | Placement Hub',
    'dashboard',
    'System Statistics',
    'Real-time overview of platform activity across all sectors'
);
?>

<div class="admin-grid admin-grid--stats">
    <div class="admin-card admin-kpi admin-kpi--yellow">
        <div class="admin-kpi__top">
            <div class="admin-kpi__icon"><i class="fas fa-graduation-cap"></i></div>
            <span class="admin-kpi__trend">+12% <i class="fas fa-arrow-up"></i></span>
        </div>
        <span class="admin-kpi__label">Total Students</span>
        <div class="admin-kpi__value"><?= number_format($totalStudents) ?></div>
    </div>
    <div class="admin-card admin-kpi admin-kpi--purple">
        <div class="admin-kpi__top">
            <div class="admin-kpi__icon"><i class="fas fa-building"></i></div>
            <span class="admin-kpi__trend">+5% <i class="fas fa-arrow-up"></i></span>
        </div>
        <span class="admin-kpi__label">Partner Companies</span>
        <div class="admin-kpi__value"><?= number_format($partnerCompanies) ?></div>
    </div>
    <div class="admin-card admin-kpi admin-kpi--yellow">
        <div class="admin-kpi__top">
            <div class="admin-kpi__icon"><i class="fas fa-briefcase"></i></div>
            <span class="admin-kpi__trend">+8% <i class="fas fa-arrow-up"></i></span>
        </div>
        <span class="admin-kpi__label">Active Internships</span>
        <div class="admin-kpi__value"><?= number_format($activeInternships) ?></div>
    </div>
    <div class="admin-card admin-kpi admin-kpi--purple">
        <div class="admin-kpi__top">
            <div class="admin-kpi__icon"><i class="fas fa-chart-line"></i></div>
            <span class="admin-kpi__trend">+15% <i class="fas fa-arrow-up"></i></span>
        </div>
        <span class="admin-kpi__label">Reports Generated</span>
        <div class="admin-kpi__value"><?= number_format($reportsCount) ?></div>
    </div>
</div>

<div class="admin-grid admin-grid--dashboard">
    <div class="admin-card">
        <div class="admin-card__head">
            <div>
                <h3>Placement Trends</h3>
                <span class="admin-card__eyebrow">Monthly application and hiring growth</span>
            </div>
            <select class="admin-select">
                <option>Last 6 Months</option>
            </select>
        </div>

        <div class="admin-chart">
            <canvas id="chartMain"></canvas>
        </div>
    </div>

    <div class="admin-card">
        <div class="admin-card__head">
            <div>
                <h3>Recent Applications</h3>
                <span class="admin-card__eyebrow">Latest student placements</span>
            </div>
        </div>

        <div class="admin-list">
            <div class="admin-list-item">
                <div class="admin-person">
                    <div class="admin-avatar">JW</div>
                    <div class="admin-person__meta">
                        <strong>James Wilson</strong>
                        <span>UI/UX at Google</span>
                    </div>
                </div>
                <span class="admin-pill admin-pill--pending">Pending</span>
            </div>
            <div class="admin-list-item">
                <div class="admin-person">
                    <div class="admin-avatar">SC</div>
                    <div class="admin-person__meta">
                        <strong>Sarah Connor</strong>
                        <span>DevOps at Amazon</span>
                    </div>
                </div>
                <span class="admin-pill admin-pill--approved">Accepted</span>
            </div>
            <div class="admin-list-item">
                <div class="admin-person">
                    <div class="admin-avatar">MC</div>
                    <div class="admin-person__meta">
                        <strong>Michael Chen</strong>
                        <span>Fullstack at Stripe</span>
                    </div>
                </div>
                <span class="admin-pill admin-pill--reviewed">Reviewing</span>
            </div>
        </div>
    </div>
</div>

<div class="admin-grid">
    <div class="admin-card">
        <div class="admin-card__head">
            <div>
                <h3>Top Partner Companies</h3>
                <span class="admin-card__eyebrow">Trusted employers working with students</span>
            </div>
            <a href="#" class="admin-button--soft">Manage Partners</a>
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
                <tr>
                    <td class="admin-table__company"><span class="admin-avatar">M</span><strong>Meta Platforms</strong></td>
                    <td>45</td>
                    <td>88%</td>
                    <td>Jan 2022</td>
                    <td><span class="admin-pill admin-pill--active">Active</span></td>
                </tr>
                <tr>
                    <td class="admin-table__company"><span class="admin-avatar">A</span><strong>Apple Inc.</strong></td>
                    <td>32</td>
                    <td>92%</td>
                    <td>Mar 2021</td>
                    <td><span class="admin-pill admin-pill--active">Active</span></td>
                </tr>
                <tr>
                    <td class="admin-table__company"><span class="admin-avatar">S</span><strong>Stripe</strong></td>
                    <td>18</td>
                    <td>83%</td>
                    <td>Sep 2021</td>
                    <td><span class="admin-pill admin-pill--active">Active</span></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const labels = <?= json_encode($chartLabels) ?>;
const rawData = <?= json_encode($chartValues) ?>;
const chartData = rawData.map(Number);

const ctx = document.getElementById('chartMain').getContext('2d');

// gradient
const gradient = ctx.createLinearGradient(0, 0, 0, 300);
gradient.addColorStop(0, 'rgba(132,204,22,0.38)');
gradient.addColorStop(1, 'rgba(132,204,22,0.04)');

const maxValue = Math.max(...chartData, 10);
const suggestedMax = Math.ceil(maxValue * 1.15);
const stepSize = Math.max(1, Math.round(suggestedMax / 5));

new Chart(ctx, {
    type: 'line',
    data: {
        labels: labels,
        datasets: [{
            data: chartData,
            borderColor: '#84cc16',
            backgroundColor: gradient,
            fill: true,
            tension: 0.4,
            pointRadius: 4,
            pointHoverRadius: 6,
            pointBackgroundColor: '#ffffff',
            pointBorderColor: '#84cc16',
            borderWidth: 3,
            pointBorderWidth: 2,
            borderJoinStyle: 'round'
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
                suggestedMax: suggestedMax,
                ticks: {
                    color: '#94a3b8',
                    stepSize: stepSize,
                    callback: function(value) {
                        return Number(value).toLocaleString();
                    }
                },
                grid: { color: '#f1f5f9' }
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