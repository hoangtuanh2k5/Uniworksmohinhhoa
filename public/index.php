<?php
require_once '../includes/functions.php';

if (isLoggedIn()) {
    $role = $_SESSION['user']['role'];

    if ($role === 'student') {
        redirect('/Uniworksmohinhhoa/student/dashboard.php');
    } elseif ($role === 'company') {
        redirect('/Uniworksmohinhhoa/company/dashboard.php');
    } elseif ($role === 'admin') {
        redirect('/Uniworksmohinhhoa/admin/dashboard.php');
    }
}

include '../includes/header.php';
include '../includes/navbar.php';
?>

<main class="container">
    <section class="hero">
    <div class="hero-content">
        <span class="hero-badge">WELCOME TO UNIWORKS</span>

        <h1 class="hero-title">
            Find Your Perfect
            <span class="hero-highlight">
    <span class="hero-i">I</span><span class="hero-n">n</span>ternship
</span>
        </h1>

        <p class="hero-desc">
            Connect with verified companies and track your career journey in one place.
            The ultimate platform for university talent.
        </p>

        <div class="hero-actions">
            <a href="/Uniworksmohinhhoa/public/register.php" class="btn btn-primary">Get Started</a>
            <a href="/Uniworksmohinhhoa/public/login.php" class="btn btn-outline">Login</a>
        </div>
    </div>

    <div class="hero-visual">
        <div class="hero-card">
            <div class="hero-card-inner">🚀</div>
        </div>
    </div>
</section>

    <section class="section">
        <div class="section-title">
            <h2>Our Key Features</h2>
            <p>Everything you need to kickstart your career.</p>
        </div>

        <div class="features-grid">
            <div class="feature-card">
                <h3>Fast-Track Hiring</h3>
                <p>Apply to top internships with a single profile verified by your university.</p>
            </div>

            <div class="feature-card soft-purple">
                <h3>Verified Partners</h3>
                <p>Connect with over 500+ pre-verified companies looking for students like you.</p>
            </div>

            <div class="feature-card soft-yellow">
                <h3>Growth Insights</h3>
                <p>Track your applications and receive feedback to improve your career path.</p>
            </div>
        </div>
    </section>

    <section class="section dashboard-block">
        <div class="dashboard-card">
            <div class="dashboard-mini top">
                <strong>Application Status</strong>
                <p>+12%</p>
            </div>

            <div class="dashboard-mini bottom">
                <strong>Skills Analytics</strong>
                <p>Updated weekly</p>
            </div>
        </div>

        <div class="dashboard-text">
            <h2>Manage everything in one intuitive dashboard.</h2>

            <div class="dashboard-list">
                <div class="dashboard-item">
                    <h4>Unified Job Board</h4>
                    <p>Apply to multiple positions with a single profile, tailored by AI for each role.</p>
                </div>

                <div class="dashboard-item">
                    <h4>Direct Messaging</h4>
                    <p>Instant communication with hiring managers and team leads during the process.</p>
                </div>

                <div class="dashboard-item">
                    <h4>Resource Library</h4>
                    <p>Access exclusive preparation guides, mock interviews, and technical workshops.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="section-title">
            <h2>Wall of Love</h2>
            <p>Trusted by the next generation of industry leaders.</p>
        </div>

        <div class="testimonial-grid">
            <div class="testimonial-card">
                <h4>Sarah Chen</h4>
                <p>UniWorks streamlined my entire application process. I landed my dream internship in record time thanks to the intuitive dashboard.</p>
            </div>
            <div class="testimonial-card">
                <h4>Marcus Rodriguez</h4>
                <p>The platform’s design is exceptional. It made tracking multiple offers actually enjoyable instead of stressful.</p>
            </div>
            <div class="testimonial-card">
                <h4>Emily Watson</h4>
                <p>I recommend UniWorks to every student I meet. The centralized resource hub is a game changer for technical prep.</p>
            </div>
            <div class="testimonial-card">
                <h4>David Park</h4>
                <p>A must-have tool for recruiting season. The automated alerts saved me from missing several high-priority deadlines.</p>
            </div>
            <div class="testimonial-card">
                <h4>Jessica Lee</h4>
                <p>The community support and peer reviews provided by InternFlow gave me the confidence I needed to ace my interviews.</p>
            </div>
            <div class="testimonial-card">
                <h4>Alex Thompson</h4>
                <p>Scaling my career started here. The platform provided the structure I needed to manage high-volume applications efficiently.</p>
            </div>
        </div>
    </section>

    <section class="cta">
        <h2>Start your career journey today</h2>
        <p>Join thousands of students finding their dream roles at top-tier companies.</p>
        <a href="/Uniworksmohinhhoa/public/register.php" class="btn btn-dark">Create Account</a>
    </section>
</main>

<?php include '../includes/footer.php'; ?>