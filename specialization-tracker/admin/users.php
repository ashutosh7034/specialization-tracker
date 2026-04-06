<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['admin', 'super_admin']);

$message = '';
$error = '';

function can_manage_role($logged_in_role, $target_role)
{
    if ($logged_in_role === 'super_admin') {
        return in_array($target_role, ['admin', 'coordinator', 'mentor', 'student'], true);
    }

    if ($logged_in_role === 'admin') {
        return in_array($target_role, ['coordinator', 'mentor', 'student'], true);
    }

    return false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $name = mysqli_real_escape_string($conn, trim($_POST['name'] ?? ''));
        $email = mysqli_real_escape_string($conn, trim($_POST['email'] ?? ''));
        $password = trim($_POST['password'] ?? '');
        $role = mysqli_real_escape_string($conn, trim($_POST['role'] ?? 'student'));
        $department_id = (int) ($_POST['department_id'] ?? 0);

        if (!can_manage_role($_SESSION['role'], $role)) {
            $error = 'You are not allowed to create this role.';
        } elseif ($name === '' || $email === '' || $password === '' || $department_id <= 0) {
            $error = 'All fields are required.';
        } else {
            $check_sql = "SELECT id FROM users WHERE email = '$email' LIMIT 1";
            $check_result = mysqli_query($conn, $check_sql);

            if ($check_result && mysqli_num_rows($check_result) > 0) {
                $error = 'Email already exists.';
            } else {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $insert_user_sql = "INSERT INTO users (name, email, password, role, department_id)
                                   VALUES ('$name', '$email', '$hashed', '$role', $department_id)";

                if (mysqli_query($conn, $insert_user_sql)) {
                    $new_user_id = mysqli_insert_id($conn);

                    if ($role === 'student') {
                        mysqli_query($conn, "INSERT INTO students (user_id, cgpa, kt_status, semester)
                                           VALUES ($new_user_id, 0.00, 'no', 1)");
                    }

                    if ($role === 'mentor') {
                        mysqli_query($conn, "INSERT INTO mentors (user_id) VALUES ($new_user_id)");
                    }

                    $message = 'User added successfully.';
                } else {
                    $error = 'Failed to add user.';
                }
            }
        }
    }

    if ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0 && $id !== (int) $_SESSION['user_id']) {
            $role_check_result = mysqli_query($conn, "SELECT role FROM users WHERE id = $id LIMIT 1");
            $role_row = $role_check_result ? mysqli_fetch_assoc($role_check_result) : null;
            $target_role = $role_row['role'] ?? '';

            if (!can_manage_role($_SESSION['role'], $target_role)) {
                $error = 'You are not allowed to delete this role.';
            } else {
                mysqli_query($conn, "DELETE FROM users WHERE id = $id");
                $message = 'User deleted.';
            }
        } else {
            $error = 'Invalid user deletion request.';
        }
    }

    if ($action === 'edit') {
        $id = (int) ($_POST['id'] ?? 0);
        $name = mysqli_real_escape_string($conn, trim($_POST['name'] ?? ''));
        $role = mysqli_real_escape_string($conn, trim($_POST['role'] ?? 'student'));
        $department_id = (int) ($_POST['department_id'] ?? 0);

        if (!can_manage_role($_SESSION['role'], $role)) {
            $error = 'You are not allowed to update this role.';
        } elseif ($id > 0 && $name !== '' && $department_id > 0) {
            $update_sql = "UPDATE users SET name = '$name', role = '$role', department_id = $department_id WHERE id = $id";
            if (mysqli_query($conn, $update_sql)) {
                $message = 'User updated successfully.';
            } else {
                $error = 'Failed to update user.';
            }
        } else {
            $error = 'Name and department are required for update.';
        }
    }
}

$dept_sql = "SELECT * FROM departments ORDER BY name ASC";
$dept_result = mysqli_query($conn, $dept_sql);

