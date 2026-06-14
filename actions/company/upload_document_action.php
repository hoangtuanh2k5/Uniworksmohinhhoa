<?php
require_once '../../config/db.php';
require_once '../../includes/functions.php';

if (!isLoggedIn() || $_SESSION['user']['role'] !== 'company') {
    redirect('/Uniworksmohinhhoa/public/login.php');
}

$user_id = $_SESSION['user']['id'];

// Lấy company_id
$stmt = $pdo->prepare("SELECT id FROM companies WHERE user_id = ?");
$stmt->execute([$user_id]);
$company = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$company) {
    setFlash('error', 'Company profile not found.');
    redirect('/Uniworksmohinhhoa/company/profile.php');
}

$company_id = $company['id'];
$doc_type   = sanitize($_POST['doc_type'] ?? '');

if (empty($_FILES['document']['name'])) {
    setFlash('error', 'Please select a file to upload.');
    redirect('/Uniworksmohinhhoa/company/profile.php');
}

$file    = $_FILES['document'];
$allowed = ['application/pdf', 'image/jpeg', 'image/png',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
$maxSize = 10 * 1024 * 1024; // 10MB

if (!in_array($file['type'], $allowed)) {
    setFlash('error', 'Only PDF, JPG, PNG, DOC, DOCX files are allowed.');
    redirect('/Uniworksmohinhhoa/company/profile.php');
}

if ($file['size'] > $maxSize) {
    setFlash('error', 'File must be under 10MB.');
    redirect('/Uniworksmohinhhoa/company/profile.php');
}

$origName = pathinfo($file['name'], PATHINFO_FILENAME);
$ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = 'doc_' . $company_id . '_' . time() . '.' . $ext;
$dest     = __DIR__ . '/../../uploads/company_docs/' . $filename;

if (!move_uploaded_file($file['tmp_name'], $dest)) {
    setFlash('error', 'Upload failed. Please try again.');
    redirect('/Uniworksmohinhhoa/company/profile.php');
}

$url = 'uploads/company_docs/' . $filename;
$pdo->prepare("
    INSERT INTO company_documents (company_id, doc_name, doc_url, doc_type)
    VALUES (?, ?, ?, ?)
")->execute([$company_id, $origName . '.' . $ext, $url, $doc_type]);

setFlash('success', 'Document uploaded successfully.');
redirect('/Uniworksmohinhhoa/company/profile.php');
