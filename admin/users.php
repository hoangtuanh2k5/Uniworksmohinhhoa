<?php
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/admin_layout.php';

$search = trim($_GET['search'] ?? '');
$roleFilter = trim($_GET['role'] ?? '');
$allowedRoles = ['student', 'company', 'admin'];

$sql = 'SELECT id, full_name, email, role, phone, created_at FROM users WHERE 1=1';
$types = '';
$params = [];

if ($search !== '') {
    $sql .= ' AND (full_name LIKE ? OR email LIKE ?)';
    $like = '%' . $search . '%';
    $types .= 'ss';
    $params[] = $like;
    $params[] = $like;
}

if (in_array($roleFilter, $allowedRoles, true)) {
    $sql .= ' AND role = ?';
    $types .= 's';
    $params[] = $roleFilter;
}

$sql .= ' ORDER BY created_at DESC';
$stmt = $conn->prepare($sql);
if ($types !== '') {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$totalUsers = (int) ($conn->query("SELECT COUNT(*) AS count FROM users")->fetch_assoc()['count'] ?? 0);
$studentUsers = (int) ($conn->query("SELECT COUNT(*) AS count FROM users WHERE role = 'student'")->fetch_assoc()['count'] ?? 0);
$companyUsers = (int) ($conn->query("SELECT COUNT(*) AS count FROM users WHERE role = 'company'")->fetch_assoc()['count'] ?? 0);
$adminUsers = (int) ($conn->query("SELECT COUNT(*) AS count FROM users WHERE role = 'admin'")->fetch_assoc()['count'] ?? 0);

ob_start();
?>
<a href="create_user.php" class="admin-button"><i class="fas fa-plus"></i> Add User</a>
<?php
$actionsHtml = ob_get_clean();

admin_render_start(
    'Users | Placement Hub',
    'users',
    'Students & Users',
    'Manage platform accounts for students, companies and admin staff',
    $actionsHtml
);
?>

<?php if (($_GET['msg'] ?? '') === 'deleted'): ?>
    <div class="admin-alert admin-alert--success">User deleted successfully.</div>
<?php elseif (($_GET['msg'] ?? '') === 'updated'): ?>
    <div class="admin-alert admin-alert--success">User updated successfully.</div>
<?php elseif (($_GET['msg'] ?? '') === 'created'): ?>
    <div class="admin-alert admin-alert--success">User created successfully.</div>
<?php elseif (($_GET['error'] ?? '') === 'invalid_id'): ?>
    <div class="admin-alert admin-alert--error">Invalid user selected.</div>
<?php elseif (($_GET['error'] ?? '') === 'delete_failed'): ?>
    <div class="admin-alert admin-alert--error">Could not delete user.</div>
<?php endif; ?>

<section class="admin-grid admin-grid--stats">
    <article class="admin-kpi admin-kpi--yellow">
        <div class="admin-kpi__top">
            <span class="admin-kpi__icon"><i class="fas fa-users"></i></span>
            <span class="admin-kpi__trend">Total</span>
        </div>
        <div>
            <div class="admin-kpi__label">All Users</div>
            <div class="admin-kpi__value"><?php echo number_format($totalUsers); ?></div>
        </div>
    </article>

    <article class="admin-kpi admin-kpi--purple">
        <div class="admin-kpi__top">
            <span class="admin-kpi__icon"><i class="fas fa-user-graduate"></i></span>
            <span class="admin-kpi__trend"><?php echo number_format($studentUsers); ?></span>
        </div>
        <div>
            <div class="admin-kpi__label">Students</div>
            <div class="admin-kpi__value"><?php echo number_format($studentUsers); ?></div>
        </div>
    </article>

    <article class="admin-kpi admin-kpi--yellow">
        <div class="admin-kpi__top">
            <span class="admin-kpi__icon"><i class="fas fa-building"></i></span>
            <span class="admin-kpi__trend"><?php echo number_format($companyUsers); ?></span>
        </div>
        <div>
            <div class="admin-kpi__label">Companies</div>
            <div class="admin-kpi__value"><?php echo number_format($companyUsers); ?></div>
        </div>
    </article>

    <article class="admin-kpi admin-kpi--purple">
        <div class="admin-kpi__top">
            <span class="admin-kpi__icon"><i class="fas fa-user-shield"></i></span>
            <span class="admin-kpi__trend"><?php echo number_format($adminUsers); ?></span>
        </div>
        <div>
            <div class="admin-kpi__label">Admins</div>
            <div class="admin-kpi__value"><?php echo number_format($adminUsers); ?></div>
        </div>
    </article>
</section>

<section class="admin-card" style="margin-top: 22px;">
    <div class="admin-card__head">
        <div>
            <h3>User Directory</h3>
            <span class="admin-card__eyebrow">Filter accounts by role or search by name and email</span>
        </div>
    </div>

    <form method="GET" class="admin-toolbar">
        <input type="text" name="search" class="admin-input" placeholder="Search name or email..." value="<?php echo htmlspecialchars($search); ?>">
        <select name="role" class="admin-filter" onchange="this.form.submit()">
            <option value="">All roles</option>
            <option value="student" <?php echo $roleFilter === 'student' ? 'selected' : ''; ?>>Student</option>
            <option value="company" <?php echo $roleFilter === 'company' ? 'selected' : ''; ?>>Company</option>
            <option value="admin" <?php echo $roleFilter === 'admin' ? 'selected' : ''; ?>>Admin</option>
        </select>
        <button type="submit" class="admin-button--soft">Apply</button>
    </form>

    <table class="admin-table">
        <thead>
            <tr>
                <th>User</th>
                <th>Email</th>
                <th>Role</th>
                <th>Phone</th>
                <th>Joined</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result instanceof mysqli_result && $result->num_rows > 0): ?>
                <?php while ($user = $result->fetch_assoc()): ?>
                    <?php $roleClass = $user['role'] === 'admin' ? 'approved' : ($user['role'] === 'company' ? 'reviewed' : 'pending'); ?>
                    <tr>
                        <td>
                            <div class="admin-person">
                                <div class="admin-avatar"><?php echo htmlspecialchars(admin_initials($user['full_name'] ?? '')); ?></div>
                                <div class="admin-person__meta">
                                    <strong><?php echo htmlspecialchars($user['full_name']); ?></strong>
                                    <span>ID #<?php echo (int) $user['id']; ?></span>
                                </div>
                            </div>
                        </td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td>
                            <span class="admin-pill admin-pill--<?php echo htmlspecialchars($roleClass); ?>">
                                <?php echo htmlspecialchars(ucfirst($user['role'])); ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($user['phone'] ?: 'N/A'); ?></td>
                        <td><?php echo !empty($user['created_at']) ? htmlspecialchars(date('d/m/Y', strtotime($user['created_at']))) : 'N/A'; ?></td>
                        <td>
                            <div class="admin-actions">
                                <a href="edit_user.php?id=<?php echo (int) $user['id']; ?>" class="admin-action-link">
                                    <i class="fas fa-pen"></i> Edit
                                </a>
                                <a href="../actions/admin/delete_user_action.php?id=<?php echo (int) $user['id']; ?>" class="admin-action-link admin-action-link--danger" onclick="return confirm('Delete this user? This may remove related records.');">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="admin-empty">No users found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</section>

<?php
admin_render_end();
$stmt->close();
$conn->close();