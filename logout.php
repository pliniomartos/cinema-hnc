<?php # DISPLAY COMPLETE LOGGED OUT PAGE.

# Access session.
session_start();

# Redirect if not logged in.
if (!isset($_SESSION['id'])) {
    require('login_tools.php');
    load();
}

# Clear existing variables.
$_SESSION = array();

# Destroy the session.
session_destroy();
# Display footer section.
include('login.php');
?>