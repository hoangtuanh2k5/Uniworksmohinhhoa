<?php
require_once '../../config/db.php';
require_once '../../includes/functions.php';

requireRole('admin');

$id = (int)$_POST['id'];
$name = sanitize($_POST['full_name']);
$email = sanitize($_POST['email']);
$role = $_POST['role'];

$stmt = $pdo->prepare("
    UPDATE users
    SET full_name=?, email=?, role=?
    WHERE id=?
");

$stmt->execute([$name, $email, $role, $id]);

setFlash('success', 'User updated');
redirect('/Uniworksmohinhhoa/admin/users.php');