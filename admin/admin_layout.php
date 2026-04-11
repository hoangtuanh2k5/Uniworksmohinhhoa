<?php

function admin_nav_items(): array
{
    return [
        'dashboard' => ['label' => 'Dashboard', 'href' => 'dashboard.php', 'icon' => 'fa-table-cells-large'],
        'users' => ['label' => 'Students', 'href' => 'users.php', 'icon' => 'fa-user-graduate'],
        'applications' => ['label' => 'Applications', 'href' => 'applications.php', 'icon' => 'fa-file-lines'],
        'monitoring' => ['label' => 'Internships', 'href' => 'monitoring.php', 'icon' => 'fa-briefcase'],
        'reports' => ['label' => 'Reports', 'href' => 'reports.php', 'icon' => 'fa-chart-column'],
        'create_user' => ['label' => 'Settings', 'href' => 'create_user.php', 'icon' => 'fa-gear'],
    ];
}

function admin_status_class(string $status): string
{
    $status = strtolower(trim($status));
    $allowed = ['pending', 'approved', 'reviewed', 'rejected', 'active'];

    return in_array($status, $allowed, true) ? $status : 'pending';
}

function admin_initials(string $name): string
{
    $name = trim($name);
    if ($name === '') {
        return 'AD';
    }

    $parts = preg_split('/\s+/', $name) ?: [];
    $initials = '';

    foreach (array_slice($parts, 0, 2) as $part) {
        $initials .= strtoupper(substr($part, 0, 1));
    }

    return $initials !== '' ? $initials : 'AD';
}

function admin_render_start(
    string $title,
    string $activeNav,
    string $pageTitle,
    string $pageSubtitle,
    string $actionsHtml = '',
    string $searchPlaceholder = 'Search data, students, or companies...'
): void {
    $navItems = admin_nav_items();
    ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body class="admin-body">
<div class="admin-shell">
    <aside class="admin-sidebar">
        <div class="admin-brand">
            <span class="admin-brand__icon"><i class="fas fa-table-cells-large"></i></span>
            <div>
                <div class="admin-brand__title">Admin Pro</div>
                <span class="admin-brand__subtitle">Placement Hub</span>
            </div>
        </div>

        <nav class="admin-nav">
            <?php foreach ($navItems as $key => $item): ?>
                <a href="<?php echo htmlspecialchars($item['href']); ?>"
                   class="admin-nav__link<?php echo $key === $activeNav ? ' is-active' : ''; ?>">
                    <i class="fas <?php echo htmlspecialchars($item['icon']); ?>"></i>
                    <span><?php echo htmlspecialchars($item['label']); ?></span>
                </a>
            <?php endforeach; ?>
        </nav>

        <div class="admin-sidebar__footer">
            <a href="../public/logout.php" class="admin-nav__link">
                <i class="fas fa-arrow-right-from-bracket"></i>
                <span>Sign Out</span>
            </a>
        </div>
    </aside>

    <main class="admin-main">
        <div class="admin-topbar">
            <label class="admin-search">
                <i class="fas fa-magnifying-glass"></i>
                <input type="text" placeholder="<?php echo htmlspecialchars($searchPlaceholder); ?>">
            </label>

            <div class="admin-topbar__actions">
                <span class="admin-icon-btn"><i class="far fa-bell"></i></span>
                <span class="admin-icon-btn"><i class="fas fa-gear"></i></span>
            </div>
        </div>

        <div class="admin-page-head">
            <div>
                <h1><?php echo htmlspecialchars($pageTitle); ?></h1>
                <p><?php echo htmlspecialchars($pageSubtitle); ?></p>
            </div>

            <?php if ($actionsHtml !== ''): ?>
                <div class="admin-head-actions"><?php echo $actionsHtml; ?></div>
            <?php endif; ?>
        </div>
<?php
}

function admin_render_end(): void
{
    ?>
    </main>
</div>
</body>
</html>
<?php
}
