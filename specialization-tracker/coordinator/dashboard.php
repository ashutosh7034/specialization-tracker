<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('coordinator');

$department_id = (int) ($_SESSION['department_id'] ?? 0);

$student_count_sql = "SELECT COUNT(*) AS total
                     FROM users u
                     INNER JOIN students s ON s.user_id = u.id
                     WHERE u.department_id = $department_id";
$mentor_count_sql = "SELECT COUNT(*) AS total
                    FROM users u
                    INNER JOIN mentors m ON m.user_id = u.id
                    WHERE u.department_id = $department_id";

$assignment_count_sql = "SELECT COUNT(*) AS total
                        FROM mentor_student_map msm
                        INNER JOIN students s ON s.id = msm.student_id
                        INNER JOIN users u ON u.id = s.user_id
                        WHERE u.department_id = $department_id";

$pending_apps_sql = "SELECT COUNT(*) AS total
                    FROM student_specializations ss
                    INNER JOIN students s ON s.id = ss.student_id
                    INNER JOIN users u ON u.id = s.user_id
                    WHERE u.department_id = $department_id
                    AND ss.status = 'pending'";

$student_count_result = mysqli_query($conn, $student_count_sql);
$mentor_count_result = mysqli_query($conn, $mentor_count_sql);
$assignment_count_result = mysqli_query($conn, $assignment_count_sql);
$pending_apps_result = mysqli_query($conn, $pending_apps_sql);

$students = (int) (mysqli_fetch_assoc($student_count_result)['total'] ?? 0);
$mentors = (int) (mysqli_fetch_assoc($mentor_count_result)['total'] ?? 0);
$assignments = (int) (mysqli_fetch_assoc($assignment_count_result)['total'] ?? 0);
$pending_apps = (int) (mysqli_fetch_assoc($pending_apps_result)['total'] ?? 0);

include __DIR__ . '/../includes/header.php';
?>
<h1 class="page-title">Coordinator Dashboard</h1>
<div class="card-grid">
    <div class="card"><h3>Department Students</h3><p><?php echo $students; ?></p></div>
    <div class="card"><h3>Department Mentors</h3><p><?php echo $mentors; ?></p></div>
    <div class="card"><h3>Mentor Assignments</h3><p><?php echo $assignments; ?></p></div>
    <div class="card"><h3>Pending Applications</h3><p><?php echo $pending_apps; ?></p></div>
</div>

<div class="card">
    <h3>Quick Actions</h3>
    <p style="margin:8px 0 12px; color:#64748b;">Use these shortcuts to manage your department faster.</p>
    <div style="display:flex; gap:10px; flex-wrap:wrap;">
        <a class="btn" style="width:auto; padding:10px 14px;" href="/specialization-tracker/coordinator/add_mentor.php">Add Mentor</a>
        <a class="btn" style="width:auto; padding:10px 14px;" href="/specialization-tracker/coordinator/assign_mentor.php">Assign Mentor</a>
        <a class="btn" style="width:auto; padding:10px 14px;" href="/specialization-tracker/coordinator/reports.php">Open Reports</a>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>