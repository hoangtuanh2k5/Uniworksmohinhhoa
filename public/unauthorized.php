<?php
require_once '../includes/functions.php';
$flash = getFlash();

include '../includes/header.php';
include '../includes/navbar.php';
?>

<main class="container center-box">
    <div class="simple-card">
        <h2 style="margin-bottom: 12px;">Unauthorized</h2>
        <p style="color:#7d7d91; margin-bottom: 18px;">
            You do not have permission to access this page.
        </p>

        <?php if ($flash): ?>
            <div class="flash <?= htmlspecialchars($flash['type']) ?>">
                <?= htmlspecialchars($flash['message']) ?>
            </div>
        <?php endif; ?>

        <a href="/Uniworksmohinhhoa/public/index.php" class="btn btn-primary">Back to Homepage</a>
    </div>
</main>

<?php include '../includes/footer.php'; ?>