<?php
require_once __DIR__ . '/includes/auth.php';

if (is_logged_in()) {
    redirect_by_role($_SESSION['role']);
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = mysqli_real_escape_string($conn, trim($_POST['email'] ?? ''));
    $password = trim($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        $error = 'Email and password are required.';
    } else {
        $sql = "SELECT * FROM users WHERE email = '$email' LIMIT 1";
        $result = mysqli_query($conn, $sql);

        if ($result && mysqli_num_rows($result) === 1) {
            $user = mysqli_fetch_assoc($result);
            $is_valid = false;

            if (password_verify($password, $user['password'])) {
                $is_valid = true;
            } elseif (strlen($user['password']) === 32 && md5($password) === $user['password']) {
                // Fallback for SQL-seeded demo users that use MD5 hashes.
                $is_valid = true;
            }

            if ($is_valid) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['department_id'] = $user['department_id'];

                redirect_by_role($user['role']);
            } else {
                $error = 'Invalid credentials.';
            }
        } else {
            $error = 'Invalid credentials.';
        }
    }
}

include __DIR__ . '/includes/header.php';
?>
<h1 class="page-title">Login</h1>
<div class="form-card">
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo e($error); ?></div>
    <?php endif; ?>

    <form method="post" data-validate="true">
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" required>
        </div>

        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>

        <button type="submit">Login</button>
    </form>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>