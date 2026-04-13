<?php
require_once '../includes/functions.php';
$type = $_GET['type'] ?? 'student';
$flash = getFlash();

include '../includes/header.php';
include '../includes/navbar.php';
?>

<main class="auth-shell">
    <div class="auth-shell__inner">
        <section class="auth-hero">
            <div class="auth-hero__badge">✦, HI, WELCOME TO UNIWORKS!!</div>

            <h1 class="auth-hero__title">
                Find Your
                <br>
                Perfect
                <br>
                <span class="auth-hero__highlight">
                    <span class="auth-hero__i">I</span><span class="auth-hero__n">n</span>ternship
                </span>
            </h1>

            <p class="auth-hero__desc">
                Connect with verified companies and track your
                <br>
                career journey in one place. The ultimate platform for
                <br>
                university talent.
            </p>
        </section>

        <section class="register-card">
            <h2 class="register-card__title">Create Your Account</h2>
            <p class="register-card__subtitle">
                Join the central hub for university career
                <br>
                opportunities.
            </p>

            <div class="register-card__role-label">SELECT YOUR ROLE</div>

            <div class="register-role-tabs">
                <a href="register.php?type=student" class="<?= $type === 'student' ? 'active' : '' ?>">Student</a>
                <a href="register.php?type=company" class="<?= $type === 'company' ? 'active' : '' ?>">Company</a>
                <a href="#" onclick="return false;">Admin</a>
            </div>

            <div class="register-mode-tabs">
                <a href="login.php">LOGIN</a>
                <a href="register.php?type=<?= htmlspecialchars($type) ?>" class="active">REGISTER</a>
            </div>

            <?php if ($flash): ?>
                <div class="flash <?= htmlspecialchars($flash['type']) ?>">
                    <?= htmlspecialchars($flash['message']) ?>
                </div>
            <?php endif; ?>

            <?php if ($type === 'student'): ?>
                <form action="../actions/auth/register_student_action.php" method="POST" class="register-form">
                    <div class="register-field">
                        <label>Full Name</label>
                        <input type="text" name="full_name" class="register-input" placeholder="John Doe" required>
                    </div>

                    <div class="register-field">
                        <label>Email Address</label>
                        <input type="email" name="email" class="register-input" placeholder="name@university.edu" required>
                    </div>

                    <div class="register-grid-2">
                        <div class="register-field">
                            <label>Password</label>
                            <input type="password" name="password" class="register-input" placeholder="••••••••" required>
                        </div>

                        <div class="register-field">
                            <label>Confirm</label>
                            <input type="password" name="confirm_password" class="register-input" placeholder="••••••••" required>
                        </div>
                    </div>

                    <button type="submit" class="register-submit">Create Your Account</button>

                    <div class="register-bottom-text">
                        Already have an account? <a href="login.php">Sign In</a>
                    </div>
                </form>
            <?php else: ?>
                <form action="../actions/auth/register_company_action.php" method="POST" class="register-form">
                    <div class="register-field">
                        <label>Full Name</label>
                        <input type="text" name="full_name" class="register-input" placeholder="John Doe" required>
                    </div>

                    <div class="register-field">
                        <label>Email Address</label>
                        <input type="email" name="email" class="register-input" placeholder="name@company.com" required>
                    </div>

                    <div class="register-grid-2">
                        <div class="register-field">
                            <label>Password</label>
                            <input type="password" name="password" class="register-input" placeholder="••••••••" required>
                        </div>

                        <div class="register-field">
                            <label>Confirm</label>
                            <input type="password" name="confirm_password" class="register-input" placeholder="••••••••" required>
                        </div>
                    </div>

                    <button type="submit" class="register-submit">Create Your Account</button>

                    <div class="register-bottom-text">
                        Already have an account? <a href="login.php">Sign In</a>
                    </div>
                </form>
            <?php endif; ?>
        </section>
    </div>
</main>

<?php include '../includes/footer.php'; ?>