<?php
require_once '../../config/db.php';
require_once '../../includes/functions.php';

if (!isLoggedIn() || $_SESSION['user']['role'] !== 'company') {
    redirect('/Uniworksmohinhhoa/public/login.php');
}

$user_id = $_SESSION['user']['id'];
$company_name = sanitize($_POST['company_name'] ?? '');
$tax_code = sanitize($_POST['tax_code'] ?? '');
$address = sanitize($_POST['address'] ?? '');
$website = sanitize($_POST['website'] ?? '');
$industry_type = sanitize($_POST['industry_type'] ?? '');
$phone = sanitize($_POST['phone'] ?? '');

if ($company_name === '' || $tax_code === '') {
    setFlash('error', 'Company Name and Tax Code are required.');
    redirect('/Uniworksmohinhhoa/company/profile.php?setup=1');
}

try {
    $stmt = $pdo->prepare("SELECT id FROM companies WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        $stmt = $pdo->prepare("
            UPDATE companies
            SET company_name = ?, tax_code = ?, address = ?, website = ?, industry_type = ?
            WHERE user_id = ?
        ");
        $stmt->execute([
            $company_name, $tax_code,
            $address !== '' ? $address : null,
            $website !== '' ? $website : null,
            $industry_type !== '' ? $industry_type : null,
            $user_id
        ]);
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO companies (user_id, company_name, tax_code, address, website, industry_type)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $user_id, $company_name, $tax_code,
            $address !== '' ? $address : null,
            $website !== '' ? $website : null,
            $industry_type !== '' ? $industry_type : null
        ]);
    }

    // Cập nhật phone trong bảng users
    if ($phone !== '') {
        $pdo->prepare("UPDATE users SET phone = ? WHERE id = ?")->execute([$phone, $user_id]);
    }

    setFlash('success', 'Company profile saved successfully.');
    redirect('/Uniworksmohinhhoa/company/profile.php?success=1');

} catch (PDOException $e) {
    if ($e->getCode() == 23000) {
        setFlash('error', 'Tax Code already exists.');
    } else {
        setFlash('error', 'Database error: ' . $e->getMessage());
    }
    redirect('/Uniworksmohinhhoa/company/profile.php?setup=1');
} catch (Exception $e) {
    setFlash('error', 'Failed to save profile.');
    redirect('/Uniworksmohinhhoa/company/profile.php?setup=1');
}