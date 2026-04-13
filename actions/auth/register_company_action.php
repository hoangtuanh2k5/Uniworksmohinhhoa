<?php
require_once '../../config/db.php';
require_once '../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/Uniworksmohinhhoa/public/register.php?type=company');
}

$full_name = sanitize($_POST['full_name'] ?? '');
$email = sanitize($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

if (!$full_name || !$email || !$password || !$confirm_password) {
    setFlash('error', 'Please fill in all required fields.');
    redirect('/Uniworksmohinhhoa/public/register.php?type=company');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    setFlash('error', 'Invalid email format.');
    redirect('/Uniworksmohinhhoa/public/register.php?type=company');
}

if ($password !== $confirm_password) {
    setFlash('error', 'Passwords do not match.');
    redirect('/Uniworksmohinhhoa/public/register.php?type=company');
}

if (strlen($password) < 6) {
    setFlash('error', 'Password must be at least 6 characters.');
    redirect('/Uniworksmohinhhoa/public/register.php?type=company');
}

try {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);

    if ($stmt->fetch()) {
        setFlash('error', 'Email already exists.');
        redirect('/Uniworksmohinhhoa/public/register.php?type=company');
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("
        INSERT INTO users (email, password, full_name, role)
        VALUES (?, ?, ?, 'company')
    ");
    $stmt->execute([$email, $hashed_password, $full_name]);

    setFlash('success', 'Account created successfully. Please login.');
    redirect('/Uniworksmohinhhoa/public/login.php');

} catch (Exception $e) {
    setFlash('error', 'Registration failed.');
    redirect('/Uniworksmohinhhoa/public/register.php?type=company');
}