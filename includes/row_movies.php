<?php
class MovieCRUD {
    private $link;

    public function __construct($db_link) {
        $this->link = $db_link;
    }

    public function getMovies() {
        $this->getMoviesByParameter();
    }

    public function getMoviesByParameter($parameter = null, $value = null) {
        $q = "SELECT * FROM movie_listings";
        if ($parameter && $value) {
            $q .= " WHERE $parameter = '" . mysqli_real_escape_string($this->link, $value) . "'";
        }
        $r = mysqli_query($this->link, $q);

        if (mysqli_num_rows($r) > 0) {
            echo '<div class="row flex-nowrap overflow-auto pb-5">';
            while ($row = mysqli_fetch_array($r, MYSQLI_ASSOC)) {
                echo '
                <div class="col-md-2 d-flex justify-content-center mb-5">
                <div class="card" style="width: 18rem; margin: 0.5rem;">
                      <div class="card text-center">
                          <img src='. $row['img'].' alt="Movie" class="img-thumbnail bg-secondary">
                          <h6>'. $row['movie_title'].'</h6>
                          <div class="card-footer">
                          <a href="movie.php?movie_id='.$row['movie_id'].'" class="btn btn-secondary btn-block" role="button">
                         Book Now</a>
                         </div>
                       </div>
                  </div>
                </div>';
            }
            echo '</div>';
        } else {
            echo '<p>There are currently no movies showing.</p>';
        }
    }
}
?>