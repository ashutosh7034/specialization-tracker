<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['admin', 'super_admin']);

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_POST['id'] ?? 0);
    $status = $_POST['status'] ?? '';

    if ($id > 0 && in_array($status, ['approved', 'rejected', 'offline_exam_required'], true)) {
        $sql = "UPDATE student_specializations SET status = '$status' WHERE id = $id";
        if (mysqli_query($conn, $sql)) {
            $message = 'Application status updated.';
        } else {
            $error = 'Failed to update application status.';
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
             ORDER BY ss.id DESC";
$list_result = mysqli_query($conn, $list_sql);

include __DIR__ . '/../includes/header.php';
?>
<h1 class="page-title">Manage Applications</h1>

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
            <th>Status</th>
            <th>Action</th>
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
                    <td><?php echo e(ucwords(str_replace('_', ' ', $row['status']))); ?></td>
                    <td>
                        <form method="post" style="display:flex; gap:8px;">
                            <input type="hidden" name="id" value="<?php echo (int) $row['id']; ?>">
                            <button type="submit" name="status" value="approved">Approve</button>
                            <button type="submit" name="status" value="rejected">Reject</button>
                            <button type="submit" name="status" value="offline_exam_required">Offline Exam</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="7">No applications found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>