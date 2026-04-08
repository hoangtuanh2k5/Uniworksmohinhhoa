<?php $user = currentUser(); ?>
<nav class="topbar">
    <div class="topbar__inner">
        <div class="topbar__brand">
            <a href="/Uniworksmohinhhoa/public/index.php">UniWorks</a>
        </div>

        <div class="topbar__menu">
            <a href="/Uniworksmohinhhoa/public/index.php">Dashboard</a>
            <a href="#">Internships</a>
            <a href="#">Applications</a>
            <a href="#">Messages</a>
        </div>

        <div class="topbar__actions">
            <?php if ($user): ?>
                <span class="topbar__helper"><?= htmlspecialchars($user['full_name']) ?></span>
                <a href="/Uniworksmohinhhoa/public/logout.php" class="topbar__pill">Logout</a>
            <?php else: ?>
                <span class="topbar__helper">Already have an account?</span>
                <a href="/Uniworksmohinhhoa/public/login.php" class="topbar__pill">Sign in</a>
            <?php endif; ?>
        </div>
    </div>
</nav>