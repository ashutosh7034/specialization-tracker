<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('mentor');

$mentor_user_id = (int) $_SESSION['user_id'];

$mentor_sql = "SELECT * FROM mentors WHERE user_id = $mentor_user_id LIMIT 1";
$mentor_result = mysqli_query($conn, $mentor_sql);
$mentor = $mentor_result ? mysqli_fetch_assoc($mentor_result) : null;
$mentor_id = (int) ($mentor['id'] ?? 0);

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $application_id = (int) ($_POST['application_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if ($application_id > 0 && in_array($action, ['approved', 'rejected', 'offline_exam_required'], true)) {
        $check_sql = "SELECT ss.id
                      FROM student_specializations ss
                      INNER JOIN students s ON s.id = ss.student_id
                      INNER JOIN mentor_student_map msm ON msm.student_id = s.id
                      WHERE ss.id = $application_id AND msm.mentor_id = $mentor_id
                      LIMIT 1";
        $check_result = mysqli_query($conn, $check_sql);

        if ($check_result && mysqli_num_rows($check_result) === 1) {
            $update_sql = "UPDATE student_specializations SET status = '$action' WHERE id = $application_id";
            if (mysqli_query($conn, $update_sql)) {
                $message = 'Student application decision saved.';
            } else {
                $error = 'Could not update application status.';
            }
        } else {
            $error = 'You are not allowed to decide this application.';
        }
    } else {
        $error = 'Invalid request.';
    }
}

$list_sql = "SELECT ss.id, ss.status, u.name AS student_name, u.email, sp.name AS specialization, sp.type
             FROM student_specializations ss
             INNER JOIN students s ON s.id = ss.student_id
             INNER JOIN users u ON u.id = s.user_id
             INNER JOIN specializations sp ON sp.id = ss.specialization_id
             INNER JOIN mentor_student_map msm ON msm.student_id = s.id
             WHERE msm.mentor_id = $mentor_id
             ORDER BY ss.id DESC";
$list_result = mysqli_query($conn, $list_sql);

include __DIR__ . '/../includes/header.php';
?>
<h1 class="page-title">Student Specialization Decisions</h1>

<?php if ($message): ?>
    <div class="alert alert-success"><?php echo e($message); ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-error"><?php echo e($error); ?></div>
<?php endif; ?>

<div class="table-wrap">
    <table>
        <thead>
        <tr>
            <th>ID</th>
            <th>Student</th>
            <th>Email</th>
            <th>Specialization</th>
            <th>Type</th>
            <th>Current Status</th>
            <th>Decision</th>
        </tr>
        </thead>
        <tbody>
        <?php if ($list_result && mysqli_num_rows($list_result) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($list_result)): ?>
                <tr>
                    <td><?php echo (int) $row['id']; ?></td>
                    <td><?php echo e($row['student_name']); ?></td>
                    <td><?php echo e($row['email']); ?></td>
                    <td><?php echo e($row['specialization']); ?></td>
                    <td><?php echo e(ucwords(str_replace('_', ' ', $row['type']))); ?></td>
                    <td>
                        <span class="status-pill status-<?php echo e($row['status']); ?>">
                            <?php echo e(ucwords(str_replace('_', ' ', $row['status']))); ?>
                        </span>
                    </td>
                    <td>
                        <form method="post" style="display:flex; gap:8px;">
                            <input type="hidden" name="application_id" value="<?php echo (int) $row['id']; ?>">
                            <button type="submit" name="action" value="approved">Approve</button>
                            <button type="submit" name="action" value="rejected">Reject</button>
                            <button type="submit" name="action" value="offline_exam_required">Offline Exam</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="7">No student applications are assigned to you yet.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>