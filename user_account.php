<?php
# Access session.
session_start();

# Redirect if not logged in.
if (!isset($_SESSION['id'])) {
	require('login_tools.php');
	load();
}

# Open database connection.
require('connect_db.php');

# Retrieve items from 'users' database table.
$q = "SELECT * FROM new_users WHERE id={$_SESSION[id]}";
$r = mysqli_query($link, $q);
if (mysqli_num_rows($r) > 0) {

	echo '
	<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration</title>
</head>
<body>
  ';

	while ($row = mysqli_fetch_array($r, MYSQLI_ASSOC)) {
		$date = $row["created_at"];
		$day = substr($date, 8, 2);
		$month = substr($date, 5, 2);
		$year = substr($date, 0, 4);

		echo '	
	  <h1>' . $row['username'] . ' </h1>
	  <hr>
	  User ID : EC2024/' . $row['id'] . ' 
	  <hr>
	  Email :  ' . $row['email'] . '
	  <hr>
	  Registration Date : ' . $day . '/' . $month . '/' . $year . '  
	  <hr>
</body>
</html>
		';
	}

	# Close database connection.
	#mysqli_close( $link ) ;
} else {
	echo '<h3>No user details.</h3>

		';
}
?>
