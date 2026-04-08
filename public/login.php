<?php
require_once '../includes/functions.php';
$flash = getFlash();

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

        <div class="auth-tabs">
            <a href="login.php" class="active">LOGIN</a>
            <a href="register.php">REGISTER</a>
        </div>

        <?php if ($flash): ?>
            <div class="flash <?= htmlspecialchars($flash['type']) ?>">
                <?= htmlspecialchars($flash['message']) ?>
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