<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('coordinator');

$department_id = (int) ($_SESSION['department_id'] ?? 0);
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mentor_id = (int) ($_POST['mentor_id'] ?? 0);
    $student_id = (int) ($_POST['student_id'] ?? 0);

    if ($mentor_id > 0 && $student_id > 0) {
        $check_sql = "SELECT id FROM mentor_student_map
                      WHERE mentor_id = $mentor_id AND student_id = $student_id LIMIT 1";
        $check_result = mysqli_query($conn, $check_sql);

        if ($check_result && mysqli_num_rows($check_result) > 0) {
            $error = 'This student is already assigned to the selected mentor.';
        } else {
            $insert_sql = "INSERT INTO mentor_student_map (mentor_id, student_id) VALUES ($mentor_id, $student_id)";
            if (mysqli_query($conn, $insert_sql)) {
                $message = 'Mentor assigned successfully.';
            } else {
                $error = 'Failed to assign mentor.';
            }
        }
    } else {
        $error = 'Please select both mentor and student.';
    }
}

$mentor_sql = "SELECT m.id, u.name
              FROM mentors m
              INNER JOIN users u ON u.id = m.user_id
              WHERE u.department_id = $department_id
              ORDER BY u.name ASC";
$mentor_result = mysqli_query($conn, $mentor_sql);

$student_sql = "SELECT s.id, u.name
               FROM students s
               INNER JOIN users u ON u.id = s.user_id
               WHERE u.department_id = $department_id
               ORDER BY u.name ASC";
$student_result = mysqli_query($conn, $student_sql);

$list_sql = "SELECT msm.id, um.name AS mentor_name, us.name AS student_name
            FROM mentor_student_map msm
            INNER JOIN mentors m ON m.id = msm.mentor_id
            INNER JOIN users um ON um.id = m.user_id
            INNER JOIN students s ON s.id = msm.student_id
            INNER JOIN users us ON us.id = s.user_id
            WHERE um.department_id = $department_id
            ORDER BY msm.id DESC";
$list_result = mysqli_query($conn, $list_sql);

include __DIR__ . '/../includes/header.php';
?>
<h1 class="page-title">Assign Mentor</h1>
<div class="form-card" style="margin-bottom:16px;">
    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo e($message); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo e($error); ?></div>
    <?php endif; ?>

    <form method="post" data-validate="true">
        <div class="form-group">
            <label>Mentor</label>
            <select name="mentor_id" required>
                <option value="">Select Mentor</option>
                <?php if ($mentor_result): ?>
                    <?php while ($mentor = mysqli_fetch_assoc($mentor_result)): ?>
                        <option value="<?php echo (int) $mentor['id']; ?>"><?php echo e($mentor['name']); ?></option>
                    <?php endwhile; ?>
                <?php endif; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Student</label>
            <select name="student_id" required>
                <option value="">Select Student</option>
                <?php if ($student_result): ?>
                    <?php while ($student = mysqli_fetch_assoc($student_result)): ?>
                        <option value="<?php echo (int) $student['id']; ?>"><?php echo e($student['name']); ?></option>
                    <?php endwhile; ?>
                <?php endif; ?>
            </select>
        </div>

        <button type="submit">Assign</button>
    </form>
</div>

<div class="table-wrap">
    <table>
        <thead>
        <tr>
            <th>ID</th>
            <th>Mentor</th>
            <th>Student</th>
        </tr>
        </thead>
        <tbody>
        <?php if ($list_result && mysqli_num_rows($list_result) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($list_result)): ?>
                <tr>
                    <td><?php echo (int) $row['id']; ?></td>
                    <td><?php echo e($row['mentor_name']); ?></td>
                    <td><?php echo e($row['student_name']); ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="3">No assignments found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>