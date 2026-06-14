<?php
require_once __DIR__ . '/functions.php';

function requireLogin() {
    if (!isLoggedIn()) {
        setFlash('error', 'Please login first.');
        redirect('/Uniworksmohinhhoa/public/login.php');
    }
}

function requireRole($role) {
    requireLogin();

    if (!isRole($role)) {
        setFlash('error', 'You do not have permission to access this page.');
        redirect('/Uniworksmohinhhoa/public/unauthorized.php');
    }
}

/**
 * Bắt company phải hoàn thiện profile + upload giấy phép kinh doanh
 * trước khi truy cập các trang khác (trừ profile.php)
 */
function requireCompanyComplete($pdo) {
    requireRole('company');

    $user = currentUser();

    // Kiểm tra thông tin cơ bản company
    $stmt = $pdo->prepare("
        SELECT id, company_name, tax_code, address, industry_type
        FROM companies WHERE user_id = ?
    ");
    $stmt->execute([$user['id']]);
    $company = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$company
        || empty($company['company_name'])
        || empty($company['tax_code'])
        || empty($company['address'])
        || empty($company['industry_type'])
    ) {
        setFlash('error', 'Please complete your company profile before continuing.');
        redirect('/Uniworksmohinhhoa/company/profile.php?setup=1');
    }

    // Kiểm tra đã upload Business License chưa
    $stmt = $pdo->prepare("
        SELECT id FROM company_documents
        WHERE company_id = ? AND doc_type = 'Business License'
        LIMIT 1
    ");
    $stmt->execute([$company['id']]);
    if (!$stmt->fetch()) {
        setFlash('error', 'Please upload your Business License document before continuing.');
        redirect('/Uniworksmohinhhoa/company/profile.php?setup=1');
    }
}
?>