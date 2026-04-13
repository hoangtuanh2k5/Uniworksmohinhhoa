<?php
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/admin_layout.php';

$message = '';
$messageType = 'success';

if (isset($_GET['error'])) {
    $messageType = 'error';
    $message = match ($_GET['error']) {
        'missing_fields' => 'Full name, email, and password are required.',
        'invalid_email' => 'Email format is invalid.',
        'create_failed' => 'Could not create user. Please check duplicate email or schema fields.',
        default => 'Could not create user.',
    };
}

ob_start();
?>
<a href="users.php" class="admin-button--soft"><i class="fas fa-users"></i> View Users</a>
<?php
$actionsHtml = ob_get_clean();

admin_render_start(
    'Create User | Placement Hub',
    'create_user',
    'Admin Settings / Create User',
    'Add a new account using the same visual style as the updated admin dashboard',
    $actionsHtml
);
?>

<?php if ($message !== ''): ?>
    <div class="admin-alert admin-alert--<?php echo $messageType === 'success' ? 'success' : 'error'; ?>">
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<section class="admin-card" style="max-width: 860px;">
    <form method="POST" action="../actions/admin/creat_user_action.php">
        <div class="admin-form-grid--2">
            <div>
                <label class="admin-form-label" for="full_name">Full Name</label>
                <input id="full_name" type="text" name="full_name" class="admin-input" required>
            </div>
            <div>
                <label class="admin-form-label" for="email">Email</label>
                <input id="email" type="email" name="email" class="admin-input" required>
            </div>
            <div>
                <label class="admin-form-label" for="phone">Phone</label>
                <input id="phone" type="text" name="phone" class="admin-input">
            </div>
            <div>
                <label class="admin-form-label" for="role">Role</label>
                <select id="role" name="role" class="admin-filter">
                    <option value="student">Student</option>
                    <option value="company">Company</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <div style="grid-column: 1 / -1;">
                <label class="admin-form-label" for="password">Password</label>
                <input id="password" type="password" name="password" class="admin-input" required>
            </div>
        </div>

        <div class="admin-form-actions">
            <button type="submit" class="admin-button">Create User</button>
            <a href="users.php" class="admin-button--soft">Cancel</a>
        </div>
    </form>
</section>

<?php
admin_render_end();
$conn->close();