<?php
session_start();

# PROCESS LOGIN ATTEMPT.
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    # Open database connection.
    require 'connect_db.php';

    # Get connection, load, and validate functions.
    require 'login_tools.php';

    # Check login.
    list($check, $data) = validate($link, $_POST['email'], $_POST['password']);

    # On success set session data and display logged in page.
    if ($check) {
        $_SESSION['id'] = $data['id'];
        $_SESSION['username'] = $data['username'];
        $_SESSION['email'] = $data['email'];
        $_SESSION['is_admin'] = (int)($data['is_admin'] ?? 0);
        load('home.php');
    } else {
        # Or on failure set errors.
        $errors = $data;
    }

    # Close database connection.
    mysqli_close($link);
}

# Continue to display login page on failure.
include 'login.php';
?>
