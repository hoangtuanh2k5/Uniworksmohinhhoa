<?php
require_once '../includes/functions.php';
$flash = getFlash();

include '../includes/header.php';
include '../includes/navbar.php';
?>

<main class="login-mockup">
    <div class="login-mockup__inner">

        <section class="login-mockup__top">
            <div class="login-mockup__card">
                <h2 class="login-mockup__title">Welcome Back</h2>
                <p class="login-mockup__subtitle">
                    Join the central hub for university career
                    <br>
                    opportunities.
                </p>

                <div class="login-mockup__tabs">
                    <a href="login.php" class="active">LOGIN</a>
                    <a href="register.php?type=student">REGISTER</a>
                </div>

                <?php if ($flash): ?>
                    <div class="flash <?= htmlspecialchars($flash['type']) ?>">
                        <?= htmlspecialchars($flash['message']) ?>
                    </div>
                <?php endif; ?>

                <form action="../actions/auth/login_action.php" method="POST" class="login-mockup__form">
                    <div class="login-mockup__field">
                        <label>Email Address</label>
                        <input type="email" name="email" class="login-mockup__input" placeholder="name@university.edu" required>
                    </div>

                    <div class="login-mockup__field">
                        <label>Password</label>
                        <input type="password" name="password" class="login-mockup__input" placeholder="••••••••" required>
                    </div>

                    <button type="submit" class="login-mockup__submit">
                        Sign In to Your Account
                    </button>
                </form>

                <p class="login-mockup__bottom-text">
                    Don’t have an account? <a href="register.php?type=student">Register</a>
                </p>
            </div>
        </section>

        <section class="login-mockup__bottom">
            <div class="login-mockup__curve"></div>

            <div class="login-mockup__features">
                <div class="login-mockup__feature login-mockup__feature--blue">
                    <div class="login-mockup__icon">🚀</div>
                    <h3>Fast-Track Hiring</h3>
                    <p>Apply to top internships with a single profile verified by your university.</p>
                </div>

                <div class="login-mockup__feature login-mockup__feature--purple">
                    <div class="login-mockup__icon">🛡</div>
                    <h3>Verified Partners</h3>
                    <p>Connect with over 500+ pre-vetted companies looking for students like you.</p>
                </div>

                <div class="login-mockup__feature login-mockup__feature--yellow">
                    <div class="login-mockup__icon">▣</div>
                    <h3>Growth Insights</h3>
                    <p>Track your applications and receive feedback to improve your career path.</p>
                </div>
            </div>
        </section>

    </div>
</main>

<?php include '../includes/footer.php'; ?>