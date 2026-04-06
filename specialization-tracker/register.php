<?php
require_once __DIR__ . '/includes/auth.php';

if (is_logged_in()) {
    redirect_by_role($_SESSION['role']);
}

$message = '';
$error = '';

$dept_sql = "SELECT * FROM departments ORDER BY name ASC";
$dept_result = mysqli_query($conn, $dept_sql);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = mysqli_real_escape_string($conn, trim($_POST['name'] ?? ''));
    $email = mysqli_real_escape_string($conn, trim($_POST['email'] ?? ''));
    $password = trim($_POST['password'] ?? '');
    $department_id = (int) ($_POST['department_id'] ?? 0);
    $cgpa = (float) ($_POST['cgpa'] ?? 0);
    $kt_status = mysqli_real_escape_string($conn, trim($_POST['kt_status'] ?? 'yes'));
    $semester = (int) ($_POST['semester'] ?? 1);

    if ($name === '' || $email === '' || $password === '' || $department_id <= 0) {
        $error = 'Please fill all required fields.';
    } else {
        $check_sql = "SELECT id FROM users WHERE email = '$email' LIMIT 1";
        $check_result = mysqli_query($conn, $check_sql);

        if ($check_result && mysqli_num_rows($check_result) > 0) {
            $error = 'Email already exists.';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $insert_user_sql = "INSERT INTO users (name, email, password, role, department_id)
                                VALUES ('$name', '$email', '$hashed', 'student', $department_id)";

            if (mysqli_query($conn, $insert_user_sql)) {
                $user_id = mysqli_insert_id($conn);
                $insert_student_sql = "INSERT INTO students (user_id, cgpa, kt_status, semester)
                                       VALUES ($user_id, $cgpa, '$kt_status', $semester)";
                mysqli_query($conn, $insert_student_sql);

                $message = 'Registration successful. Please login.';
            } else {
                $error = 'Could not register user.';
            }
        }
    }
}

include __DIR__ . '/includes/header.php';
?>
<h1 class="page-title">Student Registration</h1>
<div class="form-card">
    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo e($message); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo e($error); ?></div>
    <?php endif; ?>

    <form method="post" data-validate="true">
        <div class="form-group">
            <label>Name</label>
            <input type="text" name="name" required>
        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" required>
        </div>

        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>

        <div class="form-group">
            <label>Department</label>
            <select name="department_id" required>
                <option value="">Select Department</option>
                <?php if ($dept_result): ?>
                    <?php while ($dept = mysqli_fetch_assoc($dept_result)): ?>
                        <option value="<?php echo (int) $dept['id']; ?>"><?php echo e($dept['name']); ?></option>
                    <?php endwhile; ?>
                <?php endif; ?>
            </select>
        </div>

        <div class="form-group">
            <label>CGPA</label>
            <input type="number" step="0.01" min="0" max="10" name="cgpa" required>
        </div>

        <div class="form-group">
            <label>KT Status</label>
            <select name="kt_status" required>
                <option value="no">No KT</option>
                <option value="yes">Has KT</option>
            </select>
        </div>

        <div class="form-group">
            <label>Semester</label>
            <input type="number" min="1" max="8" name="semester" required>
        </div>

        <button type="submit">Register</button>
    </form>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>