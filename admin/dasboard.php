<?php
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/admin_layout.php';

/* ===================== LOGIC GIỮ NGUYÊN ===================== */
$totalStudents = (int) ($conn->query("SELECT COUNT(*) AS count FROM students")->fetch_assoc()['count'] ?? 12840); // Hardcode ví dụ nếu DB trống
$partnerCompanies = (int) ($conn->query("SELECT COUNT(*) AS count FROM companies")->fetch_assoc()['count'] ?? 450);
$activeInternships = (int) ($conn->query("SELECT COUNT(*) AS count FROM internship_registrations")->fetch_assoc()['count'] ?? 1205);
$reportsCount = 320;

// Giả lập dữ liệu chart nếu cần (hoặc dùng query của bạn)
$chartLabels = ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN'];
$chartValues = [20, 22, 25, 18, 25, 28]; // Đơn vị k (nghìn)

admin_render_start('Admin Dashboard', 'dashboard', 'System Statistics', 'Real-time overview of platform activity across all sectors');
?>

<style>
    :root {
        --purple-light: #e0e7ff;
        --purple-main: #6366f1;
        --yellow-light: #fef9c3;
        --yellow-main: #eab308;
        --bg-body: #f8fafc;
        --text-main: #1e293b;
    }

    .dashboard-container {
        font-family: 'Inter', sans-serif;
        color: var(--text-main);
        padding: 20px;
    }

    /* KPI Cards */
    .admin-grid--stats {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-bottom: 30px;
    }

    .admin-kpi {
        padding: 24px;
        border-radius: 20px;
        position: relative;
        overflow: hidden;
    }

    .admin-kpi--yellow { background-color: #fef3c7; }
    .admin-kpi--purple { background-color: #e0e7ff; }

    .admin-kpi .label { font-size: 0.9rem; font-weight: 500; margin-bottom: 8px; color: #64748b; }
    .admin-kpi h2 { font-size: 1.8rem; font-weight: 700; margin: 0; }
    .admin-kpi .growth { 
        position: absolute; top: 20px; right: 20px; 
        font-size: 0.85rem; font-weight: 600; color: #10b981;
    }

    /* Layout chính */
    .main-content-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 20px;
    }

    .admin-card {
        background: #fff;
        border-radius: 20px;
        padding: 25px;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
    }

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    /* List Item */
    .admin-list-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 0;
        border-bottom: 1px solid #f1f5f9;
    }

    .avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #ddd;
        object-fit: cover;
    }

    .badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        margin-left: auto;
    }
    .badge-pending { background: #fef3c7; color: #d97706; }
    .badge-accepted { background: #dcfce7; color: #16a34a; }

    /* Table */
    .admin-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
    }

    .admin-table th {
        text-align: left;
        color: #94a3b8;
        font-size: 0.8rem;
        text-transform: uppercase;
        padding: 12px;
        border-bottom: 1px solid #f1f5f9;
    }

    .admin-table td {
        padding: 15px 12px;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.9rem;
    }
    
    .status-dot {
        display: inline-block;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #22c55e;
        margin-right: 5px;
    }
</style>

<div class="dashboard-container">
    <section class="admin-grid--stats">
        <article class="admin-kpi admin-kpi--yellow">
            <span class="growth">+12% ↗</span>
            <div class="label">Total Students</div>
            <h2><?php echo number_format($totalStudents); ?></h2>
        </article>

        <article class="admin-kpi admin-kpi--purple">
            <span class="growth">+5% ↗</span>
            <div class="label">Partner Companies</div>
            <h2><?php echo number_format($partnerCompanies); ?></h2>
        </article>

        <article class="admin-kpi admin-kpi--yellow">
            <span class="growth">+8% ↗</span>
            <div class="label">Active Internships</div>
            <h2><?php echo number_format($activeInternships); ?></h2>
        </article>

        <article class="admin-kpi admin-kpi--purple">
            <span class="growth">+15% ↗</span>
            <div class="label">Reports Generated</div>
            <h2><?php echo number_format($reportsCount); ?></h2>
        </article>
    </section>

    <section class="main-content-grid">
        <div class="admin-card">
            <div class="card-header">
                <h3>Placement Trends</h3>
                <select style="border:none; background:#f1f5f9; padding:5px 10px; border-radius:8px;">
                    <option>Last 6 Months</option>
                </select>
            </div>
            <div style="height: 300px;">
                <canvas id="myChart"></canvas>
            </div>
        </div>

        <div class="admin-card">
            <div class="card-header">
                <h3>Recent Applications</h3>
            </div>
            
            <div class="admin-list-item">
                <img src="https://i.pravatar.cc/150?u=1" class="avatar">
                <div>
                    <strong>James Wilson</strong><br>
                    <small>UI/UX at Google</small>
                </div>
                <span class="badge badge-pending">Pending</span>
            </div>
            <div class="admin-list-item">
                <img src="https://i.pravatar.cc/150?u=2" class="avatar">
                <div>
                    <strong>Sarah Connor</strong><br>
                    <small>DevOps at Amazon</small>
                </div>
                <span class="badge badge-accepted">Accepted</span>
            </div>
            
            <button style="width:100%; margin-top:20px; border:none; background:#f8fafc; padding:10px; border-radius:10px; color:#6366f1; font-weight:600; cursor:pointer;">View All Applications</button>
        </div>
    </section>

    <section class="admin-card" style="margin-top:20px;">
        <div class="card-header">
            <h3>Top Partner Companies</h3>
            <a href="#" style="color:#6366f1; text-decoration:none; font-size:0.9rem;">Manage Partners →</a>
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
                    <td><strong>Meta Platforms</strong></td>
                    <td>45</td>
                    <td>88%</td>
                    <td>Jan 2022</td>
                    <td><span class="status-dot"></span> Active</td>
                </tr>
                <tr>
                    <td><strong>Apple Inc.</strong></td>
                    <td>32</td>
                    <td>92%</td>
                    <td>Mar 2021</td>
                    <td><span class="status-dot"></span> Active</td>
                </tr>
            </tbody>
        </table>
    </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('myChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($chartLabels); ?>,
        datasets: [{
            data: <?php echo json_encode($chartValues); ?>,
            borderColor: '#84cc16',
            backgroundColor: 'rgba(132, 204, 22, 0.1)',
            fill: true,
            tension: 0.4,
            pointRadius: 4,
            pointBackgroundColor: '#fff',
            pointBorderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            y: { 
                beginAtZero: true,
                grid: { color: '#f1f5f9' },
                ticks: { callback: v => v + 'k' }
            },
            x: { grid: { display: false } }
        }
    }
});
</script>

<?php
admin_render_end();
$conn->close();
?>