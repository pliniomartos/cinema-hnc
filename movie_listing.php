<?php
include 'includes/navbar.php';

# Open database connection.
require('connect_db.php');
global $link;

# Retrieve movies from 'movie_listings' database table.
require_once 'includes/movieParameterCRUD.php';

echo "<div class='container mt-5 sticky-top ' style='width: fit-content;'><h1 class='text-center text-orange0'>Listing</h1></div>
			<div class='container mt-3'>
      <h3 class='text-orange0 fw-bold'> New Releases </h3>";
$movieNow = new MovieCRUD($link);
$movieNow->getNewReleases();
echo "</div>";

echo "<div class='container mt-3'>
      <h3 class='text-orange0 fw-bold'> Coming Soon </h3>";
$movieSoon = new MovieCRUD($link);
$movieSoon->getComingSoonMovies();
echo "</div>";

echo "<div class='container mt-3'>
      <h3 class='text-orange0 fw-bold'> All Movies </h3>";
$movieAll = new MovieCRUD($link);
$movieAll->getMovies();
echo "</div>";


include 'includes/footer_element.php';
?>