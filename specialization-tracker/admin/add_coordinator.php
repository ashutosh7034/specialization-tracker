<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['admin', 'super_admin']);

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = mysqli_real_escape_string($conn, trim($_POST['name'] ?? ''));
    $email = mysqli_real_escape_string($conn, trim($_POST['email'] ?? ''));
    $password = trim($_POST['password'] ?? '');
    $department_id = (int) ($_POST['department_id'] ?? 0);

    if ($name === '' || $email === '' || $password === '' || $department_id <= 0) {
        $error = 'All fields are required.';
    } else {
        $check_sql = "SELECT id FROM users WHERE email = '$email' LIMIT 1";
        $check_result = mysqli_query($conn, $check_sql);

        if ($check_result && mysqli_num_rows($check_result) > 0) {
            $error = 'Email already exists.';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (name, email, password, role, department_id)
                    VALUES ('$name', '$email', '$hashed', 'coordinator', $department_id)";

            if (mysqli_query($conn, $sql)) {
                $message = 'Coordinator user created successfully.';
            } else {
                $error = 'Could not create coordinator user.';
            }
        }
    }
}

$dept_result = mysqli_query($conn, "SELECT * FROM departments ORDER BY name ASC");
$list_result = mysqli_query($conn, "SELECT u.id, u.name, u.email, d.name AS department
                                    FROM users u
                                    LEFT JOIN departments d ON d.id = u.department_id
                                    WHERE u.role = 'coordinator'
                                    ORDER BY u.id DESC");

include __DIR__ . '/../includes/header.php';
?>
<h1 class="page-title">Admin (Dean) -> Add Coordinator (HOD)</h1>
<div class="form-card" style="margin-bottom:16px;">
    <?php if ($message): ?><div class="alert alert-success"><?php echo e($message); ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-error"><?php echo e($error); ?></div><?php endif; ?>

    <form method="post" data-validate="true">
        <div class="form-group"><label>Name</label><input type="text" name="name" required></div>
        <div class="form-group"><label>Email</label><input type="email" name="email" required></div>
        <div class="form-group"><label>Password</label><input type="password" name="password" required></div>
        <div class="form-group">
            <label>Department</label>
            <select name="department_id" required>
                <option value="">Select Department</option>
                <?php if ($dept_result): while ($dept = mysqli_fetch_assoc($dept_result)): ?>
                    <option value="<?php echo (int) $dept['id']; ?>"><?php echo e($dept['name']); ?></option>
                <?php endwhile; endif; ?>
            </select>
        </div>
        <button type="submit">Create Coordinator</button>
    </form>
</div>

<div class="table-wrap">
    <table>
        <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Department</th></tr></thead>
        <tbody>
        <?php if ($list_result && mysqli_num_rows($list_result) > 0): while ($row = mysqli_fetch_assoc($list_result)): ?>
            <tr>
                <td><?php echo (int) $row['id']; ?></td>
                <td><?php echo e($row['name']); ?></td>
                <td><?php echo e($row['email']); ?></td>
                <td><?php echo e($row['department'] ?? '-'); ?></td>
            </tr>
        <?php endwhile; else: ?>
            <tr><td colspan="4">No coordinator users found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>