$list_sql = "SELECT u.*, d.name AS department_name
            FROM users u
            LEFT JOIN departments d ON d.id = u.department_id
            ORDER BY u.id DESC";
$list_result = mysqli_query($conn, $list_sql);

include __DIR__ . '/../includes/header.php';
?>
<h1 class="page-title">Manage Users</h1>
<div class="form-card" style="margin-bottom:16px;">
    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo e($message); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo e($error); ?></div>
    <?php endif; ?>

    <form method="post" data-validate="true">
        <input type="hidden" name="action" value="add">

        <div class="form-group"><label>Name</label><input type="text" name="name" required></div>
        <div class="form-group"><label>Email</label><input type="email" name="email" required></div>
        <div class="form-group"><label>Password</label><input type="password" name="password" required></div>

        <div class="form-group">
            <label>Role</label>
            <select name="role" required>
                <?php if ($_SESSION['role'] === 'super_admin'): ?>
                    <option value="admin">Admin</option>
                <?php endif; ?>
                <option value="coordinator">Coordinator</option>
                <option value="mentor">Mentor</option>
                <option value="student">Student</option>
            </select>
        </div>

        <div class="form-group">
            <label>Department</label>
            <select name="department_id" required>
                <option value="">Select Department</option>
                <?php if ($dept_result): ?>
                    <?php while ($dept = mysqli_fetch_assoc($dept_result)): ?>
                        <option value="<?php echo (int) $dept['id']; ?>"><?php echo e($dept['name']); ?></option>
                    <?php endwhile; ?>
                <?php endif; ?>
            </select>
        </div>

        <button type="submit">Add User</button>
    </form>
</div>

<div class="table-wrap">
    <table>
        <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Department</th>
            <th>Action</th>
        </tr>
        </thead>
        <tbody>
        <?php if ($list_result && mysqli_num_rows($list_result) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($list_result)): ?>
                <tr>
                    <td><?php echo (int) $row['id']; ?></td>
                    <td><?php echo e($row['name']); ?></td>
                    <td><?php echo e($row['email']); ?></td>
                    <td><?php echo e(get_role_label($row['role'])); ?></td>
                    <td><?php echo e($row['department_name'] ?? '-'); ?></td>
                    <td>
                        <form method="post" style="display:grid; gap:8px; margin-bottom:8px;">
                            <input type="hidden" name="action" value="edit">
                            <input type="hidden" name="id" value="<?php echo (int) $row['id']; ?>">
                            <input type="text" name="name" value="<?php echo e($row['name']); ?>" required>
                            <select name="role" required>
                                <?php if ($_SESSION['role'] === 'super_admin'): ?>
                                    <option value="admin" <?php echo $row['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                <?php endif; ?>
                                <option value="coordinator" <?php echo $row['role'] === 'coordinator' ? 'selected' : ''; ?>>Coordinator</option>
                                <option value="mentor" <?php echo $row['role'] === 'mentor' ? 'selected' : ''; ?>>Mentor</option>
                                <option value="student" <?php echo $row['role'] === 'student' ? 'selected' : ''; ?>>Student</option>
                            </select>
                            <select name="department_id" required>
                                <option value="">Select Department</option>
                                <?php
                                $dept_again = mysqli_query($conn, "SELECT id, name FROM departments ORDER BY name ASC");
                                if ($dept_again) {
                                    while ($dept_row = mysqli_fetch_assoc($dept_again)) {
                                        $selected = ((int) $row['department_id'] === (int) $dept_row['id']) ? 'selected' : '';
                                        echo '<option value="' . (int) $dept_row['id'] . '" ' . $selected . '>' . e($dept_row['name']) . '</option>';
                                    }
                                }
                                ?>
                            </select>
                            <button type="submit">Update</button>
                        </form>

                        <form method="post" onsubmit="return confirm('Delete this user?')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo (int) $row['id']; ?>">
                            <button type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="6">No users found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>