<?php
require_once '../../config/db.php';
require_once '../../includes/functions.php';

requireRole('admin');

$id = (int)$_GET['id'];

$pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);

setFlash('success', 'User deleted');
redirect('/Uniworksmohinhhoa/admin/users.php');