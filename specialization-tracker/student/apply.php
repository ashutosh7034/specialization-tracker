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

$selected_types = [];
$selected_sql = "SELECT sp.type
                 FROM student_specializations ss
                 INNER JOIN specializations sp ON sp.id = ss.specialization_id
                 WHERE ss.student_id = $student_id";
$selected_result = mysqli_query($conn, $selected_sql);
if ($selected_result) {
    while ($row = mysqli_fetch_assoc($selected_result)) {
        $selected_types[] = $row['type'];
    }
}

$eligibility = get_allowed_specializations($student ?? [], $selected_types);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $specialization_id = (int) ($_POST['specialization_id'] ?? 0);

    $sp_sql = "SELECT * FROM specializations WHERE id = $specialization_id LIMIT 1";
    $sp_result = mysqli_query($conn, $sp_sql);
    $specialization = $sp_result ? mysqli_fetch_assoc($sp_result) : null;

    if (!$student || !$specialization) {
        $error = 'Invalid request.';
    } else {
        $type = $specialization['type'];
        $check_sql = "SELECT id FROM student_specializations
                      WHERE student_id = $student_id AND specialization_id = $specialization_id
                      LIMIT 1";
        $check_result = mysqli_query($conn, $check_sql);

        if ($check_result && mysqli_num_rows($check_result) > 0) {
            $error = 'You already applied for this specialization.';
        } elseif (!isset($eligibility[$type]) || !$eligibility[$type]['eligible']) {
            if ($type === 'honours') {
                $insert_sql = "INSERT INTO student_specializations (student_id, specialization_id, status)
                               VALUES ($student_id, $specialization_id, 'offline_exam_required')";
                if (mysqli_query($conn, $insert_sql)) {
                    $message = 'Not currently eligible for Honours. Status marked as Offline Exam Required.';
                } else {
                    $error = 'Could not mark offline exam required status.';
                }
            } else {
                $error = 'You are not eligible for selected specialization. ' . ($eligibility[$type]['message'] ?? '');
            }
        } else {
            $insert_sql = "INSERT INTO student_specializations (student_id, specialization_id, status)
                           VALUES ($student_id, $specialization_id, 'pending')";
            if (mysqli_query($conn, $insert_sql)) {
                $message = 'Application submitted successfully.';
            } else {
                $error = 'Could not submit application.';
            }
        }
    }
}

$sp_list_sql = "SELECT * FROM specializations ORDER BY name ASC";
$sp_list_result = mysqli_query($conn, $sp_list_sql);

include __DIR__ . '/../includes/header.php';
?>
<h1 class="page-title">Apply for Specialization</h1>
<div class="form-card">
    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo e($message); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo e($error); ?></div>
    <?php endif; ?>

    <form method="post" data-validate="true">
        <div class="form-group">
            <label>Select Specialization</label>
            <select name="specialization_id" required>
                <option value="">Choose</option>
                <?php if ($sp_list_result): ?>
                    <?php while ($sp = mysqli_fetch_assoc($sp_list_result)): ?>
                        <option value="<?php echo (int) $sp['id']; ?>">
                            <?php echo e($sp['name']); ?> (<?php echo e(ucwords(str_replace('_', ' ', $sp['type']))); ?>)
                        </option>
                    <?php endwhile; ?>
                <?php endif; ?>
            </select>
        </div>

        <button type="submit">Apply</button>
    </form>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>