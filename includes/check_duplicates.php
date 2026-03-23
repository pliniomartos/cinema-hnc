<?php
require '../connect_db.php';

$response = array('status' => 'success', 'message' => '');

if (isset($_POST['username'])) {
    $username = mysqli_real_escape_string($link, trim($_POST['username']));
    $query = "SELECT id FROM new_users WHERE username = ?";
    $stmt = mysqli_prepare($link, $query);
    mysqli_stmt_bind_param($stmt, 's', $username);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    if (mysqli_stmt_num_rows($stmt) > 0) {
        $response['status'] = 'error';
        $response['message'] = 'Username already exists. Please choose another one.';
    }
    mysqli_stmt_close($stmt);
}

if (isset($_POST['email'])) {
    $email = mysqli_real_escape_string($link, trim($_POST['email']));
    $query = "SELECT id FROM new_users WHERE email = ?";
    $stmt = mysqli_prepare($link, $query);
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    if (mysqli_stmt_num_rows($stmt) > 0) {
        $response['status'] = 'error';
        $response['message'] = 'Email already registered. Please use another one.';
    }
    mysqli_stmt_close($stmt);
}

mysqli_close($link);
echo json_encode($response);
?>