<?php
require_once '../includes/auth.php';
require_once '../config/db.php';
require_once '../includes/functions.php';
requireRole('admin');

$user = currentUser();
$flash = getFlash();

require_once '../includes/notifications.php';
include '../includes/header.php';
?>

<style>
.admin-brand{
    padding:8px 8px 28px;
    margin-bottom:16px;
}

.admin-brand h2{
    margin:0;
    font-size:24px;
    line-height:1.25;
    color:#1b2038;
    font-weight:800;
}

.admin-nav{
    display:flex;
    flex-direction:column;
    gap:12px;
    margin-top:10px;
}

.admin-create-page{
    padding:8px 6px 24px;
}

.admin-create-header{
    margin-bottom:24px;
}

.admin-create-header h1{
    margin:0 0 10px;
    font-size:46px;
    line-height:1.08;
    color:#161b34;
    font-weight:800;
}

.admin-create-header p{
    margin:0;
    font-size:18px;
    color:#707894;
    line-height:1.6;
}

.admin-create-layout{
    display:grid;
    grid-template-columns:1.25fr .9fr;
    gap:24px;
    align-items:start;
}

.admin-create-card{
    background:#fff;
    border:1px solid #ece9f7;
    border-radius:30px;
    padding:28px;
    box-shadow:0 12px 28px rgba(31,34,51,.05);
}

.admin-create-card h2{
    margin:0 0 18px;
    font-size:24px;
    color:#161b34;
    font-weight:800;
}

.admin-create-form{
    display:flex;
    flex-direction:column;
    gap:18px;
}

.admin-create-grid{
    display:grid;
    grid-template-columns:repeat(2, minmax(0, 1fr));
    gap:18px;
}

.admin-create-field{
    display:flex;
    flex-direction:column;
}

.admin-create-field.full{
    grid-column:1 / -1;
}

.admin-create-field label{
    margin-bottom:10px;
    font-size:16px;
    font-weight:700;
    color:#25283a;
}

.admin-create-field input,
.admin-create-field select{
    width:100%;
    height:54px;
    border:1.5px solid #ddd9ef;
    border-radius:18px;
    background:#fff;
    padding:0 16px;
    font-size:16px;
    color:#1f2233;
    outline:none;
    transition:.2s ease;
    box-sizing:border-box;
}

.admin-create-field input:focus,
.admin-create-field select:focus{
    border-color:#c7b7ff;
    box-shadow:0 0 0 4px rgba(207,192,255,.18);
}

.admin-create-actions{
    display:flex;
    flex-wrap:wrap;
    gap:12px;
    margin-top:4px;
}

.admin-create-btn{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    min-width:140px;
    height:48px;
    padding:0 18px;
    border:none;
    border-radius:16px;
    text-decoration:none;
    font-size:15px;
    font-weight:800;
    transition:.2s ease;
    cursor:pointer;
}

.admin-create-btn:hover{
    transform:translateY(-1px);
}

.admin-create-btn.primary{
    background:#cfc6ff;
    color:#1f2233;
}

.admin-create-btn.primary:hover{
    background:#c1b3ff;
}

.admin-create-btn.ghost{
    background:#f6f2ff;
    color:#5e6680;
}

.admin-create-btn.ghost:hover{
    background:#eee7ff;
}

.admin-create-note{
    background:#fff7d6;
    border:1px solid #efe0a0;
    border-radius:28px;
    padding:24px;
    box-shadow:0 12px 28px rgba(31,34,51,.05);
}

.admin-create-note h3{
    margin:0 0 10px;
    font-size:24px;
    color:#161b34;
    font-weight:800;
}

.admin-create-note p{
    margin:0;
    color:#6f7790;
    line-height:1.7;
    font-size:16px;
}

.admin-create-note-list{
    margin:16px 0 0;
    padding-left:18px;
    color:#5f6478;
    line-height:1.9;
}

.admin-create-mini{
    margin-top:24px;
    background:#fff;
    border:1px solid #ece9f7;
    border-radius:28px;
    padding:24px;
    box-shadow:0 12px 28px rgba(31,34,51,.05);
}

.admin-create-mini h3{
    margin:0 0 14px;
    font-size:22px;
    color:#161b34;
    font-weight:800;
}

.admin-create-mini-item{
    display:flex;
    justify-content:space-between;
    gap:16px;
    padding:14px 0;
    border-bottom:1px solid #f1edf8;
}

.admin-create-mini-item:last-child{
    border-bottom:none;
    padding-bottom:0;
}

