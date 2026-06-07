<?php
require_once '../includes/auth.php';
require_once '../config/db.php';
require_once '../includes/functions.php';
requireRole('admin');

$flash = getFlash();

$stmt = $pdo->query("SELECT * FROM users ORDER BY id DESC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php';
?>

<div class="admin-shell">
    <aside class="admin-sidebar">
        <div>
            <h3>Admin Panel</h3>
            <nav class="admin-nav">
                <a href="/Uniworksmohinhhoa/admin/dashboard.php">Dashboard</a>
                <a href="/Uniworksmohinhhoa/admin/users.php" class="active">Users</a>
                <a href="/Uniworksmohinhhoa/admin/applications.php">Applications</a>
                <a href="/Uniworksmohinhhoa/admin/monitoring.php">Monitoring</a>
                <a href="/Uniworksmohinhhoa/admin/reports.php">Reports</a>
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

        <div class="admin-card">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th style="width: 180px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="4">No users found.</td>
                        </tr>
                    <?php else: ?>
                       <?php foreach ($users as $index => $u): ?>
    <tr class="admin-user-row <?= $index % 2 === 0 ? 'purple-row' : 'yellow-row' ?>">
                            <tr>
                                <td><?= htmlspecialchars($u['full_name']) ?></td>
                                <td><?= htmlspecialchars($u['email']) ?></td>
                                <td>
                                    <span class="admin-role-badge <?= htmlspecialchars($u['role']) ?>">
                                        <?= htmlspecialchars(ucfirst($u['role'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="/Uniworksmohinhhoa/admin/edit_user.php?id=<?= $u['id'] ?>">Edit</a>
                                    |
                                    <a href="/Uniworksmohinhhoa/actions/admin/delete_user_action.php?id=<?= $u['id'] ?>"
                                       onclick="return confirm('Delete user?')">
                                        Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<?php include '../includes/footer.php'; ?>