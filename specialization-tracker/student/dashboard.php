<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('student');

$user_id = (int) $_SESSION['user_id'];

$student_sql = "SELECT s.*, u.name, u.email
                FROM students s
                INNER JOIN users u ON u.id = s.user_id
                WHERE s.user_id = $user_id
                LIMIT 1";
$student_result = mysqli_query($conn, $student_sql);
$student = $student_result ? mysqli_fetch_assoc($student_result) : null;

$selected_types = [];
$sel_sql = "SELECT sp.type
            FROM student_specializations ss
            INNER JOIN specializations sp ON sp.id = ss.specialization_id
            WHERE ss.student_id = " . (int) ($student['id'] ?? 0);
$sel_result = mysqli_query($conn, $sel_sql);
if ($sel_result) {
    while ($row = mysqli_fetch_assoc($sel_result)) {
        $selected_types[] = $row['type'];
    }
}

$eligibility = get_allowed_specializations($student ?? [], $selected_types);

$status_sql = "SELECT ss.status, sp.name, sp.type
               FROM student_specializations ss
               INNER JOIN specializations sp ON sp.id = ss.specialization_id
               WHERE ss.student_id = " . (int) ($student['id'] ?? 0) . "
               ORDER BY ss.id DESC";
$status_result = mysqli_query($conn, $status_sql);

$credits_sql = "SELECT IFNULL(SUM(c.credits), 0) AS total_credits
                FROM student_specializations ss
                INNER JOIN specializations sp ON sp.id = ss.specialization_id
                INNER JOIN courses c ON c.type = sp.type
                WHERE ss.student_id = " . (int) ($student['id'] ?? 0) . "
                AND ss.status = 'approved'";
$credits_result = mysqli_query($conn, $credits_sql);
$credits_row = $credits_result ? mysqli_fetch_assoc($credits_result) : ['total_credits' => 0];

include __DIR__ . '/../includes/header.php';
?>
<h1 class="page-title">Student Dashboard</h1>

<?php if ($student): ?>
<div class="card-grid">
    <div class="card"><h3>CGPA</h3><p><?php echo e($student['cgpa']); ?></p></div>
    <div class="card"><h3>KT Status</h3><p><?php echo e(strtoupper($student['kt_status'])); ?></p></div>
    <div class="card"><h3>Semester</h3><p><?php echo e($student['semester']); ?></p></div>
    <div class="card"><h3>Approved Credits</h3><p><?php echo (int) ($credits_row['total_credits'] ?? 0); ?></p></div>
</div>

<div class="card" style="margin-bottom:16px;">
    <h3>Eligibility</h3>
    <p>Honours: <?php echo e($eligibility['honours']['message']); ?></p>
    <p>Minor: <?php echo e($eligibility['minor']['message']); ?></p>
    <p>Honours with Research: <?php echo e($eligibility['honours_with_research']['message']); ?></p>
</div>

<div class="table-wrap">
    <table>
        <thead>
        <tr>
            <th>Specialization</th>
            <th>Type</th>
            <th>Status</th>
        </tr>
        </thead>
        <tbody>
        <?php if ($status_result && mysqli_num_rows($status_result) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($status_result)): ?>
                <tr>
                    <td><?php echo e($row['name']); ?></td>
                    <td><?php echo e(ucwords(str_replace('_', ' ', $row['type']))); ?></td>
                    <td>
                        <span class="status-pill status-<?php echo e($row['status']); ?>">
                            <?php echo e(ucfirst($row['status'])); ?>
                        </span>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="3">No applications found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php else: ?>
    <div class="alert alert-error">Student profile not found.</div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>