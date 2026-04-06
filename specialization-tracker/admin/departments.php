<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['admin', 'super_admin']);

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $name = mysqli_real_escape_string($conn, trim($_POST['name'] ?? ''));
        if ($name !== '') {
            $sql = "INSERT INTO departments (name) VALUES ('$name')";
            if (mysqli_query($conn, $sql)) {
                $message = 'Department added successfully.';
            } else {
                $error = 'Failed to add department.';
            }
        } else {
            $error = 'Department name is required.';
        }
    }

    if ($action === 'edit') {
        $id = (int) ($_POST['id'] ?? 0);
        $name = mysqli_real_escape_string($conn, trim($_POST['name'] ?? ''));

        if ($id > 0 && $name !== '') {
            $sql = "UPDATE departments SET name = '$name' WHERE id = $id";
            if (mysqli_query($conn, $sql)) {
                $message = 'Department updated.';
            } else {
                $error = 'Failed to update department.';
            }
        } else {
            $error = 'Department ID and name are required for update.';
        }
    }

    if ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) {
            $sql = "DELETE FROM departments WHERE id = $id";
            if (mysqli_query($conn, $sql)) {
                $message = 'Department deleted.';
            } else {
                $error = 'Could not delete department (maybe users are linked).';
            }
        }
    }
}

$list_sql = "SELECT * FROM departments ORDER BY id DESC";
$list_result = mysqli_query($conn, $list_sql);

include __DIR__ . '/../includes/header.php';
?>
<h1 class="page-title">Manage Departments</h1>

<div class="form-card" style="margin-bottom:16px;">
    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo e($message); ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo e($error); ?></div>
    <?php endif; ?>

    <form method="post" data-validate="true">
        <input type="hidden" name="action" value="add">
        <div class="form-group">
            <label>Department Name</label>
            <input type="text" name="name" required>
        </div>
        <button type="submit">Add Department</button>
    </form>
</div>

<div class="table-wrap">
    <table>
        <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Action</th>
        </tr>
        </thead>
        <tbody>
        <?php if ($list_result && mysqli_num_rows($list_result) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($list_result)): ?>
                <tr>
                    <td><?php echo (int) $row['id']; ?></td>
                    <td><?php echo e($row['name']); ?></td>
                    <td>
                        <form method="post" style="display:flex; gap:8px; margin-bottom:8px;">
                            <input type="hidden" name="action" value="edit">
                            <input type="hidden" name="id" value="<?php echo (int) $row['id']; ?>">
                            <input type="text" name="name" value="<?php echo e($row['name']); ?>" required>
                            <button type="submit">Update</button>
                        </form>

                        <form method="post" onsubmit="return confirm('Delete this department?')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo (int) $row['id']; ?>">
                            <button type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="3">No departments found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>