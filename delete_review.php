<?php
session_start();
# Open database connection.
require 'connect_db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	if (!isset($_SESSION['id'])) {
		http_response_code(403);
		exit('Not authorised.');
	}
	$review_id = mysqli_real_escape_string($link, $_POST['review_id']);
	$user_id = (int)$_SESSION['id'];

	$query = "DELETE FROM reviews WHERE id='$review_id' AND user_id='$user_id'";
	$result = mysqli_query($link, $query);

	if ($result) {
		echo "Review deleted successfully.";
	} else {
		echo "Error: " . mysqli_error($link);
	}

	mysqli_close($link);
}
?>