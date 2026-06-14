<?php
require_once '../../config/db.php';
require_once '../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/Uniworksmohinhhoa/public/register.php?type=company');
}

$full_name        = sanitize($_POST['full_name']        ?? '');
$email            = sanitize($_POST['email']            ?? '');
$phone            = sanitize($_POST['phone']            ?? '');
$tax_code         = sanitize($_POST['tax_code']         ?? '');
$password         = $_POST['password']                  ?? '';
$confirm_password = $_POST['confirm_password']          ?? '';

if (!$full_name || !$email || !$phone || !$tax_code || !$password || !$confirm_password) {
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
    // Check email
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        setFlash('error', 'Email already exists.');
        redirect('/Uniworksmohinhhoa/public/register.php?type=company');
    }

    // Check tax_code
    $stmt = $pdo->prepare("SELECT id FROM companies WHERE tax_code = ?");
    $stmt->execute([$tax_code]);
    if ($stmt->fetch()) {
        setFlash('error', 'Tax code already registered.');
        redirect('/Uniworksmohinhhoa/public/register.php?type=company');
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert user với phone
    $stmt = $pdo->prepare("
        INSERT INTO users (email, password, full_name, phone, role)
        VALUES (?, ?, ?, ?, 'company')
    ");
    $stmt->execute([$email, $hashed_password, $full_name, $phone]);
    $user_id = $pdo->lastInsertId();

    // Insert company placeholder với tax_code
    $stmt = $pdo->prepare("
        INSERT INTO companies (user_id, company_name, tax_code)
        VALUES (?, ?, ?)
    ");
    $stmt->execute([$user_id, $full_name, $tax_code]);

    setFlash('success', 'Account created successfully. Please login.');
    redirect('/Uniworksmohinhhoa/public/login.php');

} catch (Exception $e) {
    setFlash('error', 'Registration failed. Please try again.');
    redirect('/Uniworksmohinhhoa/public/register.php?type=company');
}
