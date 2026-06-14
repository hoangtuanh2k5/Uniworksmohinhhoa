<?php
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/admin_layout.php';

<<<<<<< Updated upstream
$search = trim($_GET['search'] ?? '');
$roleFilter = trim($_GET['role'] ?? '');
$allowedRoles = ['student', 'company', 'admin'];

$sql = 'SELECT id, full_name, email, role, phone, created_at FROM users WHERE 1=1';
$types = '';
$params = [];
=======
$flash = getFlash();
$tab   = $_GET['tab'] ?? 'student';
if (!in_array($tab, ['student', 'company'])) $tab = 'student';

// Lấy students
$students = $pdo->query("
    SELECT u.id, u.full_name, u.email, u.phone, u.created_at,
           s.id AS student_id, s.student_code, m.name AS major_name, s.class_name, s.gpa
    FROM users u
    INNER JOIN students s ON s.user_id = u.id
    LEFT JOIN majors m ON m.id = s.major_id
    ORDER BY u.id DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Lấy companies
$companies = $pdo->query("
    SELECT u.id, u.full_name, u.email, u.phone, u.created_at,
           c.id AS company_id, c.company_name, c.tax_code, c.industry_type, c.status AS company_status
    FROM users u
    INNER JOIN companies c ON c.user_id = u.id
    ORDER BY u.id DESC
")->fetchAll(PDO::FETCH_ASSOC);
>>>>>>> Stashed changes

if ($search !== '') {
    $sql .= ' AND (full_name LIKE ? OR email LIKE ?)';
    $like = '%' . $search . '%';
    $types .= 'ss';
    $params[] = $like;
    $params[] = $like;
}

if (in_array($roleFilter, $allowedRoles, true)) {
    $sql .= ' AND role = ?';
    $types .= 's';
    $params[] = $roleFilter;
}

$sql .= ' ORDER BY created_at DESC';
$stmt = $conn->prepare($sql);
if ($types !== '') {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$totalUsers = (int) ($conn->query("SELECT COUNT(*) AS count FROM users")->fetch_assoc()['count'] ?? 0);
$studentUsers = (int) ($conn->query("SELECT COUNT(*) AS count FROM users WHERE role = 'student'")->fetch_assoc()['count'] ?? 0);
$companyUsers = (int) ($conn->query("SELECT COUNT(*) AS count FROM users WHERE role = 'company'")->fetch_assoc()['count'] ?? 0);
$adminUsers = (int) ($conn->query("SELECT COUNT(*) AS count FROM users WHERE role = 'admin'")->fetch_assoc()['count'] ?? 0);

ob_start();
?>
<a href="create_user.php" class="admin-button"><i class="fas fa-plus"></i> Add User</a>
<?php
$actionsHtml = ob_get_clean();

admin_render_start(
    'Users | Placement Hub',
    'users',
    'Students & Users',
    'Manage platform accounts for students, companies and admin staff',
    $actionsHtml
);
?>

<?php if (($_GET['msg'] ?? '') === 'deleted'): ?>
    <div class="admin-alert admin-alert--success">User deleted successfully.</div>
<?php elseif (($_GET['msg'] ?? '') === 'updated'): ?>
    <div class="admin-alert admin-alert--success">User updated successfully.</div>
<?php elseif (($_GET['msg'] ?? '') === 'created'): ?>
    <div class="admin-alert admin-alert--success">User created successfully.</div>
<?php elseif (($_GET['error'] ?? '') === 'invalid_id'): ?>
    <div class="admin-alert admin-alert--error">Invalid user selected.</div>
<?php elseif (($_GET['error'] ?? '') === 'delete_failed'): ?>
    <div class="admin-alert admin-alert--error">Could not delete user.</div>
<?php endif; ?>

<section class="admin-grid admin-grid--stats">
    <article class="admin-kpi admin-kpi--yellow">
        <div class="admin-kpi__top">
            <span class="admin-kpi__icon"><i class="fas fa-users"></i></span>
            <span class="admin-kpi__trend">Total</span>
        </div>
        <div>
<<<<<<< Updated upstream
            <div class="admin-kpi__label">All Users</div>
            <div class="admin-kpi__value"><?php echo number_format($totalUsers); ?></div>
        </div>
    </article>

    <article class="admin-kpi admin-kpi--purple">
        <div class="admin-kpi__top">
            <span class="admin-kpi__icon"><i class="fas fa-user-graduate"></i></span>
            <span class="admin-kpi__trend"><?php echo number_format($studentUsers); ?></span>
        </div>
        <div>
            <div class="admin-kpi__label">Students</div>
            <div class="admin-kpi__value"><?php echo number_format($studentUsers); ?></div>
=======
            <div class="admin-brand"><h2>Admin Panel</h2></div>
            <nav class="admin-nav">
                <a href="/Uniworksmohinhhoa/admin/dashboard.php">Dashboard</a>
                <a href="/Uniworksmohinhhoa/admin/users.php" class="active">Users</a>
                <a href="/Uniworksmohinhhoa/admin/company_approvals.php">Companies</a>
                <a href="/Uniworksmohinhhoa/admin/applications.php">Applications</a>
                <a href="/Uniworksmohinhhoa/admin/monitoring.php">Monitoring</a>
                <a href="/Uniworksmohinhhoa/admin/reports.php">Reports</a>
            </nav>
        </div>
        <div class="admin-sidebar__footer">
            <a href="/Uniworksmohinhhoa/public/logout.php">Logout</a>
        </div>
    </aside>

    <main class="admin-main">
        <div class="admin-topbar">
            <div>
                <h1>Manage Users</h1>
                <p>View and manage student and company accounts.</p>
            </div>
            <a href="/Uniworksmohinhhoa/admin/create_user.php" class="admin-btn">+ Create User</a>
>>>>>>> Stashed changes
        </div>
    </article>

    <article class="admin-kpi admin-kpi--yellow">
        <div class="admin-kpi__top">
            <span class="admin-kpi__icon"><i class="fas fa-building"></i></span>
            <span class="admin-kpi__trend"><?php echo number_format($companyUsers); ?></span>
        </div>
        <div>
            <div class="admin-kpi__label">Companies</div>
            <div class="admin-kpi__value"><?php echo number_format($companyUsers); ?></div>
        </div>
    </article>

<<<<<<< Updated upstream
    <article class="admin-kpi admin-kpi--purple">
        <div class="admin-kpi__top">
            <span class="admin-kpi__icon"><i class="fas fa-user-shield"></i></span>
            <span class="admin-kpi__trend"><?php echo number_format($adminUsers); ?></span>
        </div>
        <div>
            <div class="admin-kpi__label">Admins</div>
            <div class="admin-kpi__value"><?php echo number_format($adminUsers); ?></div>
        </div>
    </article>
</section>

<section class="admin-card" style="margin-top: 22px;">
    <div class="admin-card__head">
        <div>
            <h3>User Directory</h3>
            <span class="admin-card__eyebrow">Filter accounts by role or search by name and email</span>
        </div>
    </div>

    <form method="GET" class="admin-toolbar">
        <input type="text" name="search" class="admin-input" placeholder="Search name or email..." value="<?php echo htmlspecialchars($search); ?>">
        <select name="role" class="admin-filter" onchange="this.form.submit()">
            <option value="">All roles</option>
            <option value="student" <?php echo $roleFilter === 'student' ? 'selected' : ''; ?>>Student</option>
            <option value="company" <?php echo $roleFilter === 'company' ? 'selected' : ''; ?>>Company</option>
            <option value="admin" <?php echo $roleFilter === 'admin' ? 'selected' : ''; ?>>Admin</option>
        </select>
        <button type="submit" class="admin-button--soft">Apply</button>
    </form>

    <table class="admin-table">
        <thead>
            <tr>
                <th>User</th>
                <th>Email</th>
                <th>Role</th>
                <th>Phone</th>
                <th>Joined</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result instanceof mysqli_result && $result->num_rows > 0): ?>
                <?php while ($user = $result->fetch_assoc()): ?>
                    <?php $roleClass = $user['role'] === 'admin' ? 'approved' : ($user['role'] === 'company' ? 'reviewed' : 'pending'); ?>
                    <tr>
                        <td>
                            <div class="admin-person">
                                <div class="admin-avatar"><?php echo htmlspecialchars(admin_initials($user['full_name'] ?? '')); ?></div>
                                <div class="admin-person__meta">
                                    <strong><?php echo htmlspecialchars($user['full_name']); ?></strong>
                                    <span>ID #<?php echo (int) $user['id']; ?></span>
                                </div>
                            </div>
                        </td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td>
                            <span class="admin-pill admin-pill--<?php echo htmlspecialchars($roleClass); ?>">
                                <?php echo htmlspecialchars(ucfirst($user['role'])); ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($user['phone'] ?: 'N/A'); ?></td>
                        <td><?php echo !empty($user['created_at']) ? htmlspecialchars(date('d/m/Y', strtotime($user['created_at']))) : 'N/A'; ?></td>
                        <td>
                            <div class="admin-actions">
                                <a href="edit_user.php?id=<?php echo (int) $user['id']; ?>" class="admin-action-link">
                                    <i class="fas fa-pen"></i> Edit
                                </a>
                                <a href="../actions/admin/delete_user_action.php?id=<?php echo (int) $user['id']; ?>" class="admin-action-link admin-action-link--danger" onclick="return confirm('Delete this user? This may remove related records.');">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="admin-empty">No users found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</section>

<?php
admin_render_end();
$stmt->close();
$conn->close();
=======
        <!-- Tabs -->
        <div style="display:flex;gap:0;margin-bottom:22px;border-bottom:2px solid #eceef6;">
            <a href="?tab=student"
               style="padding:12px 28px;font-size:15px;font-weight:800;text-decoration:none;border-bottom:3px solid <?= $tab==='student' ? '#7c6fcf' : 'transparent' ?>;color:<?= $tab==='student' ? '#17172b' : '#7a8096' ?>;margin-bottom:-2px;">
                🎓 Students
                <span style="display:inline-flex;align-items:center;justify-content:center;min-width:22px;height:22px;border-radius:999px;background:<?= $tab==='student' ? '#cfc6f6' : '#f0eeff' ?>;color:#17172b;font-size:12px;font-weight:800;margin-left:8px;padding:0 6px;">
                    <?= count($students) ?>
                </span>
            </a>
            <a href="?tab=company"
               style="padding:12px 28px;font-size:15px;font-weight:800;text-decoration:none;border-bottom:3px solid <?= $tab==='company' ? '#7c6fcf' : 'transparent' ?>;color:<?= $tab==='company' ? '#17172b' : '#7a8096' ?>;margin-bottom:-2px;">
                🏢 Companies
                <span style="display:inline-flex;align-items:center;justify-content:center;min-width:22px;height:22px;border-radius:999px;background:<?= $tab==='company' ? '#cfc6f6' : '#f0eeff' ?>;color:#17172b;font-size:12px;font-weight:800;margin-left:8px;padding:0 6px;">
                    <?= count($companies) ?>
                </span>
            </a>
        </div>

        <!-- STUDENT TAB -->
        <?php if ($tab === 'student'): ?>
        <div class="admin-panel" style="padding:0;overflow:hidden;">
            <div style="padding:20px 26px 14px;border-bottom:1px solid #eceef6;">
                <h2 style="margin:0;font-size:18px;font-weight:800;color:#161b34;">Student Accounts</h2>
            </div>
            <?php if (empty($students)): ?>
                <div style="padding:40px;text-align:center;color:#7a8198;">No students found.</div>
            <?php else: ?>
            <div style="overflow-x:auto;">
                <table style="width:100%;border-collapse:collapse;min-width:900px;">
                    <thead>
                        <tr>
                            <?php foreach (['User ID', 'Full Name', 'Email', 'Student Code', 'Major', 'Class', 'GPA', 'Phone', 'Joined', 'Action'] as $h): ?>
                                <th style="text-align:left;padding:13px 18px;background:#faf8ff;color:#74809b;font-size:13px;font-weight:800;border-bottom:1px solid #eceef6;white-space:nowrap;"><?= $h ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $u): ?>
                        <tr>
                            <td style="padding:14px 18px;border-bottom:1px solid #f1edf8;vertical-align:middle;">
                                <span style="font-size:12px;font-weight:700;color:#7a8096;background:#f4f1ff;padding:4px 9px;border-radius:999px;">USR-<?= $u['id'] ?></span>
                            </td>
                            <td style="padding:14px 18px;border-bottom:1px solid #f1edf8;vertical-align:middle;">
                                <div style="font-weight:700;color:#17172b;"><?= htmlspecialchars($u['full_name']) ?></div>
                            </td>
                            <td style="padding:14px 18px;border-bottom:1px solid #f1edf8;vertical-align:middle;font-size:13px;color:#4d566f;"><?= htmlspecialchars($u['email']) ?></td>
                            <td style="padding:14px 18px;border-bottom:1px solid #f1edf8;vertical-align:middle;">
                                <span style="font-size:12px;font-weight:700;background:#ece8fb;color:#5b43a7;padding:4px 9px;border-radius:999px;"><?= htmlspecialchars($u['student_code'] ?: '—') ?></span>
                            </td>
                            <td style="padding:14px 18px;border-bottom:1px solid #f1edf8;vertical-align:middle;font-size:13px;color:#4d566f;"><?= htmlspecialchars($u['major_name'] ?: '—') ?></td>
                            <td style="padding:14px 18px;border-bottom:1px solid #f1edf8;vertical-align:middle;font-size:13px;color:#4d566f;"><?= htmlspecialchars($u['class_name'] ?: '—') ?></td>
                            <td style="padding:14px 18px;border-bottom:1px solid #f1edf8;vertical-align:middle;font-size:13px;font-weight:700;color:#17172b;"><?= $u['gpa'] !== null ? number_format((float)$u['gpa'], 2) : '—' ?></td>
                            <td style="padding:14px 18px;border-bottom:1px solid #f1edf8;vertical-align:middle;font-size:13px;color:#4d566f;"><?= htmlspecialchars($u['phone'] ?: '—') ?></td>
                            <td style="padding:14px 18px;border-bottom:1px solid #f1edf8;vertical-align:middle;font-size:12px;color:#7a8096;white-space:nowrap;"><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
                            <td style="padding:14px 18px;border-bottom:1px solid #f1edf8;vertical-align:middle;">
                                <div style="display:flex;gap:8px;">
                                    <a href="/Uniworksmohinhhoa/admin/student_detail.php?id=<?= $u['student_id'] ?>"
                                       style="padding:7px 13px;border-radius:10px;background:#f0d86b;color:#17172b;font-size:13px;font-weight:700;text-decoration:none;">View</a>
                                    <a href="/Uniworksmohinhhoa/admin/edit_user.php?id=<?= $u['id'] ?>"
                                       style="padding:7px 13px;border-radius:10px;background:#cfc6f6;color:#17172b;font-size:13px;font-weight:700;text-decoration:none;">Edit</a>
                                    <a href="/Uniworksmohinhhoa/actions/admin/delete_user_action.php?id=<?= $u['id'] ?>"
                                       onclick="return confirm('Delete this student?')"
                                       style="padding:7px 13px;border-radius:10px;background:#ffe1e1;color:#ad3e3e;font-size:13px;font-weight:700;text-decoration:none;">Delete</a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- COMPANY TAB -->
        <?php if ($tab === 'company'): ?>
        <div class="admin-panel" style="padding:0;overflow:hidden;">
            <div style="padding:20px 26px 14px;border-bottom:1px solid #eceef6;">
                <h2 style="margin:0;font-size:18px;font-weight:800;color:#161b34;">Company Accounts</h2>
            </div>
            <?php if (empty($companies)): ?>
                <div style="padding:40px;text-align:center;color:#7a8198;">No companies found.</div>
            <?php else: ?>
            <div style="overflow-x:auto;">
                <table style="width:100%;border-collapse:collapse;min-width:900px;">
                    <thead>
                        <tr>
                            <?php foreach (['User ID', 'Company ID', 'Company Name', 'Representative', 'Email', 'Tax Code', 'Industry', 'Phone', 'Status', 'Joined', 'Action'] as $h): ?>
                                <th style="text-align:left;padding:13px 18px;background:#faf8ff;color:#74809b;font-size:13px;font-weight:800;border-bottom:1px solid #eceef6;white-space:nowrap;"><?= $h ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($companies as $u): ?>
                        <tr>
                            <td style="padding:14px 18px;border-bottom:1px solid #f1edf8;vertical-align:middle;">
                                <span style="font-size:12px;font-weight:700;color:#7a8096;background:#f4f1ff;padding:4px 9px;border-radius:999px;">USR-<?= $u['id'] ?></span>
                            </td>
                            <td style="padding:14px 18px;border-bottom:1px solid #f1edf8;vertical-align:middle;">
                                <span style="font-size:12px;font-weight:700;color:#7a8096;background:#fff8e1;padding:4px 9px;border-radius:999px;">CO-<?= $u['company_id'] ?></span>
                            </td>
                            <td style="padding:14px 18px;border-bottom:1px solid #f1edf8;vertical-align:middle;">
                                <div style="font-weight:800;color:#17172b;"><?= htmlspecialchars($u['company_name']) ?></div>
                            </td>
                            <td style="padding:14px 18px;border-bottom:1px solid #f1edf8;vertical-align:middle;font-weight:600;color:#1f2233;"><?= htmlspecialchars($u['full_name']) ?></td>
                            <td style="padding:14px 18px;border-bottom:1px solid #f1edf8;vertical-align:middle;font-size:13px;color:#4d566f;"><?= htmlspecialchars($u['email']) ?></td>
                            <td style="padding:14px 18px;border-bottom:1px solid #f1edf8;vertical-align:middle;font-size:13px;color:#4d566f;"><?= htmlspecialchars($u['tax_code']) ?></td>
                            <td style="padding:14px 18px;border-bottom:1px solid #f1edf8;vertical-align:middle;font-size:13px;color:#4d566f;"><?= htmlspecialchars($u['industry_type'] ?: '—') ?></td>
                            <td style="padding:14px 18px;border-bottom:1px solid #f1edf8;vertical-align:middle;font-size:13px;color:#4d566f;"><?= htmlspecialchars($u['phone'] ?: '—') ?></td>
                            <td style="padding:14px 18px;border-bottom:1px solid #f1edf8;vertical-align:middle;">
                                <?php
                                $cs = $u['company_status'];
                                $csBadge = match($cs) {
                                    'approved'  => 'background:#d8f2df;color:#187a3d;',
                                    'rejected'  => 'background:#ffe1e1;color:#ad3e3e;',
                                    'suspended' => 'background:#fff3e0;color:#b45309;',
                                    default     => 'background:#f6e8a6;color:#a36a00;',
                                };
                                ?>
                                <span style="display:inline-flex;align-items:center;padding:5px 11px;border-radius:999px;font-size:12px;font-weight:800;<?= $csBadge ?>">
                                    <?= ucfirst($cs) ?>
                                </span>
                            </td>
                            <td style="padding:14px 18px;border-bottom:1px solid #f1edf8;vertical-align:middle;font-size:12px;color:#7a8096;white-space:nowrap;"><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
                            <td style="padding:14px 18px;border-bottom:1px solid #f1edf8;vertical-align:middle;">
                                <div style="display:flex;gap:8px;">
                                    <a href="/Uniworksmohinhhoa/admin/company_detail.php?id=<?= $u['company_id'] ?>"
                                       style="padding:7px 13px;border-radius:10px;background:#f0d86b;color:#17172b;font-size:13px;font-weight:700;text-decoration:none;">View</a>
                                    <a href="/Uniworksmohinhhoa/admin/edit_user.php?id=<?= $u['id'] ?>"
                                       style="padding:7px 13px;border-radius:10px;background:#cfc6f6;color:#17172b;font-size:13px;font-weight:700;text-decoration:none;">Edit</a>
                                    <a href="/Uniworksmohinhhoa/actions/admin/delete_user_action.php?id=<?= $u['id'] ?>"
                                       onclick="return confirm('Delete this company?')"
                                       style="padding:7px 13px;border-radius:10px;background:#ffe1e1;color:#ad3e3e;font-size:13px;font-weight:700;text-decoration:none;">Delete</a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

    </main>
</div>

<?php include '../includes/footer.php'; ?>
>>>>>>> Stashed changes
