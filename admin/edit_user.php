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
