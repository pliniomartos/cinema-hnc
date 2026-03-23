<?php
require_once __DIR__ . '/../config.php';

$link = getDBConnection();
$apiKey = OMDB_API_KEY;

if (empty($apiKey)) {
    die('OMDB_API_KEY is missing in .env');
}

$moviesFile = __DIR__ . '/movie_ids.txt';
$movies = array();
if (file_exists($moviesFile)) {
    $lines = file($moviesFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0) {
            continue;
        }
        if (preg_match('/^tt\d+$/i', $line)) {
            $movies[] = $line;
        }
    }
}

if (empty($movies)) {
    die('No IMDb IDs found. Add them to sql/movie_ids.txt (one per line).');
}

function omdbFetch($movieId, $apiKey) {
    $url = 'https://www.omdbapi.com/?i=' . urlencode($movieId) . '&plot=full&apikey=' . urlencode($apiKey);

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        $response = curl_exec($ch);
        curl_close($ch);
    } else {
        $response = @file_get_contents($url);
    }

    if ($response === false) {
        return false;
    }

    return json_decode($response, true);
}

$upsert = mysqli_prepare(
    $link,
    'INSERT INTO movie_listings (movie_id, movie_title, genre, age_rating, show1, show2, show3, theatre, further_info, `release`, img, preview, mov_price)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
     ON DUPLICATE KEY UPDATE movie_title=VALUES(movie_title), genre=VALUES(genre), age_rating=VALUES(age_rating), show1=VALUES(show1), show2=VALUES(show2), show3=VALUES(show3), theatre=VALUES(theatre), further_info=VALUES(further_info), `release`=VALUES(`release`), img=VALUES(img), preview=VALUES(preview), mov_price=VALUES(mov_price)'
);

foreach ($movies as $movieId) {
    $movieData = omdbFetch($movieId, $apiKey);

    if (!$movieData || !isset($movieData['Response']) || $movieData['Response'] !== 'True') {
        echo 'Error fetching ' . htmlspecialchars($movieId) . ': ' . htmlspecialchars($movieData['Error'] ?? 'request failed') . '<br>';
        continue;
    }

    $movie_id = $movieData['imdbID'];
    $movie_title = $movieData['Title'] ?? 'Unknown';
    $genre = $movieData['Genre'] ?? '';
    $age_rating = $movieData['Rated'] ?? '';
    $show1 = '10:00';
    $show2 = '14:00';
    $show3 = '18:00';
    $theatre = 'Main Theatre';
    $further_info = $movieData['Plot'] ?? '';

    $release = null;
    if (!empty($movieData['Released']) && $movieData['Released'] !== 'N/A') {
        $dt = DateTime::createFromFormat('d M Y', $movieData['Released']);
        if ($dt) {
            $release = $dt->format('Y-m-d');
        }
    }

    $img = (!empty($movieData['Poster']) && $movieData['Poster'] !== 'N/A') ? $movieData['Poster'] : '';
    $preview = 'https://www.youtube.com/results?search_query=' . urlencode($movie_title . ' official trailer');
    $mov_price = 10.00;

    mysqli_stmt_bind_param($upsert, 'sssssssssssds', $movie_id, $movie_title, $genre, $age_rating, $show1, $show2, $show3, $theatre, $further_info, $release, $img, $preview, $mov_price);

    if (mysqli_stmt_execute($upsert)) {
        echo 'Upserted: ' . htmlspecialchars($movie_title) . '<br>';
    } else {
        echo 'Upsert error for ' . htmlspecialchars($movie_id) . ': ' . htmlspecialchars(mysqli_stmt_error($upsert)) . '<br>';
    }
}

mysqli_stmt_close($upsert);

echo 'Done.';
?>

