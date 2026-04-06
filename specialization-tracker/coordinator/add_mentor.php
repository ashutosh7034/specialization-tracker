<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('coordinator');

$department_id = (int) ($_SESSION['department_id'] ?? 0);
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = mysqli_real_escape_string($conn, trim($_POST['name'] ?? ''));
    $email = mysqli_real_escape_string($conn, trim($_POST['email'] ?? ''));
    $password = trim($_POST['password'] ?? '');

    if ($name === '' || $email === '' || $password === '') {
        $error = 'All fields are required.';
    } else {
        $check_sql = "SELECT id FROM users WHERE email = '$email' LIMIT 1";
        $check_result = mysqli_query($conn, $check_sql);

        if ($check_result && mysqli_num_rows($check_result) > 0) {
            $error = 'Email already exists.';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $insert_user_sql = "INSERT INTO users (name, email, password, role, department_id)
                                VALUES ('$name', '$email', '$hashed', 'mentor', $department_id)";

            if (mysqli_query($conn, $insert_user_sql)) {
                $new_user_id = mysqli_insert_id($conn);
                mysqli_query($conn, "INSERT INTO mentors (user_id) VALUES ($new_user_id)");
                $message = 'Mentor created successfully in your department.';
            } else {
                $error = 'Could not create mentor user.';
            }
        }
    }
}

$list_sql = "SELECT m.id, u.name, u.email
             FROM mentors m
             INNER JOIN users u ON u.id = m.user_id
             WHERE u.department_id = $department_id
             ORDER BY m.id DESC";
$list_result = mysqli_query($conn, $list_sql);

include __DIR__ . '/../includes/header.php';
?>
<h1 class="page-title">Coordinator -> Add Mentor</h1>
<div class="form-card" style="margin-bottom:16px;">
    <?php if ($message): ?><div class="alert alert-success"><?php echo e($message); ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-error"><?php echo e($error); ?></div><?php endif; ?>

    <form method="post" data-validate="true">
        <div class="form-group"><label>Name</label><input type="text" name="name" required></div>
        <div class="form-group"><label>Email</label><input type="email" name="email" required></div>
        <div class="form-group"><label>Password</label><input type="password" name="password" required></div>
        <button type="submit">Create Mentor</button>
    </form>
</div>

<div class="table-wrap">
    <table>
        <thead><tr><th>Mentor ID</th><th>Name</th><th>Email</th></tr></thead>
        <tbody>
        <?php if ($list_result && mysqli_num_rows($list_result) > 0): while ($row = mysqli_fetch_assoc($list_result)): ?>
            <tr>
                <td><?php echo (int) $row['id']; ?></td>
                <td><?php echo e($row['name']); ?></td>
                <td><?php echo e($row['email']); ?></td>
            </tr>
        <?php endwhile; else: ?>
            <tr><td colspan="3">No mentors found in your department.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>