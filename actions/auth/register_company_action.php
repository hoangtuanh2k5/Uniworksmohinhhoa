<?php
require_once '../../config/db.php';
require_once '../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/Uniworksmohinhhoa/public/register.php?type=company');
}

$full_name = sanitize($_POST['full_name'] ?? '');
$email = sanitize($_POST['email'] ?? '');
$phone = sanitize($_POST['phone'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';
$company_name = sanitize($_POST['company_name'] ?? '');
$tax_code = sanitize($_POST['tax_code'] ?? '');
$address = sanitize($_POST['address'] ?? '');
$website = sanitize($_POST['website'] ?? '');
$industry_type = sanitize($_POST['industry_type'] ?? '');

if (!$full_name || !$email || !$password || !$confirm_password || !$company_name || !$tax_code) {
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

    $stmt = $pdo->prepare("SELECT id FROM companies WHERE tax_code = ?");
    $stmt->execute([$tax_code]);
    if ($stmt->fetch()) {
        setFlash('error', 'Tax code already exists.');
        redirect('/Uniworksmohinhhoa/public/register.php?type=company');
    }

    $pdo->beginTransaction();

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("
        INSERT INTO users (email, password, full_name, phone, role)
        VALUES (?, ?, ?, ?, 'company')
    ");
    $stmt->execute([$email, $hashed_password, $full_name, $phone]);

    $user_id = $pdo->lastInsertId();

    $stmt = $pdo->prepare("
        INSERT INTO companies (user_id, company_name, tax_code, address, website, industry_type)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$user_id, $company_name, $tax_code, $address, $website, $industry_type]);

    $pdo->commit();

    setFlash('success', 'Company account created successfully. Please login.');
    redirect('/your_folder_name/public/login.php');

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    setFlash('error', 'Registration failed.');
    redirect('/Uniworksmohinhhoa/public/register.php?type=company');
}