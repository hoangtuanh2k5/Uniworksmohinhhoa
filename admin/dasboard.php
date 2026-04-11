<?php
// 1. Kết nối và khởi tạo
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/admin_layout.php';

// 2. Lấy dữ liệu thực tế (Dùng try-catch để tránh lỗi dừng trang nếu bảng chưa có)
try {
    $totalStudents = $conn->query("SELECT COUNT(*) FROM students")->fetch_row()[0] ?? 12840;
    $partnerCompanies = $conn->query("SELECT COUNT(*) FROM companies")->fetch_row()[0] ?? 450;
    $activeInternships = $conn->query("SELECT COUNT(*) FROM internship_registrations")->fetch_row()[0] ?? 1205;
    $reportsCount = 320;
} catch (Exception $e) {
    // Fallback dữ liệu mẫu nếu DB lỗi để bạn vẫn thấy được giao diện
    $totalStudents = 12840; $partnerCompanies = 450; $activeInternships = 1205; $reportsCount = 320;
}

// Dữ liệu biểu đồ
$chartLabels = ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN'];
$chartValues = [20, 23, 27, 20, 26, 30];

admin_render_start('Dashboard', 'dashboard');
?>

<style>
    /* Google Font */
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

    .dashboard-wrapper {
        font-family: 'Inter', sans-serif;
        color: #1e293b;
        padding: 20px;
        background-color: #f8fafc;
    }

    /* Header & Search */
    .dash-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
    }
    .search-bar {
        background: #fff;
        border: 1px solid #e2e8f0;
        padding: 10px 20px;
        border-radius: 50px;
        width: 350px;
        display: flex;
        align-items: center;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
    }
    .search-bar input { border: none; outline: none; margin-left: 10px; width: 100%; font-size: 0.9rem; }

    /* KPI Section */
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    .kpi-card {
        padding: 25px;
        border-radius: 20px;
        position: relative;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
    }
    .kpi-card .label { font-size: 0.85rem; color: #64748b; font-weight: 500; margin-bottom: 5px; }
    .kpi-card .value { font-size: 1.75rem; font-weight: 800; margin: 0; }
    .kpi-card .trend { position: absolute; top: 20px; right: 20px; color: #10b981; font-size: 0.8rem; font-weight: 700; }
    
    .bg-yellow { background-color: #fef9c3; }
    .bg-purple { background-color: #eef2ff; }

    /* Main Grid */
    .main-grid {
        display: grid;
        grid-template-columns: 2fr 1.2fr;
        gap: 25px;
        margin-bottom: 25px;
    }
    .card { background: #fff; border-radius: 24px; padding: 25px; border: 1px solid #f1f5f9; }
    .card-h { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; font-weight: 700; }

    /* Recent Apps List */
    .app-item {
        display: flex;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px solid #f8fafc;
    }
    .app-img { width: 42px; height: 42px; border-radius: 50%; margin-right: 12px; }
    .badge {
        margin-left: auto;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    .badge-p { background: #fef3c7; color: #92400e; }
    .badge-a { background: #dcfce7; color: #166534; }
    .badge-r { background: #e0e7ff; color: #3730a3; }

    /* Company Table */
    .comp-table { width: 100%; border-collapse: collapse; }
    .comp-table th { text-align: left; padding: 12px; color: #94a3b8; font-size: 0.75rem; text-transform: uppercase; border-bottom: 1px solid #f1f5f9; }
    .comp-table td { padding: 15px 12px; border-bottom: 1px solid #f1f5f9; font-size: 0.9rem; }
    .comp-info { display: flex; align-items: center; gap: 10px; }
    .comp-logo { width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-weight: 800; }

    /* Fix Responsive */
    @media (max-width: 1024px) {
        .main-grid { grid-template-columns: 1fr; }
    }
</style>

<div class="dashboard-wrapper">
    <div class="dash-header">
        <div class="search-bar">
            <span>🔍</span>
            <input type="text" placeholder="Search data, students, or companies...">
        </div>
        <div style="display:flex; gap:15px;">
            <button style="border:none; background:none; cursor:pointer; font-size:1.2rem;">🔔</button>
            <div style="width:35px; height:35px; background:#ddd; border-radius:50%;"></div>
        </div>
    </div>

    <div style="margin-bottom: 25px;">
        <h1 style="font-size: 1.8rem; font-weight: 800; margin:0;">System Statistics</h1>
        <p style="color:#64748b; margin: 5px 0 0 0;">Real-time overview of platform activity</p>
    </div>

    <div class="kpi-grid">
        <div class="kpi-card bg-yellow">
            <span class="trend">+12% ↗</span>
            <div class="label">Total Students</div>
            <p class="value"><?= number_format($totalStudents) ?></p>
        </div>
        <div class="kpi-card bg-purple">
            <span class="trend">+5% ↗</span>
            <div class="label">Partner Companies</div>
            <p class="value"><?= number_format($partnerCompanies) ?></p>
        </div>
        <div class="kpi-card bg-yellow">
            <span class="trend">+8% ↗</span>
            <div class="label">Active Internships</div>
            <p class="value"><?= number_format($activeInternships) ?></p>
        </div>
        <div class="kpi-card bg-purple">
            <span class="trend">+15% ↗</span>
            <div class="label">Reports Generated</div>
            <p class="value"><?= $reportsCount ?></p>
        </div>
    </div>

    <div class="main-grid">
        <div class="card">
            <div class="card-h">
                <span>Placement Trends</span>
                <select style="border:none; background:#f1f5f9; padding:5px; border-radius:5px; font-size:0.8rem;">
                    <option>Last 6 Months</option>
                </select>
            </div>
            <div style="height: 300px;">
                <canvas id="chartMain"></canvas>
            </div>
        </div>

        <div class="card">
            <div class="card-h">Recent Applications</div>
            <div class="app-item">
                <img src="https://i.pravatar.cc/100?u=1" class="app-img">
                <div><strong>James Wilson</strong><br><small style="color:#64748b;">UI/UX at Google</small></div>
                <span class="badge badge-p">Pending</span>
            </div>
            <div class="app-item">
                <img src="https://i.pravatar.cc/100?u=2" class="app-img">
                <div><strong>Sarah Connor</strong><br><small style="color:#64748b;">DevOps at Amazon</small></div>
                <span class="badge badge-a">Accepted</span>
            </div>
            <div class="app-item">
                <img src="https://i.pravatar.cc/100?u=3" class="app-img">
                <div><strong>Michael Chen</strong><br><small style="color:#64748b;">Fullstack at Stripe</small></div>
                <span class="badge badge-r">Reviewing</span>
            </div>
            <button style="width:100%; margin-top:20px; border:none; background:#f8fafc; padding:12px; border-radius:12px; color:#6366f1; font-weight:700; cursor:pointer;">View All</button>
        </div>
    </div>

    <div class="card">
        <div class="card-h">
            <span>Top Partner Companies</span>
            <a href="#" style="color:#6366f1; text-decoration:none; font-size:0.8rem;">Manage Partners →</a>
        </div>
        <table class="comp-table">
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
                    <td>
                        <div class="comp-info">
                            <div class="comp-logo" style="background:#f3f0ff; color:#8b5cf6;">M</div>
                            <strong>Meta Platforms</strong>
                        </div>
                    </td>
                    <td>45</td>
                    <td>88%</td>
                    <td>Jan 2022</td>
                    <td><span style="color:#10b981; font-weight:600;">● Active</span></td>
                </tr>
                <tr>
                    <td>
                        <div class="comp-info">
                            <div class="comp-logo" style="background:#fef2f2; color:#ef4444;">A</div>
                            <strong>Apple Inc.</strong>
                        </div>
                    </td>
                    <td>32</td>
                    <td>92%</td>
                    <td>Mar 2021</td>
                    <td><span style="color:#10b981; font-weight:600;">● Active</span></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('chartMain').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode($chartLabels) ?>,
            datasets: [{
                data: <?= json_encode($chartValues) ?>,
                borderColor: '#84cc16',
                backgroundColor: 'rgba(132, 204, 22, 0.1)',
                fill: true, tension: 0.4, pointRadius: 5, pointBackgroundColor: '#fff', pointBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { callback: v => v + 'k' } },
                x: { grid: { display: false } }
            }
        }
    });
</script>

<?php 
admin_render_end(); 
$conn->close();
?>