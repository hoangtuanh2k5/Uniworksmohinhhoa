<?php
require_once '../includes/auth.php';
require_once '../config/db.php';
require_once '../includes/functions.php';
requireRole('student');

$user = currentUser();

$keyword = trim($_GET['keyword'] ?? '');

/*
|-------------------------------------------------------
| Lấy danh sách jobs + search theo title/company/industry
|-------------------------------------------------------
*/
$sql = "
    SELECT 
        j.id,
        j.title,
        j.description,
        j.requirements,
        j.deadline,
        j.slots,
        j.status,
        c.company_name,
        c.industry_type,
        c.address,
        ip.name AS period_name
    FROM jobs j
    INNER JOIN companies c ON j.company_id = c.id
    LEFT JOIN internship_periods ip ON j.period_id = ip.id
    WHERE j.status = 'open'
";

$params = [];

if ($keyword !== '') {
    $sql .= " 
        AND (
            j.title LIKE ?
            OR c.company_name LIKE ?
            OR c.industry_type LIKE ?
            OR j.description LIKE ?
        )
    ";
    $searchValue = '%' . $keyword . '%';
    $params[] = $searchValue;
    $params[] = $searchValue;
    $params[] = $searchValue;
    $params[] = $searchValue;
}

$sql .= " ORDER BY j.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php';
?>

<style>
.student-jobs-header{
    margin-bottom: 24px;
}

.student-jobs-header h1{
    margin: 0 0 8px;
    font-size: 44px;
    line-height: 1.1;
    color: #1f2233;
    font-weight: 800;
}

.student-jobs-header p{
    margin: 0;
    font-size: 18px;
    color: #7a8096;
}

.student-search-bar{
    margin: 28px 0 30px;
}

.student-search-form{
    display: flex;
    align-items: center;
    gap: 14px;
    background: #ffffff;
    border: 1px solid #ece9f7;
    border-radius: 20px;
    padding: 14px 18px;
    box-shadow: 0 8px 24px rgba(34, 34, 34, 0.04);
}

.student-search-icon{
    font-size: 20px;
    color: #9aa1b5;
    flex-shrink: 0;
}

.student-search-input{
    flex: 1;
    border: none;
    outline: none;
    background: transparent;
    font-size: 17px;
    color: #1f2233;
}

.student-search-input::placeholder{
    color: #98a0b5;
}

.student-search-btn{
    border: none;
    background: #cfc0ff;
    color: #1f2233;
    font-weight: 700;
    font-size: 15px;
    border-radius: 14px;
    padding: 12px 22px;
    cursor: pointer;
    transition: .2s ease;
}

.student-search-btn:hover{
    background: #c1afff;
    transform: translateY(-1px);
}

.student-clear-btn{
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    background: #f4f1ff;
    color: #5d647a;
    font-weight: 600;
    font-size: 15px;
    border-radius: 14px;
    padding: 12px 18px;
    transition: .2s ease;
}

.student-clear-btn:hover{
    background: #ece6ff;
}

.student-jobs-grid{
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 24px;
}

.student-job-card{
    position: relative;
    border-radius: 28px;
    padding: 26px;
    min-height: 280px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    overflow: hidden;
    box-shadow: 0 10px 28px rgba(37, 40, 58, 0.05);
}

.student-job-card.purple{
    background: #cdc3ff;
}

.student-job-card.yellow{
    background: #efd867;
}

.student-job-top{
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 18px;
}

.student-job-logo{
    width: 56px;
    height: 56px;
    border-radius: 18px;
    background: rgba(255,255,255,0.75);
    color: #1f2233;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    font-weight: 800;
    box-shadow: inset 0 1px 0 rgba(255,255,255,0.65);
}

.student-job-badge{
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 8px 14px;
    border-radius: 999px;
    background: rgba(255,255,255,0.72);
    color: #5f657b;
    font-size: 12px;
    font-weight: 800;
    letter-spacing: .04em;
    text-transform: uppercase;
}

.student-job-content h3{
    margin: 0 0 8px;
    font-size: 22px;
    line-height: 1.25;
    color: #1f2233;
    font-weight: 800;
}

.student-job-company{
    margin: 0 0 14px;
    font-size: 18px;
    color: #4f5770;
    font-weight: 500;
}

.student-job-meta{
    display: flex;
    flex-wrap: wrap;
    gap: 14px;
    margin-bottom: 18px;
    color: #65708b;
    font-size: 15px;
}

