<?php
require_once '../includes/auth.php';
require_once '../config/db.php';
require_once '../includes/functions.php';
requireRole('admin');

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    redirect('users.php');
}

$flash = getFlash();

require_once '../includes/notifications.php';
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
                <h1>Edit User</h1>
                <p>Update account information and optionally reset the password.</p>
            </div>
            <a href="/Uniworksmohinhhoa/admin/users.php" class="admin-btn">← Back to Users</a>
        </div>

        <?php if ($flash): ?>
            <div class="flash <?= htmlspecialchars($flash['type']) ?>" style="margin-bottom:18px;">
                <?= htmlspecialchars($flash['message']) ?>
            </div>
        <?php endif; ?>

        <div class="admin-card" style="max-width:680px;">
            <form action="/Uniworksmohinhhoa/actions/admin/update_user_action.php" method="POST">
                <input type="hidden" name="id" value="<?= $user['id'] ?>">

                <div style="margin-bottom:16px;">
                    <label style="display:block;font-size:13px;font-weight:700;margin-bottom:6px;">Full Name</label>
                    <input type="text" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" required
                           style="width:100%;height:44px;border:1px solid #e1e3ee;border-radius:12px;padding:0 14px;font-size:14px;outline:none;font-family:inherit;">
                </div>

                <div style="margin-bottom:16px;">
                    <label style="display:block;font-size:13px;font-weight:700;margin-bottom:6px;">Email</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required
                           style="width:100%;height:44px;border:1px solid #e1e3ee;border-radius:12px;padding:0 14px;font-size:14px;outline:none;font-family:inherit;">
                </div>

                <div style="margin-bottom:16px;">
                    <label style="display:block;font-size:13px;font-weight:700;margin-bottom:6px;">Role</label>
                    <select name="role" style="width:100%;height:44px;border:1px solid #e1e3ee;border-radius:12px;padding:0 14px;font-size:14px;outline:none;font-family:inherit;background:#fff;">
                        <option value="student" <?= $user['role']==='student'?'selected':'' ?>>Student</option>
                        <option value="company" <?= $user['role']==='company'?'selected':'' ?>>Company</option>
                        <option value="admin"   <?= $user['role']==='admin'  ?'selected':'' ?>>Admin</option>
                    </select>
                </div>

                <hr style="border:none;border-top:1px solid #eceef6;margin:22px 0;">

                <p style="font-size:13px;color:#7a8093;margin-bottom:14px;">Leave password fields blank to keep the current password unchanged.</p>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:16px;">
                    <div>
                        <label style="display:block;font-size:13px;font-weight:700;margin-bottom:6px;">New Password</label>
                        <input type="password" name="new_password" placeholder="At least 6 characters"
                               style="width:100%;height:44px;border:1px solid #e1e3ee;border-radius:12px;padding:0 14px;font-size:14px;outline:none;font-family:inherit;">
                    </div>
                    <div>
                        <label style="display:block;font-size:13px;font-weight:700;margin-bottom:6px;">Confirm Password</label>
                        <input type="password" name="confirm_password" placeholder="Repeat new password"
                               style="width:100%;height:44px;border:1px solid #e1e3ee;border-radius:12px;padding:0 14px;font-size:14px;outline:none;font-family:inherit;">
                    </div>
                </div>

                <button type="submit" class="admin-btn">Save Changes</button>
            </form>
        </div>
    </main>
</div>

<?php include '../includes/footer.php'; ?>