<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['admin', 'super_admin']);

$sql = "SELECT u.name AS student_name, u.email, d.name AS department,
               s.cgpa, s.kt_status, s.semester,
               sp.name AS specialization, ss.status AS app_status
        FROM users u
        INNER JOIN students s ON s.user_id = u.id
        LEFT JOIN departments d ON d.id = u.department_id
        LEFT JOIN student_specializations ss ON ss.student_id = s.id
        LEFT JOIN specializations sp ON sp.id = ss.specialization_id
        ORDER BY u.name ASC";
$result = mysqli_query($conn, $sql);

include __DIR__ . '/../includes/header.php';
?>
<h1 class="page-title">All Student Data</h1>
<div class="table-wrap">
    <table>
        <thead>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Department</th>
            <th>CGPA</th>
            <th>KT</th>
            <th>Semester</th>
            <th>Specialization</th>
            <th>Status</th>
        </tr>
        </thead>
        <tbody>
        <?php if ($result && mysqli_num_rows($result) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?php echo e($row['student_name']); ?></td>
                    <td><?php echo e($row['email']); ?></td>
                    <td><?php echo e($row['department'] ?? '-'); ?></td>
                    <td><?php echo e($row['cgpa']); ?></td>
                    <td><?php echo e(strtoupper($row['kt_status'])); ?></td>
                    <td><?php echo e($row['semester']); ?></td>
                    <td><?php echo e($row['specialization'] ?? '-'); ?></td>
                    <td><?php echo e($row['app_status'] ?? '-'); ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="8">No data found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>