.student-job-meta span{
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.student-job-desc{
    margin: 0;
    color: #4e556c;
    font-size: 16px;
    line-height: 1.7;
}

.student-job-bottom{
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    margin-top: 26px;
}

.student-job-status{
    font-size: 15px;
    font-weight: 800;
    color: #1f2233;
    text-transform: capitalize;
}

.student-job-action{
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 130px;
    height: 48px;
    padding: 0 20px;
    border-radius: 999px;
    background: rgba(255,255,255,0.62);
    color: #1f2233;
    text-decoration: none;
    font-weight: 800;
    transition: .2s ease;
}

.student-job-action:hover{
    background: rgba(255,255,255,0.82);
    transform: translateY(-1px);
}

.student-jobs-empty{
    background: #fff;
    border-radius: 24px;
    padding: 40px 28px;
    text-align: center;
    color: #6b738a;
    border: 1px solid #ece9f7;
}

@media (max-width: 1100px){
    .student-jobs-grid{
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px){
    .student-jobs-header h1{
        font-size: 34px;
    }

    .student-search-form{
        flex-wrap: wrap;
    }

    .student-search-btn,
    .student-clear-btn{
        width: 100%;
    }

    .student-job-card{
        min-height: auto;
        padding: 22px;
    }

    .student-job-bottom{
        flex-direction: column;
        align-items: stretch;
    }

    .student-job-action{
        width: 100%;
    }
}
</style>

<div class="student-shell">
    <aside class="student-sidebar">
        <div>
            <div class="student-brand">
                <div class="student-brand__logo">✦</div>
                <div class="student-brand__text">
                    <h3><?= htmlspecialchars($user['full_name']) ?></h3>
                    <p>Aspiring Student</p>
                </div>
            </div>

            <nav class="student-nav">
                <a href="/Uniworksmohinhhoa/student/dashboard.php">Dashboard</a>
                <a href="/Uniworksmohinhhoa/student/applications.php">Applications</a>
                <a href="/Uniworksmohinhhoa/student/jobs.php" class="active">Internships</a>
                <a href="/Uniworksmohinhhoa/student/messages.php">Messages</a>
                <a href="/Uniworksmohinhhoa/student/profile.php">Profile</a>
                <a href="/Uniworksmohinhhoa/student/report.php">Final Report</a>
                <a href="/Uniworksmohinhhoa/student/evaluation.php">Evaluation</a>
            </nav>
        </div>

        <div class="student-sidebar__footer">
            <a href="/Uniworksmohinhhoa/public/logout.php">↩ Logout</a>
        </div>
    </aside>

    <main class="student-main">
        <div class="student-jobs-header">
            <h1>Available Internships</h1>
            <p>Discover and apply to internship opportunities.</p>
        </div>

        <div class="student-search-bar">
            <form method="GET" action="/Uniworksmohinhhoa/student/jobs.php" class="student-search-form">
                <span class="student-search-icon">⌕</span>
                <input 
                    type="text" 
                    name="keyword" 
                    class="student-search-input"
                    placeholder="Search by role, company, or skills..."
                    value="<?= htmlspecialchars($keyword) ?>"
                >
                <button type="submit" class="student-search-btn">Search</button>

                <?php if ($keyword !== ''): ?>
                    <a href="/Uniworksmohinhhoa/student/jobs.php" class="student-clear-btn">Clear</a>
                <?php endif; ?>
            </form>
        </div>

        <?php if (!empty($jobs)): ?>
            <div class="student-jobs-grid">
                <?php foreach ($jobs as $index => $job): ?>
                    <?php
                        $cardClass = ($index % 2 === 0) ? 'purple' : 'yellow';
                        $shortDesc = trim($job['description'] ?? '');
                        if ($shortDesc === '') {
                            $shortDesc = 'No description available.';
                        }
                        if (mb_strlen($shortDesc) > 90) {
                            $shortDesc = mb_substr($shortDesc, 0, 90) . '...';
                        }
                    ?>
                    <div class="student-job-card <?= $cardClass ?>">
                        <div>
                            <div class="student-job-top">
                                <div class="student-job-logo">
                                    <?= strtoupper(substr($job['company_name'], 0, 1)) ?>
                                </div>

                                <div class="student-job-badge">
                                    <?= $index === 0 ? 'New' : 'Open' ?>
                                </div>
                            </div>

                            <div class="student-job-content">
                                <h3><?= htmlspecialchars($job['title']) ?></h3>
                                <p class="student-job-company"><?= htmlspecialchars($job['company_name']) ?></p>

                                <div class="student-job-meta">
                                    <?php if (!empty($job['period_name'])): ?>
                                        <span><?= htmlspecialchars($job['period_name']) ?></span>
                                    <?php endif; ?>

                                    <?php if (!empty($job['deadline'])): ?>
                                        <span>Deadline: <?= htmlspecialchars($job['deadline']) ?></span>
                                    <?php endif; ?>

                                    <?php if (!empty($job['slots'])): ?>
                                        <span>Slots: <?= htmlspecialchars($job['slots']) ?></span>
                                    <?php endif; ?>
                                </div>

                                <p class="student-job-desc"><?= htmlspecialchars($shortDesc) ?></p>
                            </div>
                        </div>

                        <div class="student-job-bottom">
                            <div class="student-job-status"><?= htmlspecialchars($job['status']) ?></div>
                            <a href="/Uniworksmohinhhoa/student/job_detail.php?id=<?= $job['id'] ?>" class="student-job-action">
                                View
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="student-jobs-empty">
                <h3 style="margin-top:0; margin-bottom:10px; color:#1f2233;">No internships found</h3>
                <p style="margin:0;">
                    <?= $keyword !== '' 
                        ? 'No internship matches your search keyword.' 
                        : 'There are currently no open internships.' ?>
                </p>
            </div>
        <?php endif; ?>
    </main>
</div>

<?php include '../includes/footer.php'; ?>