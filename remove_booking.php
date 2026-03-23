<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['movie_id'])) {
	$movie_id = $_POST['movie_id'];
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['movie_id'])) {
	$movie_id = $_GET['movie_id'];
} else {
	echo 'Invalid request.';
	exit();
}

if (isset($_SESSION['cart'][$movie_id])) {
	unset($_SESSION['cart'][$movie_id]);
	echo 'Booking removed successfully.';
} else {
	echo 'Booking not found.';
}
?>