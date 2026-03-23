<?php
include 'includes/navbar.php';
require 'connect_db.php';

# Initialize an error array.
$errors = array();

# Check form submitted.
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	# Check for an email address:
	if (empty($_POST['email'])) {
		$errors[] = 'Enter your email address.';
	} else {
		$e = mysqli_real_escape_string($link, trim($_POST['email']));
	}

	# Check for a password:
	if (empty($_POST['password'])) {
		$errors[] = 'Enter your password.';
	} else {
		$p = mysqli_real_escape_string($link, trim($_POST['password']));
	}

	# On success retrieve user from 'new_users' database table.
	if (empty($errors)) {
		$q = "SELECT id, username FROM new_users WHERE email='$e' AND password=SHA2('$p',256)";
		$r = @mysqli_query($link, $q);
		if (mysqli_num_rows($r) == 1) {
			# Set session data and redirect to the home page.
			session_start();
			$_SESSION = mysqli_fetch_array($r, MYSQLI_ASSOC);
			header('Location: home.php');
			exit();
		} else {
			$errors[] = 'Email or password is incorrect.';
		}
	}
}

# Display modal and only auto-open when login errors exist.
include 'includes/login_modal.php';
if (!empty($errors)) {
    echo '<script>const errorModal = new bootstrap.Modal(document.getElementById("loginModal")); errorModal.show();</script>';
}
include 'includes/footer.php';
?>
