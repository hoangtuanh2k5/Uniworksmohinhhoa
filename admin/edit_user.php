<?php
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/admin_layout.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$message = '';
if (isset($_GET['error'])) {
    $message = match ($_GET['error']) {
        'missing_fields' => 'Full name and email are required.',
        'invalid_email' => 'Email format is invalid.',
        'update_failed' => 'Could not update user.',
        default => 'Could not update user.',
    };
}

$stmt = $conn->prepare('SELECT * FROM users WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    die('User not found.');
}

ob_start();
?>
<a href="users.php" class="admin-button--soft"><i class="fas fa-arrow-left"></i> Back</a>
<?php
$actionsHtml = ob_get_clean();

admin_render_start(
    'Edit User | Placement Hub',
    'users',
    'Edit User',
    'Update account details and access role',
    $actionsHtml
);
?>

<<<<<<< Updated upstream
<?php if ($message !== ''): ?>
    <div class="admin-alert admin-alert--error"><?php echo htmlspecialchars($message); ?></div>
<?php endif; ?>

<section class="admin-card" style="max-width: 780px;">
    <form method="POST" action="../actions/admin/update_user_action.php?id=<?php echo $id; ?>">
        <div class="admin-form-grid--2">
            <div>
                <label class="admin-form-label" for="full_name">Full Name</label>
                <input id="full_name" type="text" name="full_name" class="admin-input" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
            </div>
            <div>
                <label class="admin-form-label" for="email">Email</label>
                <input id="email" type="email" name="email" class="admin-input" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>
            <div>
                <label class="admin-form-label" for="phone">Phone</label>
                <input id="phone" type="text" name="phone" class="admin-input" value="<?php echo htmlspecialchars((string) $user['phone']); ?>">
            </div>
            <div>
                <label class="admin-form-label" for="role">Role</label>
                <select id="role" name="role" class="admin-filter">
                    <option value="student" <?php echo $user['role'] === 'student' ? 'selected' : ''; ?>>Student</option>
                    <option value="company" <?php echo $user['role'] === 'company' ? 'selected' : ''; ?>>Company</option>
                    <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                </select>
            </div>
        </div>

        <div class="admin-form-actions">
            <button type="submit" class="admin-button">Save Changes</button>
            <a href="users.php" class="admin-button--soft">Cancel</a>
        </div>
    </form>
</section>

<?php
admin_render_end();
$conn->close();
=======
<div class="admin-shell">
    <aside class="admin-sidebar">
        <div>
            <h3>Admin Panel</h3>
            <nav class="admin-nav">
                <a href="/Uniworksmohinhhoa/admin/dashboard.php">Dashboard</a>
                <a href="/Uniworksmohinhhoa/admin/users.php" class="active">Users</a>
                <a href="/Uniworksmohinhhoa/admin/company_approvals.php">Companies</a>
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
>>>>>>> Stashed changes
