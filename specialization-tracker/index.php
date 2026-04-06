<?php
require_once __DIR__ . '/includes/auth.php';

if (is_logged_in()) {
    redirect_by_role($_SESSION['role']);
}

include __DIR__ . '/includes/header.php';
?>
<h1 class="page-title">Specialization Tracker System</h1>
<div class="card">
    <p>This system helps students apply for Honours, Minor, and Honours with Research specialization tracks.</p>
    <br>
    <a class="btn" href="/specialization-tracker/login.php">Login</a>
    <br><br>
    <a class="btn" href="/specialization-tracker/register.php">Register as Student</a>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>