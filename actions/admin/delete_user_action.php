<?php
require_once '../../config/db.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

requireRole('admin');

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    setFlash('error', 'Invalid user ID.');
    redirect('/Uniworksmohinhhoa/admin/users.php');
}

$pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);

setFlash('success', 'User deleted successfully.');
redirect('/Uniworksmohinhhoa/admin/users.php');
