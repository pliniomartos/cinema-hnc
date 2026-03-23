<?php
session_start();
require '../connect_db.php';

if (!isset($_SESSION['id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['id'];

$query = "DELETE FROM new_users WHERE id = ?";
$stmt = mysqli_prepare($link, $query);
mysqli_stmt_bind_param($stmt, 'i', $user_id);

if (mysqli_stmt_execute($stmt)) {
    session_destroy();
    header('Location: goodbye.php');
    exit();
} else {
    echo '<p>Error deleting account: ' . mysqli_error($link) . '</p>';
}

mysqli_stmt_close($stmt);
mysqli_close($link);
?>