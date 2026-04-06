<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('student');

$user_id = (int) $_SESSION['user_id'];
$message = '';
$error = '';

$student_sql = "SELECT * FROM students WHERE user_id = $user_id LIMIT 1";
$student_result = mysqli_query($conn, $student_sql);
$student = $student_result ? mysqli_fetch_assoc($student_result) : null;
$student_id = (int) ($student['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$student) {
        $error = 'Student record not found.';
    } elseif (!isset($_FILES['certificate']) || $_FILES['certificate']['error'] !== UPLOAD_ERR_OK) {
        $error = 'Please upload a valid file.';
    } else {
        $file = $_FILES['certificate'];
        $allowed = ['pdf', 'jpg', 'jpeg', 'png'];
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($file_ext, $allowed, true)) {
            $error = 'Only PDF/JPG/JPEG/PNG files are allowed.';
        } elseif ($file['size'] > 5 * 1024 * 1024) {
            $error = 'File size must be below 5MB.';
        } else {
            $new_name = 'cert_' . $student_id . '_' . time() . '.' . $file_ext;
            $upload_dir = __DIR__ . '/../uploads/';
            $relative_path = 'uploads/' . $new_name;
            $target = $upload_dir . $new_name;

            if (move_uploaded_file($file['tmp_name'], $target)) {
                $insert_sql = "INSERT INTO certificates (student_id, file_path, status)
                               VALUES ($student_id, '$relative_path', 'pending')";
                if (mysqli_query($conn, $insert_sql)) {
                    $message = 'Certificate uploaded successfully.';
                } else {
                    $error = 'File uploaded but DB insert failed.';
                }
            } else {
                $error = 'Failed to move uploaded file.';
            }
        }
    }
}

$list_sql = "SELECT * FROM certificates WHERE student_id = $student_id ORDER BY id DESC";
$list_result = mysqli_query($conn, $list_sql);

include __DIR__ . '/../includes/header.php';
?>
<h1 class="page-title">Certificates</h1>
<div class="form-card" style="margin-bottom:16px;">
    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo e($message); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo e($error); ?></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" data-validate="true">
        <div class="form-group">
            <label>Upload Certificate (PDF / Image)</label>
            <input type="file" name="certificate" required>
        </div>
        <button type="submit">Upload</button>
    </form>
</div>

<div class="table-wrap">
    <table>
        <thead>
        <tr>
            <th>ID</th>
            <th>File</th>
            <th>Status</th>
        </tr>
        </thead>
        <tbody>
        <?php if ($list_result && mysqli_num_rows($list_result) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($list_result)): ?>
                <tr>
                    <td><?php echo (int) $row['id']; ?></td>
                    <td><a href="/specialization-tracker/<?php echo e($row['file_path']); ?>" target="_blank">View File</a></td>
                    <td><span class="status-pill status-<?php echo e($row['status']); ?>"><?php echo e(ucfirst($row['status'])); ?></span></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="3">No certificates uploaded yet.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>