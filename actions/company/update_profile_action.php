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

<<<<<<< Updated upstream
$userId = $_SESSION['user']['id'];

$companyName = sanitize($_POST['company_name'] ?? '');
$taxCode = sanitize($_POST['tax_code'] ?? '');
$address = sanitize($_POST['address'] ?? '');
$website = sanitize($_POST['website'] ?? '');
$industryType = sanitize($_POST['industry_type'] ?? '');
=======
$user_id       = $_SESSION['user']['id'];
$company_name  = sanitize($_POST['company_name']  ?? '');
$tax_code      = sanitize($_POST['tax_code']      ?? '');
$address       = sanitize($_POST['address']       ?? '');
$website       = sanitize($_POST['website']       ?? '');
$industry_type = sanitize($_POST['industry_type'] ?? '');
$phone         = sanitize($_POST['phone']         ?? '');
>>>>>>> Stashed changes

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

<<<<<<< Updated upstream
    setFlash('success', 'Profile updated successfully.');
    redirect('/Uniworksmohinhhoa/company/profile.php');
} catch (Exception $e) {
    setFlash('error', 'Failed to update profile.');
    redirect('/Uniworksmohinhhoa/company/profile.php');
}
=======
    if ($existing) {
        $pdo->prepare("
            UPDATE companies
            SET company_name = ?, tax_code = ?, address = ?, website = ?, industry_type = ?
            WHERE user_id = ?
        ")->execute([
            $company_name,
            $tax_code,
            $address !== '' ? $address : null,
            $website !== '' ? $website : null,
            $industry_type !== '' ? $industry_type : null,
            $user_id
        ]);
    } else {
        $pdo->prepare("
            INSERT INTO companies (user_id, company_name, tax_code, address, website, industry_type)
            VALUES (?, ?, ?, ?, ?, ?)
        ")->execute([
            $user_id,
            $company_name,
            $tax_code,
            $address !== '' ? $address : null,
            $website !== '' ? $website : null,
            $industry_type !== '' ? $industry_type : null
        ]);
    }

    // Cập nhật phone vào bảng users
    $pdo->prepare("UPDATE users SET phone = ? WHERE id = ?")
        ->execute([$phone !== '' ? $phone : null, $user_id]);

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
>>>>>>> Stashed changes
