<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require '../connect_db.php';

    $username = mysqli_real_escape_string($link, trim($_POST['username']));
    $email = mysqli_real_escape_string($link, trim($_POST['email']));
    $password = mysqli_real_escape_string($link, trim($_POST['pass1']));
    $confirm_password = mysqli_real_escape_string($link, trim($_POST['pass2']));

    if ($password !== $confirm_password) {
        echo '<p>Passwords do not match.</p>';
        exit();
    }

    // Check for duplicate username
    $query = "SELECT id FROM new_users WHERE username = ?";
    $stmt = mysqli_prepare($link, $query);
    mysqli_stmt_bind_param($stmt, 's', $username);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    if (mysqli_stmt_num_rows($stmt) > 0) {
        echo '<p>Username already exists. Please choose another one.</p>';
        exit();
    }
    mysqli_stmt_close($stmt);

    // Check for duplicate email
    $query = "SELECT id FROM new_users WHERE email = ?";
    $stmt = mysqli_prepare($link, $query);
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    if (mysqli_stmt_num_rows($stmt) > 0) {
        echo '<p>Email already registered. Please use another one.</p>';
        exit();
    }
    mysqli_stmt_close($stmt);

    $hashed_password = hash('sha256', $password);

    $query = "INSERT INTO new_users (username, email, password, created_at) VALUES (?, ?, ?, NOW())";
    $stmt = mysqli_prepare($link, $query);
    mysqli_stmt_bind_param($stmt, 'sss', $username, $email, $hashed_password);

    if (mysqli_stmt_execute($stmt)) {
        header('Location: ../login.php');
        exit();
    } else {
        echo '<p>Error registering user: ' . mysqli_error($link) . '</p>';
    }

    mysqli_stmt_close($stmt);
    mysqli_close($link);
} else {
    header('Location: ../register.php');
    exit();
}