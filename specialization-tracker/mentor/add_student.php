<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('mentor');

$mentor_user_id = (int) $_SESSION['user_id'];
$department_id = (int) ($_SESSION['department_id'] ?? 0);

$mentor_sql = "SELECT * FROM mentors WHERE user_id = $mentor_user_id LIMIT 1";
$mentor_result = mysqli_query($conn, $mentor_sql);
$mentor = $mentor_result ? mysqli_fetch_assoc($mentor_result) : null;
$mentor_id = (int) ($mentor['id'] ?? 0);

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = mysqli_real_escape_string($conn, trim($_POST['name'] ?? ''));
    $email = mysqli_real_escape_string($conn, trim($_POST['email'] ?? ''));
    $password = trim($_POST['password'] ?? '');
    $cgpa = (float) ($_POST['cgpa'] ?? 0);
    $kt_status = mysqli_real_escape_string($conn, trim($_POST['kt_status'] ?? 'no'));
    $semester = (int) ($_POST['semester'] ?? 1);

    if ($name === '' || $email === '' || $password === '') {
        $error = 'Name, email and password are required.';
    } elseif ($mentor_id <= 0) {
        $error = 'Mentor profile not found.';
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
                $new_user_id = mysqli_insert_id($conn);

                $insert_student_sql = "INSERT INTO students (user_id, cgpa, kt_status, semester)
                                       VALUES ($new_user_id, $cgpa, '$kt_status', $semester)";
                if (mysqli_query($conn, $insert_student_sql)) {
                    $new_student_id = mysqli_insert_id($conn);

                    mysqli_query($conn, "INSERT INTO mentor_student_map (mentor_id, student_id)
                                         VALUES ($mentor_id, $new_student_id)");
                    $message = 'Student created and assigned to you successfully.';
                } else {
                    $error = 'Student profile creation failed.';
                }
            } else {
                $error = 'Could not create student user.';
            }
        }
    }
}

$list_sql = "SELECT s.id, u.name, u.email, s.cgpa, s.kt_status, s.semester
             FROM mentor_student_map msm
             INNER JOIN students s ON s.id = msm.student_id
             INNER JOIN users u ON u.id = s.user_id
             WHERE msm.mentor_id = $mentor_id
             ORDER BY s.id DESC";
$list_result = mysqli_query($conn, $list_sql);

include __DIR__ . '/../includes/header.php';
?>
<h1 class="page-title">Mentor -> Add Student</h1>
<div class="form-card" style="margin-bottom:16px;">
    <?php if ($message): ?><div class="alert alert-success"><?php echo e($message); ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-error"><?php echo e($error); ?></div><?php endif; ?>

    <form method="post" data-validate="true">
        <div class="form-group"><label>Name</label><input type="text" name="name" required></div>
        <div class="form-group"><label>Email</label><input type="email" name="email" required></div>
        <div class="form-group"><label>Password</label><input type="password" name="password" required></div>
        <div class="form-group"><label>CGPA</label><input type="number" min="0" max="10" step="0.01" name="cgpa" required></div>
        <div class="form-group">
            <label>KT Status</label>
            <select name="kt_status" required>
                <option value="no">No KT</option>
                <option value="yes">Has KT</option>
            </select>
        </div>
        <div class="form-group"><label>Semester</label><input type="number" min="1" max="8" name="semester" required></div>
        <button type="submit">Create Student</button>
    </form>
</div>

<div class="table-wrap">
    <table>
        <thead><tr><th>Student ID</th><th>Name</th><th>Email</th><th>CGPA</th><th>KT</th><th>Semester</th></tr></thead>
        <tbody>
        <?php if ($list_result && mysqli_num_rows($list_result) > 0): while ($row = mysqli_fetch_assoc($list_result)): ?>
            <tr>
                <td><?php echo (int) $row['id']; ?></td>
                <td><?php echo e($row['name']); ?></td>
                <td><?php echo e($row['email']); ?></td>
                <td><?php echo e($row['cgpa']); ?></td>
                <td><?php echo e(strtoupper($row['kt_status'])); ?></td>
                <td><?php echo e($row['semester']); ?></td>
            </tr>
        <?php endwhile; else: ?>
            <tr><td colspan="6">No assigned students found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>