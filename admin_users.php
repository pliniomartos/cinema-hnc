<?php
require_once 'config.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('Location: login.php');
    exit();
}

$link = getDBConnection();
$message = '';

// Handle admin toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_admin']) && validateCsrfToken($_POST['csrf_token'] ?? '')) {
    $user_id = intval($_POST['toggle_admin']);
    // Don't allow removing admin from yourself
    if ($user_id != $_SESSION['id']) {
        $stmt = mysqli_prepare($link, "UPDATE new_users SET is_admin = NOT is_admin WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'i', $user_id);
        if (mysqli_stmt_execute($stmt)) {
            $message = 'User admin status updated.';
        }
        mysqli_stmt_close($stmt);
    } else {
        $message = 'You cannot remove your own admin privileges.';
    }
}

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user']) && validateCsrfToken($_POST['csrf_token'] ?? '')) {
    $user_id = intval($_POST['delete_user']);
    // Don't allow deleting yourself
    if ($user_id != $_SESSION['id']) {
        $stmt = mysqli_prepare($link, "DELETE FROM new_users WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'i', $user_id);
        if (mysqli_stmt_execute($stmt)) {
            $message = 'User deleted successfully.';
        }
        mysqli_stmt_close($stmt);
    } else {
        $message = 'You cannot delete your own account.';
    }
}

// Get all users
$result = mysqli_query($link, "SELECT id, username, email, created_at, is_admin FROM new_users ORDER BY created_at DESC");
$users = [];
while ($row = mysqli_fetch_assoc($result)) {
    $users[] = $row;
}

mysqli_close($link);

include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="container mt-5">
    <h1 class="text-orange0 mb-4">Manage Users</h1>
    
    <?php if ($message): ?>
        <div class="alert alert-info"><?php echo e($message); ?></div>
    <?php endif; ?>

    <div class="card bg-black border-orange0">
        <div class="card-header bg-orange0 text-black">
            <h4 class="mb-0">Users (<?php echo count($users); ?>)</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-dark table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Created</th>
                            <th>Role</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo e($user['username']); ?></td>
                            <td><?php echo e($user['email']); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                            <td>
                                <?php if ($user['is_admin']): ?>
                                    <span class="badge bg-orange0 text-black">Admin</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">User</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <form method="POST" style="display:inline" onsubmit="return confirm('Are you sure?')">
                                    <input type="hidden" name="csrf_token" value="<?php echo e(getCsrfToken()); ?>">
                                    <input type="hidden" name="toggle_admin" value="<?php echo e($user['id']); ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-yellow-1">
                                        <?php echo $user['is_admin'] ? 'Remove Admin' : 'Make Admin'; ?>
                                    </button>
                                </form>
                                <?php if ($user['id'] != $_SESSION['id']): ?>
                                    <form method="POST" style="display:inline" onsubmit="return confirm('Are you sure you want to delete this user?')">
                                        <input type="hidden" name="csrf_token" value="<?php echo e(getCsrfToken()); ?>">
                                        <input type="hidden" name="delete_user" value="<?php echo e($user['id']); ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-red0">Delete</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer_element.php'; ?>
