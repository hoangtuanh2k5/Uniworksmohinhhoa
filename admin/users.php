<?php
require_once '../includes/auth.php';
require_once '../config/db.php';
require_once '../includes/functions.php';
requireRole('admin');

$flash = getFlash();
$tab   = $_GET['tab'] ?? 'student'; // 'student' | 'company'

$students = $pdo->query("
    SELECT u.id, u.full_name, u.email, u.phone, u.role,
           s.id AS student_id, s.student_code, s.class_name, s.gpa
    FROM users u
    LEFT JOIN students s ON s.user_id = u.id
    WHERE u.role = 'student'
    ORDER BY u.id DESC
")->fetchAll(PDO::FETCH_ASSOC);

$companies = $pdo->query("
    SELECT u.id, u.full_name, u.email, u.phone, u.role,
           c.id AS company_id, c.company_name, c.tax_code, c.industry_type, c.address
    FROM users u
    LEFT JOIN companies c ON c.user_id = u.id
    WHERE u.role = 'company'
    ORDER BY u.id DESC
")->fetchAll(PDO::FETCH_ASSOC);

require_once '../includes/notifications.php';
include '../includes/header.php';
?>

<style>
.admin-tabs {
    display: flex;
    gap: 0;
    margin-bottom: 22px;
    border-bottom: 2px solid #eceef6;
}
.admin-tabs a {
    padding: 12px 28px;
    font-size: 15px;
    font-weight: 700;
    color: #7a8093;
    text-decoration: none;
    border-bottom: 3px solid transparent;
    margin-bottom: -2px;
    transition: .15s;
}
.admin-tabs a.active {
    color: #17172b;
    border-bottom-color: #cfc6f6;
}
.admin-tabs a:hover { color: #17172b; }

.admin-role-badge {
    display: inline-flex;
    align-items: center;
    padding: 4px 10px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 700;
}
.admin-role-badge.student  { background: #e7dcff; color: #5b43a7; }
.admin-role-badge.company  { background: #fef0c9; color: #92600a; }
.admin-role-badge.admin    { background: #d8f2df; color: #187a3d; }

.admin-action-links { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
.admin-action-links a { font-size: 13px; font-weight: 700; }
.admin-view-link   { color: #7f4df3; }
.admin-edit-link   { color: #265ad9; }
.admin-delete-link { color: #b42323; }
</style>

<div class="admin-shell">
    <aside class="admin-sidebar">
        <div>
            <h3>Admin Panel</h3>
            <nav class="admin-nav">
                <a href="/Uniworksmohinhhoa/admin/dashboard.php">Dashboard</a>
                <a href="/Uniworksmohinhhoa/admin/users.php" class="active">Users</a>
                <a href="/Uniworksmohinhhoa/admin/applications.php">Applications</a>
                <a href="/Uniworksmohinhhoa/admin/monitoring.php">Monitoring</a>
                <a href="/Uniworksmohinhhoa/admin/reports.php">Reports<?php if(!empty($notif['reports']) && $notif['reports']>0): ?><span class="notif-badge"><?= $notif['reports'] ?></span><?php endif; ?></a>
            </nav>
        </div>
        <div class="admin-sidebar__footer">
            <a href="/Uniworksmohinhhoa/public/logout.php">Logout</a>
        </div>
    </aside>

    <main class="admin-main">
        <div class="admin-topbar">
            <div>
                <h1>Manage Users</h1>
                <p>Create, edit, and organize all system accounts.</p>
            </div>
            <a href="/Uniworksmohinhhoa/admin/create_user.php" class="admin-btn">+ Create User</a>
        </div>

        <?php if ($flash): ?>
            <div class="flash <?= htmlspecialchars($flash['type']) ?>" style="margin-bottom:18px;">
                <?= htmlspecialchars($flash['message']) ?>
            </div>
        <?php endif; ?>

        <!-- Tabs -->
        <div class="admin-tabs">
            <a href="?tab=student" class="<?= $tab === 'student' ? 'active' : '' ?>">
                Students (<?= count($students) ?>)
            </a>
            <a href="?tab=company" class="<?= $tab === 'company' ? 'active' : '' ?>">
                Companies (<?= count($companies) ?>)
            </a>
        </div>

        <?php if ($tab === 'student'): ?>
        <!-- ===== STUDENT TABLE ===== -->
        <div class="admin-card">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Student Code</th>
                        <th>Class</th>
                        <th>GPA</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($students)): ?>
                        <tr><td colspan="6">No students found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($students as $u): ?>
                            <tr>
                                <td><?= htmlspecialchars($u['full_name']) ?></td>
                                <td><?= htmlspecialchars($u['email']) ?></td>
                                <td><?= htmlspecialchars($u['student_code'] ?? '—') ?></td>
                                <td><?= htmlspecialchars($u['class_name'] ?? '—') ?></td>
                                <td><?= $u['gpa'] ? number_format((float)$u['gpa'], 2) : '—' ?></td>
                                <td>
                                    <div class="admin-action-links">
                                        <?php if (!empty($u['student_id'])): ?>
                                            <a href="/Uniworksmohinhhoa/admin/student_detail.php?id=<?= $u['student_id'] ?>"
                                               class="admin-view-link">View</a> |
                                        <?php endif; ?>
                                        <a href="/Uniworksmohinhhoa/admin/edit_user.php?id=<?= $u['id'] ?>"
                                           class="admin-edit-link">Edit</a> |
                                        <a href="/Uniworksmohinhhoa/actions/admin/delete_user_action.php?id=<?= $u['id'] ?>"
                                           class="admin-delete-link"
                                           onclick="return confirm('Delete this student?')">Delete</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php else: ?>
        <!-- ===== COMPANY TABLE ===== -->
        <div class="admin-card">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Contact Name</th>
                        <th>Email</th>
                        <th>Company Name</th>
                        <th>Tax Code</th>
                        <th>Industry</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($companies)): ?>
                        <tr><td colspan="6">No companies found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($companies as $u): ?>
                            <tr>
                                <td><?= htmlspecialchars($u['full_name']) ?></td>
                                <td><?= htmlspecialchars($u['email']) ?></td>
                                <td><?= htmlspecialchars($u['company_name'] ?? '—') ?></td>
                                <td><?= htmlspecialchars($u['tax_code'] ?? '—') ?></td>
                                <td><?= htmlspecialchars($u['industry_type'] ?? '—') ?></td>
                                <td>
                                    <div class="admin-action-links">
                                        <?php if (!empty($u['company_id'])): ?>
                                            <a href="/Uniworksmohinhhoa/admin/company_detail.php?id=<?= $u['company_id'] ?>"
                                               class="admin-view-link">View</a> |
                                        <?php endif; ?>
                                        <a href="/Uniworksmohinhhoa/admin/edit_user.php?id=<?= $u['id'] ?>"
                                           class="admin-edit-link">Edit</a> |
                                        <a href="/Uniworksmohinhhoa/actions/admin/delete_user_action.php?id=<?= $u['id'] ?>"
                                           class="admin-delete-link"
                                           onclick="return confirm('Delete this company?')">Delete</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </main>
</div>

<?php include '../includes/footer.php'; ?>
