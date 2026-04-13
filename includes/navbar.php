<?php $user = currentUser(); ?>
<nav class="topbar">
    <div class="topbar__inner">
        <!-- Logo -->
        <div class="topbar__brand">
            <a href="/Uniworksmohinhhoa/public/index.php">
                <span class="topbar__logo-box">✦</span>
                <span>UniWorks</span>
            </a>
        </div>

        <!-- Menu -->
        <div class="topbar__menu">
            <a href="/Uniworksmohinhhoa/public/index.php">Dashboard</a>
            <a href="/Uniworksmohinhhoa/public/login.php">Internships</a>
            <a href="/Uniworksmohinhhoa/public/login.php">Applications</a>
            <a href="/Uniworksmohinhhoa/public/login.php">Messages</a>
        </div>

        <!-- Actions -->
        <div class="topbar__actions">
            <?php if ($user): ?>
                <span class="topbar__helper"><?= htmlspecialchars($user['full_name']) ?></span>
                <a href="/Uniworksmohinhhoa/public/logout.php" class="topbar__pill">Logout</a>
            <?php else: ?>
                <a href="/Uniworksmohinhhoa/public/login.php?type=student" class="topbar__helper">Already have an account?</a>
                <a href="/Uniworksmohinhhoa/public/login.php" class="topbar__pill">Login</a>
            <?php endif; ?>
        </div>
    </div>
</nav>