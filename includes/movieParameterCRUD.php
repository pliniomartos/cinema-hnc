<?php

class MovieCRUD
{
	private $link;

	public function __construct($db_link)
	{
		$this->link = $db_link;
	}

	private function posterOrPlaceholder($img)
	{
		$img = trim((string)$img);
		if ($img === '' || strtoupper($img) === 'N/A') {
			return 'img/comingsoon.gif';
		}
		return $img;
	}

	public function displayMovies($r)
	{
		if ($r && mysqli_num_rows($r) > 0) {
			echo '<div class="row flex-nowrap overflow-x-visible mt-3 mb-3" style="overflow-x: scroll; overflow-y: hidden;" onwheel="event.preventDefault(); this.scrollLeft += (event.deltaY > 0 ? 1 : -1) * 50;">';
			while ($row = mysqli_fetch_array($r, MYSQLI_ASSOC)) {
				$poster = $this->posterOrPlaceholder($row['img'] ?? '');
				echo '
            <div class="col-md-3 mb-3 d-flex justify-content-center">
                <div class="card bg-black d-flex flex-column" style="width: 18rem;">
                    <img src="' . htmlspecialchars($poster) . '" class="card-img" alt="Movie poster">
                    <div class="card-img-overlay text-center d-flex flex-column justify-content-between">
                        <h5 class="card-title text-bg-yellow-1 rounded">' . htmlspecialchars($row['movie_title']) . '</h5>
                        <a href="movie.php?movie_id=' . urlencode($row['movie_id']) . '" class="btn btn-outline-orange0 fw-bolder stretched-link border-0 btn-sm mt-auto" role="button">Book Now</a>
                    </div>
                </div>
            </div>';
			}
			echo '</div>';
		} else {
			echo '<p>There are currently no movies showing.</p>';
		}
	}

	public function getMovies()
	{
		$this->getMoviesByParameter();
	}

	public function getMoviesByParameter($parameter = null, $value = null)
	{
		$q = "SELECT * FROM movie_listings";
		if ($parameter && $value) {
			$q .= " WHERE $parameter >= '" . mysqli_real_escape_string($this->link, $value) . "'";
		}
		$q .= " ORDER BY `release` DESC";
		$this->displayMovies(mysqli_query($this->link, $q));
	}

	public function getComingSoonMovies()
	{
		$q = "SELECT * FROM movie_listings WHERE `release` > CURDATE() AND `release` <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) ORDER BY `release` ASC";
		$this->displayMovies(mysqli_query($this->link, $q));
	}

	public function getNewReleases()
	{
		$q = "SELECT * FROM movie_listings WHERE `release` BETWEEN DATE_SUB(CURDATE(), INTERVAL 2 MONTH) AND CURDATE() ORDER BY `release` DESC";
		$this->displayMovies(mysqli_query($this->link, $q));
	}

	public function getMoviesByGenre($genre)
	{
		$q = "SELECT * FROM movie_listings WHERE genre = '" . mysqli_real_escape_string($this->link, $genre) . "' ORDER BY `release` DESC";
		$this->displayMovies(mysqli_query($this->link, $q));
	}
}

?>
