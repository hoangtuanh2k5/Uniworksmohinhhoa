<?php
require_once '../includes/auth.php';
requireRole('student');
include '../includes/header.php';
include '../includes/navbar.php';
?>
<main class="container center-box">
    <div class="simple-card">
        <h2>Student Dashboard</h2>
    </div>
</main>
<?php include '../includes/footer.php'; ?>