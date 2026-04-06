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
    $certificate_id = (int) ($_POST['certificate_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if ($certificate_id > 0 && in_array($action, ['approved', 'rejected'], true)) {
        $check_sql = "SELECT c.id
                      FROM certificates c
                      INNER JOIN students s ON s.id = c.student_id
                      INNER JOIN mentor_student_map msm ON msm.student_id = s.id
                      WHERE c.id = $certificate_id AND msm.mentor_id = $mentor_id
                      LIMIT 1";
        $check_result = mysqli_query($conn, $check_sql);

        if ($check_result && mysqli_num_rows($check_result) === 1) {
            $update_sql = "UPDATE certificates SET status = '$action' WHERE id = $certificate_id";
            if (mysqli_query($conn, $update_sql)) {
                $message = 'Certificate status updated.';
            } else {
                $error = 'Failed to update certificate status.';
            }
        } else {
            $error = 'You are not allowed to update this certificate.';
        }
    } else {
        $error = 'Invalid request.';
    }
}

$list_sql = "SELECT c.id, c.file_path, c.status, u.name AS student_name
            FROM certificates c
            INNER JOIN students s ON s.id = c.student_id
            INNER JOIN users u ON u.id = s.user_id
            INNER JOIN mentor_student_map msm ON msm.student_id = s.id
            WHERE msm.mentor_id = $mentor_id
            ORDER BY c.id DESC";
$list_result = mysqli_query($conn, $list_sql);

include __DIR__ . '/../includes/header.php';
?>
<h1 class="page-title">Certificate Review</h1>

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
            <th>File</th>
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
                    <td><a href="/specialization-tracker/<?php echo e($row['file_path']); ?>" target="_blank">View</a></td>
                    <td><span class="status-pill status-<?php echo e($row['status']); ?>"><?php echo e(ucfirst($row['status'])); ?></span></td>
                    <td>
                        <form method="post" style="display:flex; gap:8px;">
                            <input type="hidden" name="certificate_id" value="<?php echo (int) $row['id']; ?>">
                            <button type="submit" name="action" value="approved">Approve</button>
                            <button type="submit" name="action" value="rejected">Reject</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="5">No certificates to review.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>