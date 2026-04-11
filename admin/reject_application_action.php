<?php
// actions/admin/reject_application_action.php
include '../../includes/db_connect.php';

if (isset($_GET['id'])) {
    $app_id = $_GET['id'];

    // Sử dụng Prepared Statement để bảo mật
    // Cập nhật trạng thái application thành 'rejected' và admin_approved thành 0 [cite: 88, 259]
    $stmt = $conn->prepare("UPDATE applications SET status = 'rejected', admin_approved = 0 WHERE id = ?");
    $stmt->bind_param("i", $app_id);

    if ($stmt->execute()) {
        // Sau khi cập nhật thành công, quay lại trang danh sách application [cite: 225]
        header("Location: ../../admin/applications.php?msg=rejected");
    } else {
        header("Location: ../../admin/applications.php?error=failed");
    }
    $stmt->close();
}
$conn->close();
?>