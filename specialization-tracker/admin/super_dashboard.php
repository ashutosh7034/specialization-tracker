<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('super_admin');

$counts = get_dashboard_counts($conn);

include __DIR__ . '/../includes/header.php';
?>
<h1 class="page-title">Super Admin Dashboard</h1>
<div class="card-grid">
    <div class="card"><h3>Total Users</h3><p><?php echo $counts['users']; ?></p></div>
    <div class="card"><h3>Total Students</h3><p><?php echo $counts['students']; ?></p></div>
    <div class="card"><h3>Total Mentors</h3><p><?php echo $counts['mentors']; ?></p></div>
    <div class="card"><h3>Applications</h3><p><?php echo $counts['applications']; ?></p></div>
    <div class="card"><h3>Certificates</h3><p><?php echo $counts['certificates']; ?></p></div>
</div>

<div class="card">
    <p>Super admin can manage specialization rules dynamically from the sidebar.</p>
</div>

<div class="card" style="margin-top:16px;">
    <h3>Hierarchy Action</h3>
    <p style="margin:8px 0 12px; color:#64748b;">As Super Admin (Developer), create Admin (Dean) accounts.</p>
    <a class="btn" style="width:auto; padding:10px 14px;" href="/specialization-tracker/admin/add_admin.php">Add Admin</a>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>