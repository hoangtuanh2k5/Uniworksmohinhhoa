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

include '../includes/header.php';
?>

<h1>Edit User</h1>

<?php if ($flash): ?>
    <div class="flash <?= $flash['type'] ?>">
        <?= $flash['message'] ?>
    </div>
<?php endif; ?>

<form action="../actions/admin/update_user_action.php" method="POST">
    <input type="hidden" name="id" value="<?= $user['id'] ?>">

    <input type="text" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" required>
    <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>

    <select name="role">
        <option value="student" <?= $user['role']=='student'?'selected':'' ?>>Student</option>
        <option value="company" <?= $user['role']=='company'?'selected':'' ?>>Company</option>
        <option value="admin" <?= $user['role']=='admin'?'selected':'' ?>>Admin</option>
    </select>

    <button type="submit">Update</button>
</form>