.admin-create-mini-item span{
    color:#8a93aa;
    font-weight:600;
}

.admin-create-mini-item strong{
    color:#1f2233;
    text-align:right;
}

@media (max-width: 1000px){
    .admin-create-layout{
        grid-template-columns:1fr;
    }
}

@media (max-width: 768px){
    .admin-create-header h1{
        font-size:38px;
    }

    .admin-create-header p{
        font-size:16px;
    }

    .admin-create-grid{
        grid-template-columns:1fr;
    }

    .admin-create-btn{
        width:100%;
    }
}
</style>

<div class="admin-shell">
    <aside class="admin-sidebar">
        <div>
            <div class="admin-brand">
                <h2>Admin Panel</h2>
            </div>

            <nav class="admin-nav">
                <a href="/Uniworksmohinhhoa/admin/dashboard.php">Dashboard</a>
                <a href="/Uniworksmohinhhoa/admin/users.php" class="active">Users</a>
                <a href="/Uniworksmohinhhoa/admin/applications.php">Applications</a>
                <a href="/Uniworksmohinhhoa/admin/monitoring.php">Monitoring</a>
                <a href="/Uniworksmohinhhoa/admin/reports.php">Reports<?php if(!empty($notif['reports']) && $notif['reports']>0): ?><span class="notif-badge"><?= $notif['reports'] ?></span><?php endif; ?></a>
            </nav>
        </div>

        <div class="admin-sidebar__footer">
            <a href="/Uniworksmohinhhoa/public/logout.php">Logout</a>
        </div>
    </aside>

    <main class="admin-main">
        <div class="admin-create-page">
            <div class="admin-create-header">
                <h1>Create User</h1>
                <p>Create a new system account for admin, student, or company users.</p>
            </div>

            <?php if ($flash): ?>
                <div class="flash <?= htmlspecialchars($flash['type']) ?>" style="margin-bottom:18px;">
                    <?= htmlspecialchars($flash['message']) ?>
                </div>
            <?php endif; ?>

            <div class="admin-create-layout">
                <section class="admin-create-card">
                    <h2>User Account Form</h2>

                    <form action="/Uniworksmohinhhoa/actions/admin/create_user_action.php" method="POST" class="admin-create-form">
                        <div class="admin-create-grid">
                            <div class="admin-create-field full">
                                <label for="full_name">Full Name</label>
                                <input type="text" id="full_name" name="full_name" placeholder="Enter full name" required>
                            </div>

                            <div class="admin-create-field">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" placeholder="Enter email address" required>
                            </div>

                            <div class="admin-create-field">
                                <label for="password">Password</label>
                                <input type="password" id="password" name="password" placeholder="Enter password" required>
                            </div>

                            <div class="admin-create-field">
                                <label for="phone">Phone</label>
                                <input type="text" id="phone" name="phone" placeholder="Enter phone number">
                            </div>

                            <div class="admin-create-field">
                                <label for="role">Role</label>
                                <select id="role" name="role" required>
                                    <option value="">Select role</option>
                                    <option value="admin">Admin</option>
                                    <option value="student">Student</option>
                                    <option value="company">Company</option>
                                </select>
                            </div>
                        </div>

                        <div class="admin-create-actions">
                            <a href="/Uniworksmohinhhoa/admin/users.php" class="admin-create-btn ghost">Back</a>
                            <button type="submit" class="admin-create-btn primary">Create User</button>
                        </div>
                    </form>
                </section>

                <aside>
                    <div class="admin-create-note">
                        <h3>Quick Note</h3>
                        <p>
                            This form creates a new account in the <strong>users</strong> table. Additional profile details for students or companies can be completed later in their profile setup flow.
                        </p>

                        <ul class="admin-create-note-list">
                            <li>Use a valid and unique email address.</li>
                            <li>Choose the correct role before saving.</li>
                            <li>Student and company details are extended in separate profile tables.</li>
                        </ul>
                    </div>

                    <div class="admin-create-mini">
                        <h3>ERD Alignment</h3>

                        <div class="admin-create-mini-item">
                            <span>Main Table</span>
                            <strong>users</strong>
                        </div>

                        <div class="admin-create-mini-item">
                            <span>Supported Roles</span>
                            <strong>Admin / Student / Company</strong>
                        </div>

                        <div class="admin-create-mini-item">
                            <span>Next Step</span>
                            <strong>Complete profile after first login</strong>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </main>
</div>

<?php include '../includes/footer.php'; ?>