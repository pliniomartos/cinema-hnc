<?php

// Include the database connection file

require_once __DIR__ . '/../connect_db.php';
global $link;

// Check if the connection is successful
if (!$link) {
    echo 'Could not connect to MySQL: ' . mysqli_error($link);
    exit;
}

// Fetch movie listings
$sql = $link->prepare("SELECT movie_id, movie_title, img FROM movie_listings WHERE img IS NOT NULL AND TRIM(img) <> '' AND UPPER(TRIM(img)) <> 'N/A' ORDER BY `release` DESC");
if (!$sql) {
    echo 'Error preparing query: ' . $link->error;
    exit;
}

$sql->execute();
$sql->store_result();

// Check if the query was successful
if ($sql->errno) {
    echo 'Error executing query: ' . $sql->error;
    exit;
}

// Check if there are any results
if ($sql->num_rows > 0) {
    echo '<div id="carouselExampleCaptions" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-indicators">';
    for ($i = 0; $i < $sql->num_rows; $i++) {
        echo '<button type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide-to="' . $i . '" ' . ($i == 0 ? 'class="active" aria-current="true"' : '') . ' aria-label="Slide ' . ($i + 1) . '"></button>';
    }
    echo '</div>'
        . '<div class="carousel-inner">';
    $sql->bind_result($movie_id, $movie_title, $img);
    $i = 0;
    while ($sql->fetch()) {
        echo '<div class="carousel-item ' . ($i == 0 ? 'active' : '') . '">
            <img src="' . $img . '" class="d-block w-100" alt="' . $movie_title . '" style="height: 80vh; object-fit: contain; ">
            <div class="carousel-caption d-none d-md-block max-vh-100">
            <a href="movie.php?movie_id=' . $movie_id . '" class="btn btn-lg btn-outline-yellow-1">' . $movie_title . '</a>
            </div>
            </div>';
        $i++;
    }
    echo '</div>
        <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Next</span>
        </button>
        </div>';
} else {
    echo "0 results";
}

$sql->close();
$link->close();


