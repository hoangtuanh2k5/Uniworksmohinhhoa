<?php
include '../../includes/db_connect.php';
session_start();

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Validate ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: ../../admin/applications.php?error=invalid_id");
    exit();
}

$app_id = (int) $_GET['id'];

// Start transaction
$conn->begin_transaction();

try {

    // 1. Check application exists + status
    $check = $conn->prepare("SELECT status FROM applications WHERE id = ?");
    $check->bind_param("i", $app_id);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows === 0) {
        throw new Exception("Application not found");
    }

    $row = $result->fetch_assoc();

    // ❗ Tránh approve lại nhiều lần
    if ($row['status'] === 'approved') {
        throw new Exception("Already approved");
    }

    // 2. Update application
    $stmt = $conn->prepare("
        UPDATE applications 
        SET status = 'approved', admin_approved = 1 
        WHERE id = ?
    ");
    $stmt->bind_param("i", $app_id);
    $stmt->execute();

    // 3. Insert registration (tránh duplicate)
    $checkReg = $conn->prepare("
        SELECT id FROM internship_registrations WHERE application_id = ?
    ");
    $checkReg->bind_param("i", $app_id);
    $checkReg->execute();
    $regResult = $checkReg->get_result();

    if ($regResult->num_rows === 0) {

        $reg_stmt = $conn->prepare("
            INSERT INTO internship_registrations (application_id, start_date) 
            VALUES (?, CURDATE())
        ");
        $reg_stmt->bind_param("i", $app_id);
        $reg_stmt->execute();
    }

    // Commit
    $conn->commit();

    header("Location: ../../admin/applications.php?msg=approved");
    exit();

} catch (Exception $e) {

    $conn->rollback();

    // Debug (có thể tắt khi nộp bài)
    // echo $e->getMessage();

    header("Location: ../../admin/applications.php?error=failed");
    exit();
}
?>