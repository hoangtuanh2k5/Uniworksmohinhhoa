<?php
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/admin_layout.php';

$sql = "SELECT
            u.full_name,
            s.student_code,
            c.company_name,
            j.title AS job_title,
            ir.start_date,
            ir.end_date
        FROM internship_registrations ir
        JOIN applications a ON ir.application_id = a.id
        JOIN students s ON a.student_id = s.id
        JOIN users u ON s.user_id = u.id
        JOIN jobs j ON a.job_id = j.id
        JOIN companies c ON j.company_id = c.id
        ORDER BY ir.start_date DESC";

$result = $conn->query($sql);
$activeInternships = (int) ($conn->query("SELECT COUNT(*) AS count FROM internship_registrations")->fetch_assoc()['count'] ?? 0);
$endingSoon = (int) ($conn->query("SELECT COUNT(*) AS count FROM internship_registrations WHERE end_date IS NOT NULL AND end_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)")->fetch_assoc()['count'] ?? 0);

admin_render_start(
    'Internships | Placement Hub',
    'monitoring',
    'Internship Monitoring',
    'Track students currently placed and monitor internship timelines'
);
?>

<section class="admin-grid admin-grid--stats">
    <article class="admin-kpi admin-kpi--yellow">
        <div class="admin-kpi__top">
            <span class="admin-kpi__icon"><i class="fas fa-briefcase"></i></span>
            <span class="admin-kpi__trend">Active</span>
        </div>
        <div>
            <div class="admin-kpi__label">Current Internships</div>
            <div class="admin-kpi__value"><?php echo number_format($activeInternships); ?></div>
        </div>
    </article>

    <article class="admin-kpi admin-kpi--purple">
        <div class="admin-kpi__top">
            <span class="admin-kpi__icon"><i class="fas fa-calendar-day"></i></span>
            <span class="admin-kpi__trend">30 days</span>
        </div>
        <div>
            <div class="admin-kpi__label">Ending Soon</div>
            <div class="admin-kpi__value"><?php echo number_format($endingSoon); ?></div>
        </div>
    </article>
</section>

<section class="admin-card" style="margin-top: 22px;">
    <div class="admin-card__head">
        <div>
            <h3>Internship Pipeline</h3>
            <span class="admin-card__eyebrow">Students who are currently in active internship placements</span>
        </div>
    </div>

    <table class="admin-table">
        <thead>
            <tr>
                <th>Student</th>
                <th>Company</th>
                <th>Role</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result instanceof mysqli_result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
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
                        <td><?php echo htmlspecialchars($row['company_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['job_title']); ?></td>
                        <td><?php echo !empty($row['start_date']) ? htmlspecialchars(date('d/m/Y', strtotime($row['start_date']))) : 'N/A'; ?></td>
                        <td><?php echo !empty($row['end_date']) ? htmlspecialchars(date('d/m/Y', strtotime($row['end_date']))) : 'In progress'; ?></td>
                        <td><span class="admin-pill admin-pill--active">Active</span></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="admin-empty">No active internships found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</section>

<?php
admin_render_end();
$conn->close();