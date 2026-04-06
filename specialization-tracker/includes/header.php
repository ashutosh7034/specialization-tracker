<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$currentRole = $_SESSION['role'] ?? '';
$currentName = $_SESSION['name'] ?? 'Guest';
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);

if (!function_exists('nav_is_active')) {
    function nav_is_active($href, $currentPath)
    {
        if ($href === '/specialization-tracker/index.php') {
            return $currentPath === '/specialization-tracker/' || $currentPath === '/specialization-tracker/index.php';
        }

        return strpos((string) $currentPath, $href) !== false;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Specialization Tracker</title>
    <link rel="stylesheet" href="/specialization-tracker/assets/css/style.css">
</head>
<body>
<div class="layout">
    <button class="menu-toggle" type="button" data-menu-toggle="true">Menu</button>

    <aside class="sidebar" data-sidebar="true">
        <h2>Tracker</h2>
        <p class="small"><?php echo htmlspecialchars($currentName); ?></p>
        <p class="badge"><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $currentRole))); ?></p>

        <nav>
            <a class="<?php echo nav_is_active('/specialization-tracker/index.php', $currentPath) ? 'active' : ''; ?>" href="/specialization-tracker/index.php">Home</a>

            <?php if ($currentRole === 'student'): ?>
                <a class="<?php echo nav_is_active('/specialization-tracker/student/dashboard.php', $currentPath) ? 'active' : ''; ?>" href="/specialization-tracker/student/dashboard.php">Student Dashboard</a>
                <a class="<?php echo nav_is_active('/specialization-tracker/student/apply.php', $currentPath) ? 'active' : ''; ?>" href="/specialization-tracker/student/apply.php">Apply</a>
                <a class="<?php echo nav_is_active('/specialization-tracker/student/certificates.php', $currentPath) ? 'active' : ''; ?>" href="/specialization-tracker/student/certificates.php">Certificates</a>
            <?php endif; ?>

            <?php if ($currentRole === 'mentor'): ?>
                <a class="<?php echo nav_is_active('/specialization-tracker/mentor/dashboard.php', $currentPath) ? 'active' : ''; ?>" href="/specialization-tracker/mentor/dashboard.php">Mentor Dashboard</a>
                <a class="<?php echo nav_is_active('/specialization-tracker/mentor/add_student.php', $currentPath) ? 'active' : ''; ?>" href="/specialization-tracker/mentor/add_student.php">Add Student</a>
                <a class="<?php echo nav_is_active('/specialization-tracker/mentor/applications.php', $currentPath) ? 'active' : ''; ?>" href="/specialization-tracker/mentor/applications.php">Student Decisions</a>
                <a class="<?php echo nav_is_active('/specialization-tracker/mentor/certificates.php', $currentPath) ? 'active' : ''; ?>" href="/specialization-tracker/mentor/certificates.php">Review Certificates</a>
            <?php endif; ?>

            <?php if ($currentRole === 'coordinator'): ?>
                <a class="<?php echo nav_is_active('/specialization-tracker/coordinator/dashboard.php', $currentPath) ? 'active' : ''; ?>" href="/specialization-tracker/coordinator/dashboard.php">Coordinator Dashboard</a>
                <a class="<?php echo nav_is_active('/specialization-tracker/coordinator/add_mentor.php', $currentPath) ? 'active' : ''; ?>" href="/specialization-tracker/coordinator/add_mentor.php">Add Mentor</a>
                <a class="<?php echo nav_is_active('/specialization-tracker/coordinator/assign_mentor.php', $currentPath) ? 'active' : ''; ?>" href="/specialization-tracker/coordinator/assign_mentor.php">Assign Mentors</a>
                <a class="<?php echo nav_is_active('/specialization-tracker/coordinator/reports.php', $currentPath) ? 'active' : ''; ?>" href="/specialization-tracker/coordinator/reports.php">Reports</a>
            <?php endif; ?>

            <?php if ($currentRole === 'admin' || $currentRole === 'super_admin'): ?>
                <a class="<?php echo nav_is_active('/specialization-tracker/admin/dashboard.php', $currentPath) ? 'active' : ''; ?>" href="/specialization-tracker/admin/dashboard.php">Admin Dashboard</a>
                <a class="<?php echo nav_is_active('/specialization-tracker/admin/add_coordinator.php', $currentPath) ? 'active' : ''; ?>" href="/specialization-tracker/admin/add_coordinator.php">Add Coordinator</a>
                <a class="<?php echo nav_is_active('/specialization-tracker/admin/departments.php', $currentPath) ? 'active' : ''; ?>" href="/specialization-tracker/admin/departments.php">Departments</a>
                <a class="<?php echo nav_is_active('/specialization-tracker/admin/users.php', $currentPath) ? 'active' : ''; ?>" href="/specialization-tracker/admin/users.php">Users</a>
                <a class="<?php echo nav_is_active('/specialization-tracker/admin/applications.php', $currentPath) ? 'active' : ''; ?>" href="/specialization-tracker/admin/applications.php">Applications</a>
                <a class="<?php echo nav_is_active('/specialization-tracker/admin/all_data.php', $currentPath) ? 'active' : ''; ?>" href="/specialization-tracker/admin/all_data.php">All Data</a>
            <?php endif; ?>

            <?php if ($currentRole === 'super_admin'): ?>
                <a class="<?php echo nav_is_active('/specialization-tracker/admin/super_dashboard.php', $currentPath) ? 'active' : ''; ?>" href="/specialization-tracker/admin/super_dashboard.php">Super Admin</a>
                <a class="<?php echo nav_is_active('/specialization-tracker/admin/add_admin.php', $currentPath) ? 'active' : ''; ?>" href="/specialization-tracker/admin/add_admin.php">Add Admin</a>
                <a class="<?php echo nav_is_active('/specialization-tracker/admin/specialization_rules.php', $currentPath) ? 'active' : ''; ?>" href="/specialization-tracker/admin/specialization_rules.php">Specialization Rules</a>
            <?php endif; ?>

            <?php if (!empty($currentRole)): ?>
                <a class="<?php echo nav_is_active('/specialization-tracker/logout.php', $currentPath) ? 'active' : ''; ?>" href="/specialization-tracker/logout.php">Logout</a>
            <?php else: ?>
                <a class="<?php echo nav_is_active('/specialization-tracker/login.php', $currentPath) ? 'active' : ''; ?>" href="/specialization-tracker/login.php">Login</a>
                <a class="<?php echo nav_is_active('/specialization-tracker/register.php', $currentPath) ? 'active' : ''; ?>" href="/specialization-tracker/register.php">Register</a>
            <?php endif; ?>
        </nav>
    </aside>

    <div class="main-area">
        <header class="topbar">
            <p>Specialization Tracker System</p>
            <div class="topbar-actions">
                <span><?php echo date('d M Y'); ?></span>
                <button type="button" class="btn-topbar" data-theme-toggle="true">Switch Theme</button>
            </div>
        </header>

        <main class="content">