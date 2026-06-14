<?php
require_once '../includes/functions.php';
$flash = getFlash();

// Lấy email prefill nếu có (sau login thành công)
$prefillEmail = '';
if (isset($_SESSION['prefill_email'])) {
    $prefillEmail = $_SESSION['prefill_email'];
    unset($_SESSION['prefill_email']);
}

include '../includes/header.php';
include '../includes/navbar.php';
?>

<main class="container auth-page">
    <section class="auth-left">
        <span class="hero-badge">WELCOME TO UNIWORKS</span>
        <h1>Find Your Perfect <span>Internship</span></h1>
        <p>
            Connect with verified companies and track your career journey in one place.
            The ultimate platform for university talent.
        </p>
    </section>

    <section class="auth-card">
        <h2>Welcome Back</h2>
        <p class="sub">Join the central hub for university career opportunities.</p>

        <div class="role-tabs">
            <a href="#" class="active">Student</a>
            <a href="#">Company</a>
            <a href="#">Admin</a>
        </div>

<<<<<<< Updated upstream
        <div class="auth-tabs">
            <a href="login.php" class="active">LOGIN</a>
            <a href="register.php">REGISTER</a>
        </div>

        <?php if ($flash): ?>
            <div class="flash <?= htmlspecialchars($flash['type']) ?>">
                <?= htmlspecialchars($flash['message']) ?>
=======
                <?php if ($flash): ?>
                    <div class="flash <?= htmlspecialchars($flash['type']) ?>" id="flash-msg">
                        <?= htmlspecialchars($flash['message']) ?>
                        <?php if ($flash['type'] === 'success' && isset($_SESSION['redirect_to'])): ?>
                            <div class="flash-bar"></div>
                        <?php endif; ?>
                    </div>
                    <?php if ($flash['type'] === 'success' && isset($_SESSION['redirect_to'])): ?>
                        <?php $dest = $_SESSION['redirect_to']; unset($_SESSION['redirect_to']); ?>
                        <script>
                            setTimeout(function () {
                                window.location.href = <?= json_encode($dest) ?>;
                            }, 800);
                        </script>
                    <?php endif; ?>
                <?php endif; ?>

                <form action="../actions/auth/login_action.php" method="POST" class="login-mockup__form">
                    <div class="login-mockup__field">
                        <label>Email Address</label>
                        <input type="email" name="email" class="login-mockup__input" placeholder="name@university.edu" required
                               value="<?= htmlspecialchars($prefillEmail) ?>">
                    </div>

                    <div class="login-mockup__field">
                        <label>Password</label>
                        <input type="password" name="password" class="login-mockup__input" placeholder="••••••••" required
                               value="<?= $prefillEmail ? '••••••••' : '' ?>">
                    </div>

                    <button type="submit" class="login-mockup__submit">
                        Sign In to Your Account
                    </button>
                </form>

                <p class="login-mockup__bottom-text">
                    Don’t have an account? <a href="register.php?type=student">Register</a>
                </p>
>>>>>>> Stashed changes
            </div>
        <?php endif; ?>

        <form action="../actions/auth/login_action.php" method="POST">
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary auth-submit">Login to Your Account</button>
        </form>
    </section>
</main>

<?php include '../includes/footer.php'; ?>