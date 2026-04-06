<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('coordinator');

$department_id = (int) ($_SESSION['department_id'] ?? 0);

$report_sql = "SELECT u.name, u.email, s.semester, s.cgpa, s.kt_status,
                      sp.name AS specialization, ss.status
               FROM users u
               INNER JOIN students s ON s.user_id = u.id
               LEFT JOIN student_specializations ss ON ss.student_id = s.id
               LEFT JOIN specializations sp ON sp.id = ss.specialization_id
               WHERE u.department_id = $department_id
               ORDER BY u.name ASC";
$report_result = mysqli_query($conn, $report_sql);

include __DIR__ . '/../includes/header.php';
?>
<h1 class="page-title">Department Reports</h1>
<div class="table-wrap">
    <table>
        <thead>
        <tr>
            <th>Student</th>
            <th>Email</th>
            <th>Semester</th>
            <th>CGPA</th>
            <th>KT</th>
            <th>Specialization</th>
            <th>Status</th>
        </tr>
        </thead>
        <tbody>
        <?php if ($report_result && mysqli_num_rows($report_result) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($report_result)): ?>
                <tr>
                    <td><?php echo e($row['name']); ?></td>
                    <td><?php echo e($row['email']); ?></td>
                    <td><?php echo e($row['semester']); ?></td>
                    <td><?php echo e($row['cgpa']); ?></td>
                    <td><?php echo e(strtoupper($row['kt_status'])); ?></td>
                    <td><?php echo e($row['specialization'] ?? '-'); ?></td>
                    <td><?php echo e($row['status'] ?? '-'); ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="7">No records found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>