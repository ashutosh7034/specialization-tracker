<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('mentor');

$mentor_user_id = (int) $_SESSION['user_id'];

$mentor_sql = "SELECT * FROM mentors WHERE user_id = $mentor_user_id LIMIT 1";
$mentor_result = mysqli_query($conn, $mentor_sql);
$mentor = $mentor_result ? mysqli_fetch_assoc($mentor_result) : null;
$mentor_id = (int) ($mentor['id'] ?? 0);

$list_sql = "SELECT u.name, u.email, d.name AS department, s.cgpa, s.kt_status, s.semester
            FROM mentor_student_map msm
            INNER JOIN students s ON s.id = msm.student_id
            INNER JOIN users u ON u.id = s.user_id
            LEFT JOIN departments d ON d.id = u.department_id
            WHERE msm.mentor_id = $mentor_id
            ORDER BY u.name ASC";
$list_result = mysqli_query($conn, $list_sql);

include __DIR__ . '/../includes/header.php';
?>
<h1 class="page-title">Mentor Dashboard</h1>

<div class="card" style="margin-bottom:16px;">
    <h3>Hierarchy Action</h3>
    <p style="margin:8px 0 12px; color:#64748b;">As mentor, you can create new student accounts and auto-assign them to yourself.</p>
    <a class="btn" style="width:auto; padding:10px 14px;" href="/specialization-tracker/mentor/add_student.php">Add Student</a>
</div>

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
        </tr>
        </thead>
        <tbody>
        <?php if ($list_result && mysqli_num_rows($list_result) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($list_result)): ?>
                <tr>
                    <td><?php echo e($row['name']); ?></td>
                    <td><?php echo e($row['email']); ?></td>
                    <td><?php echo e($row['department']); ?></td>
                    <td><?php echo e($row['cgpa']); ?></td>
                    <td><?php echo e(strtoupper($row['kt_status'])); ?></td>
                    <td><?php echo e($row['semester']); ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="6">No assigned students found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>