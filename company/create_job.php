<?php
require_once '../includes/auth.php';
require_once '../config/db.php';
require_once '../includes/functions.php';
requireRole('company');

$user = currentUser();
$flash = getFlash();

$stmt = $pdo->prepare("SELECT id, company_name FROM companies WHERE user_id = ?");
$stmt->execute([$user['id']]);
$company = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$company) {
    redirect('/Uniworksmohinhhoa/company/profile.php?setup=1');
}

$periods = $pdo->query("SELECT * FROM internship_periods ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php';
?>

<style>
.job-create-hero{
    display:flex;
    justify-content:space-between;
    align-items:stretch;
    gap:20px;
    margin-bottom:24px;
}

.job-create-hero__text,
.job-create-hero__stat{
    background:#fff;
    border:1px solid #eee9f8;
    border-radius:28px;
    padding:24px 26px;
    box-shadow:0 10px 28px rgba(31,34,51,.05);
}

.job-create-hero__text{
    flex:1.5;
}

.job-create-hero__stat{
    flex:0.9;
    background:#fff7d6;
    border-color:#efe0a0;
    display:flex;
    flex-direction:column;
    justify-content:center;
}

.job-create-badge{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    margin:0 0 12px;
    padding:8px 14px;
    border-radius:999px;
    background:#f4f0ff;
    color:#6259aa;
    font-size:13px;
    font-weight:700;
}

.job-create-hero__text h1{
    margin:0 0 10px;
    font-size:42px;
    line-height:1.1;
    color:#1f2233;
    font-weight:800;
}

.job-create-hero__text p:last-child{
    margin:0;
    color:#6e7690;
    font-size:17px;
    line-height:1.7;
}

.job-create-hero__stat span{
    display:block;
    margin-bottom:10px;
    color:#8c7b30;
    font-size:13px;
    font-weight:800;
    text-transform:uppercase;
    letter-spacing:.04em;
}

.job-create-hero__stat strong{
    color:#1f2233;
    font-size:18px;
    line-height:1.6;
}

.job-create-card{
    background:#fff;
    border:1px solid #eee9f8;
    border-radius:30px;
    padding:28px;
    box-shadow:0 14px 30px rgba(31,34,51,.05);
}

.job-create-form{
    display:flex;
    flex-direction:column;
    gap:24px;
}

.job-create-section{
    border:1px solid #f1ecfa;
    border-radius:24px;
    padding:24px;
    background:#fcfbff;
}

.job-create-section--accent{
    background:#fffdf4;
    border-color:#f3e7b9;
}

.job-create-section__header{
    margin-bottom:20px;
}

.job-create-section__header h3{
    margin:0 0 6px;
    font-size:24px;
    color:#1f2233;
    font-weight:800;
}

.job-create-section__header p{
    margin:0;
    color:#7c849c;
    font-size:15px;
}

.job-create-grid{
    display:grid;
    grid-template-columns:repeat(2, minmax(0, 1fr));
    gap:18px;
}

.job-create-field{
    display:flex;
    flex-direction:column;
}

.job-create-field--full{
    grid-column:1 / -1;
}

.job-create-field label{
    margin-bottom:10px;
    font-size:16px;
    font-weight:700;
    color:#25283a;
}

.job-create-field input,
.job-create-field select,
.job-create-field textarea{
    width:100%;
    border:1.5px solid #ddd9ef;
    border-radius:18px;
    background:#fff;
    padding:15px 16px;
    font-size:16px;
    color:#1f2233;
    outline:none;
    transition:.2s ease;
    box-sizing:border-box;
}

.job-create-field textarea{
    resize:vertical;
    min-height:140px;
}

.job-create-field input:focus,
.job-create-field select:focus,
.job-create-field textarea:focus{
    border-color:#c7b7ff;
    box-shadow:0 0 0 4px rgba(207,192,255,.18);
}

.job-create-field input[type="date"]{
    color:#555d75;
}

.job-create-actions{
    display:flex;
    justify-content:flex-end;
    gap:12px;
    padding-top:4px;
}

.job-btn{
    min-width:140px;
    height:50px;
    border:none;
    border-radius:16px;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    text-decoration:none;
    font-weight:800;
    font-size:15px;
    cursor:pointer;
    transition:.2s ease;
}

.job-btn:hover{
    transform:translateY(-1px);
}

.job-btn--primary{
    background:#cfc0ff;
    color:#1f2233;
}

.job-btn--primary:hover{
    background:#c3b1ff;
}

.job-btn--ghost{
    background:#f6f1ff;
    color:#5c647d;
}

.job-btn--ghost:hover{
    background:#eee7ff;
}

.job-tip-list{
    margin:0;
    padding-left:18px;
    color:#6d748d;
    line-height:1.8;
}

.job-tip-list li + li{
    margin-top:6px;
}

@media (max-width: 1024px){
    .job-create-hero{
        flex-direction:column;
    }
}

@media (max-width: 768px){
    .job-create-hero__text h1{
        font-size:34px;
    }

    .job-create-grid{
        grid-template-columns:1fr;
    }

    .job-create-section,
    .job-create-card,
    .job-create-hero__text,
    .job-create-hero__stat{
        border-radius:22px;
    }

    .job-create-actions{
        flex-direction:column;
    }

    .job-btn{
        width:100%;
    }
}
</style>

<div class="company-shell">
    <aside class="company-sidebar">
        <div>
            <div class="company-brand">
                <div class="company-brand__logo">✦</div>
                <div class="company-brand__text">
                    <h3><?= htmlspecialchars($company['company_name']) ?></h3>
                    <p>Recruiter Portal</p>
                </div>
            </div>

            <nav class="company-nav">
                <a href="/Uniworksmohinhhoa/company/dashboard.php">Dashboard</a>
                <a href="/Uniworksmohinhhoa/company/applications.php">Applicants</a>
                <a href="/Uniworksmohinhhoa/company/manage_job.php" class="active">Jobs</a>
                <a href="/Uniworksmohinhhoa/company/messages.php">Messages</a>
                <a href="/Uniworksmohinhhoa/company/profile.php">Profile</a>
            </nav>
        </div>

        <div class="company-sidebar__footer">
            <a href="/Uniworksmohinhhoa/public/logout.php">↩ Logout</a>
        </div>
    </aside>

    <main class="company-main">
        <div class="job-create-hero">
            <div class="job-create-hero__text">
                <p class="job-create-badge">Company Portal</p>
                <h1>Create Internship Job</h1>
                <p>Create a clear internship post so students can quickly understand the role, requirements, number of slots, and application deadline.</p>
            </div>

            <div class="job-create-hero__stat">
                <span>Quick Tips</span>
                <strong>Use a specific title, clear requirements, and a realistic deadline to attract better applicants.</strong>
            </div>
        </div>

        <div class="job-create-card">
            <?php if ($flash): ?>
                <div class="flash <?= htmlspecialchars($flash['type']) ?>" style="margin-bottom:18px;">
                    <?= htmlspecialchars($flash['message']) ?>
                </div>
            <?php endif; ?>

            <form action="../actions/company/create_job_action.php" method="POST" class="job-create-form">
                <div class="job-create-section">
                    <div class="job-create-section__header">
                        <h3>Basic Information</h3>
                        <p>Enter the main details of the internship position.</p>
                    </div>

                    <div class="job-create-grid">
                        <div class="job-create-field job-create-field--full">
                            <label for="title">Title</label>
                            <input type="text" id="title" name="title" required>
                        </div>

                        <div class="job-create-field">
                            <label for="period_id">Internship Period</label>
                            <select id="period_id" name="period_id" required>
                                <option value="">Select period</option>
                                <?php foreach ($periods as $period): ?>
                                    <option value="<?= $period['id'] ?>">
                                        <?= htmlspecialchars($period['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="job-create-field">
                            <label for="slots">Slots</label>
                            <input type="number" id="slots" name="slots" min="1" required>
                        </div>

                        <div class="job-create-field job-create-field--full">
                            <label for="deadline">Deadline</label>
                            <input type="date" id="deadline" name="deadline" required>
                        </div>
                    </div>
                </div>

                <div class="job-create-section job-create-section--accent">
                    <div class="job-create-section__header">
                        <h3>Job Details</h3>
                        <p>Describe what the intern will do and what qualifications are expected.</p>
                    </div>

                    <div class="job-create-grid">
                        <div class="job-create-field job-create-field--full">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" required></textarea>
                        </div>

                        <div class="job-create-field job-create-field--full">
                            <label for="requirements">Requirements</label>
                            <textarea id="requirements" name="requirements" required></textarea>
                        </div>
                    </div>
                </div>

                <div class="job-create-section">
                    <div class="job-create-section__header">
                        <h3>Posting Notes</h3>
                        <p>A few small reminders before publishing the internship job.</p>
                    </div>

                    <ul class="job-tip-list">
                        <li>Make sure the title matches the actual internship role.</li>
                        <li>Keep requirements short, clear, and easy for students to understand.</li>
                        <li>Check the deadline carefully before creating the job post.</li>
                    </ul>
                </div>

                <div class="job-create-actions">
                    <a href="/Uniworksmohinhhoa/company/manage_job.php" class="job-btn job-btn--ghost">Back</a>
                    <button type="submit" class="job-btn job-btn--primary">Create Job</button>
                </div>
            </form>
        </div>
    </main>
</div>

<?php include '../includes/footer.php'; ?>