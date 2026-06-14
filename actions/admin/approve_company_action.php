<?php
require_once '../../config/db.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';
requireRole('admin');

$company_id = (int)($_POST['company_id'] ?? 0);
$decision   = $_POST['decision'] ?? ''; // approve | reject | suspend | unsuspend

if ($company_id <= 0 || !in_array($decision, ['approve', 'reject', 'suspend', 'unsuspend'], true)) {
    setFlash('error', 'Invalid request.');
    redirect('/Uniworksmohinhhoa/admin/company_approvals.php');
}

$status = match($decision) {
    'approve'   => 'approved',
    'reject'    => 'rejected',
    'suspend'   => 'suspended',
    'unsuspend' => 'approved',
};

$pdo->prepare("UPDATE companies SET status = ? WHERE id = ?")
    ->execute([$status, $company_id]);

$msg = match($decision) {
    'approve'   => 'Company approved successfully.',
    'reject'    => 'Company rejected.',
    'suspend'   => 'Company account suspended.',
    'unsuspend' => 'Company account reactivated.',
};

setFlash('success', $msg);
redirect('/Uniworksmohinhhoa/admin/company_approvals.php' . (isset($_POST['back_status']) ? '?status=' . $_POST['back_status'] : ''));
