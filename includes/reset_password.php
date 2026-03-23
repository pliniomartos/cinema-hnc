<?php
session_start();
require '../connect_db.php';
global $link;

if (!isset($_SESSION['id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $old_password = mysqli_real_escape_string($link, trim($_POST['old_password']));
    $new_password = mysqli_real_escape_string($link, trim($_POST['new_password']));
    $confirm_password = mysqli_real_escape_string($link, trim($_POST['confirm_password']));

    // Fetch the current password from the database
    $query = "SELECT password FROM new_users WHERE id = ?";
    $stmt = mysqli_prepare($link, $query);
    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $current_password);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    // Validate the old password
    if (hash('sha256', $old_password) !== $current_password) {
        echo '<p>Old password is incorrect.</p>';
        exit();
    }

    // Check if new password and confirm password match
    if ($new_password !== $confirm_password) {
        echo '<p>New passwords do not match.</p>';
        exit();
    }

    // Update the password in the database
    $hashed_new_password = hash('sha256', $new_password);
    $query = "UPDATE new_users SET password = ? WHERE id = ?";
    $stmt = mysqli_prepare($link, $query);
    mysqli_stmt_bind_param($stmt, 'si', $hashed_new_password, $user_id);

    if (mysqli_stmt_execute($stmt)) {
        echo 'Password successfully updated.';
    } else {
        echo '<p>Error updating password: ' . mysqli_error($link) . '</p>';
    }

    mysqli_stmt_close($stmt);
    mysqli_close($link);
} else {
    echo '<form action="reset_password.php" method="post">
            <label for="old_password">Old Password:</label>
            <input type="password" name="old_password" id="old_password" required>
            <label for="new_password">New Password:</label>
            <input type="password" name="new_password" id="new_password" required>
            <label for="confirm_password">Confirm New Password:</label>
            <input type="password" name="confirm_password" id="confirm_password" required>
            <button type="submit">Reset Password</button>
          </form>';
}
?>