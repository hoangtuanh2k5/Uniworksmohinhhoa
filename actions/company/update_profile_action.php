<?php
session_start();
require_once '../../config/db.php';
require_once '../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/Uniworksmohinhhoa/company/profile.php');
}

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'company') {
    redirect('/Uniworksmohinhhoa/public/login.php');
}

$userId = $_SESSION['user']['id'];

$companyName = sanitize($_POST['company_name'] ?? '');
$taxCode = sanitize($_POST['tax_code'] ?? '');
$address = sanitize($_POST['address'] ?? '');
$website = sanitize($_POST['website'] ?? '');
$industryType = sanitize($_POST['industry_type'] ?? '');

if (!$companyName || !$taxCode) {
    setFlash('error', 'Company name and tax code are required.');
    redirect('/Uniworksmohinhhoa/company/profile.php');
}

try {
    $stmt = $pdo->prepare("
        UPDATE companies
        SET company_name = ?, tax_code = ?, address = ?, website = ?, industry_type = ?
        WHERE user_id = ?
    ");
    $stmt->execute([$companyName, $taxCode, $address, $website, $industryType, $userId]);

    setFlash('success', 'Profile updated successfully.');
    redirect('/Uniworksmohinhhoa/company/profile.php');
} catch (Exception $e) {
    setFlash('error', 'Failed to update profile.');
    redirect('/Uniworksmohinhhoa/company/profile.php');
}