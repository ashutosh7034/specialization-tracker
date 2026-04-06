<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('super_admin');

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rule_key = mysqli_real_escape_string($conn, trim($_POST['rule_key'] ?? ''));
    $rule_value = mysqli_real_escape_string($conn, trim($_POST['rule_value'] ?? ''));
    $description = mysqli_real_escape_string($conn, trim($_POST['description'] ?? ''));

    if ($rule_key === '' || $rule_value === '') {
        $error = 'Rule key and value are required.';
    } else {
        $check_sql = "SELECT id FROM specialization_rules WHERE rule_key = '$rule_key' LIMIT 1";
        $check_result = mysqli_query($conn, $check_sql);

        if ($check_result && mysqli_num_rows($check_result) > 0) {
            $update_sql = "UPDATE specialization_rules
                           SET rule_value = '$rule_value', description = '$description'
                           WHERE rule_key = '$rule_key'";
            if (mysqli_query($conn, $update_sql)) {
                $message = 'Rule updated successfully.';
            } else {
                $error = 'Failed to update rule.';
            }
        } else {
            $insert_sql = "INSERT INTO specialization_rules (rule_key, rule_value, description)
                           VALUES ('$rule_key', '$rule_value', '$description')";
            if (mysqli_query($conn, $insert_sql)) {
                $message = 'Rule added successfully.';
            } else {
                $error = 'Failed to add rule.';
            }
        }
    }
}

$list_sql = "SELECT * FROM specialization_rules ORDER BY id DESC";
$list_result = mysqli_query($conn, $list_sql);

include __DIR__ . '/../includes/header.php';
?>
<h1 class="page-title">Specialization Rules</h1>

<div class="form-card" style="margin-bottom:16px;">
    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo e($message); ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo e($error); ?></div>
    <?php endif; ?>

    <form method="post" data-validate="true">
        <div class="form-group">
            <label>Rule Key</label>
            <input type="text" name="rule_key" placeholder="example: honours_min_cgpa" required>
        </div>

        <div class="form-group">
            <label>Rule Value</label>
            <input type="text" name="rule_value" placeholder="example: 7.0" required>
        </div>

        <div class="form-group">
            <label>Description</label>
            <textarea name="description" rows="3" placeholder="Optional description"></textarea>
        </div>

        <button type="submit">Save Rule</button>
    </form>
</div>

<div class="table-wrap">
    <table>
        <thead>
        <tr>
            <th>ID</th>
            <th>Rule Key</th>
            <th>Rule Value</th>
            <th>Description</th>
        </tr>
        </thead>
        <tbody>
        <?php if ($list_result && mysqli_num_rows($list_result) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($list_result)): ?>
                <tr>
                    <td><?php echo (int) $row['id']; ?></td>
                    <td><?php echo e($row['rule_key']); ?></td>
                    <td><?php echo e($row['rule_value']); ?></td>
                    <td><?php echo e($row['description']); ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="4">No rules found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>