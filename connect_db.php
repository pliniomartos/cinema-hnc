<?php # CONNECT TO MySQL DATABASE.

# Connect  on 'localhost'.
$link = mysqli_connect('localhost', 'root', '', 'cinema_db');
if (!$link) {

# Otherwise fail gracefully and explain the error. 
	die('Could not connect to MySQL: ' . mysqli_error($link));
}



