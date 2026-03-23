<?php
require_once __DIR__ . '/../config.php';

$link = getDBConnection();

$q = "CREATE TABLE IF NOT EXISTS `movie_listings` (
  `movie_id` varchar(20) NOT NULL,
  `movie_title` varchar(100) NOT NULL,
  `genre` varchar(100) DEFAULT NULL,
  `age_rating` varchar(50) DEFAULT NULL,
  `show1` varchar(10) DEFAULT '10:00',
  `show2` varchar(10) DEFAULT '14:00',
  `show3` varchar(10) DEFAULT '18:00',
  `theatre` varchar(50) DEFAULT 'Main Theatre',
  `further_info` text,
  `release` date DEFAULT NULL,
  `img` varchar(255) DEFAULT NULL,
  `preview` varchar(300) DEFAULT NULL,
  `mov_price` decimal(5,2) DEFAULT 10.00,
  PRIMARY KEY (`movie_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if (!mysqli_query($link, $q)) {
    die('Error creating table movie_listings: ' . mysqli_error($link));
}

echo "Table movie_listings ready.<br>";

$omdbKey = OMDB_API_KEY;
$tmdbKey = defined('TMDB_API_KEY') ? TMDB_API_KEY : ($_ENV['TMDB_API_KEY'] ?? '');
if (empty($omdbKey)) die('OMDB_API_KEY is missing in .env');
if (empty($tmdbKey)) die('TMDB_API_KEY is missing in .env');

function httpGetJson($url) {
    $response = false;
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 8);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        $response = curl_exec($ch);
        curl_close($ch);
    }
    if ($response === false) {
        $ctx = stream_context_create(['http' => ['timeout' => 20]]);
        $response = @file_get_contents($url, false, $ctx);
    }
    if ($response === false) return false;
    $data = json_decode($response, true);
    return is_array($data) ? $data : false;
}

function omdbFetch($imdbId, $omdbKey) {
    $url = 'https://www.omdbapi.com/?i=' . urlencode($imdbId) . '&plot=full&apikey=' . urlencode($omdbKey);
    $data = httpGetJson($url);
    if (!$data || ($data['Response'] ?? 'False') !== 'True') return false;
    return $data;
}

function tmdbGetImdbId($tmdbMovieId, $tmdbKey) {
    $url = 'https://api.themoviedb.org/3/movie/' . (int)$tmdbMovieId . '?api_key=' . urlencode($tmdbKey) . '&append_to_response=external_ids';
    $data = httpGetJson($url);
    if (!$data) return '';
    $id = $data['imdb_id'] ?? ($data['external_ids']['imdb_id'] ?? '');
    return preg_match('/^tt\d+$/i', $id) ? $id : '';
}

function tmdbGetTrailerUrl($tmdbMovieId, $tmdbKey, $fallbackTitle) {
    $url = 'https://api.themoviedb.org/3/movie/' . (int)$tmdbMovieId . '/videos?api_key=' . urlencode($tmdbKey) . '&language=en-US';
    $data = httpGetJson($url);
    if (!$data || empty($data['results']) || !is_array($data['results'])) return '';

    $yt = [];
    foreach ($data['results'] as $v) {
        if (($v['site'] ?? '') === 'YouTube' && !empty($v['key'])) {
            $yt[] = $v;
        }
    }
    if (empty($yt)) return '';

    usort($yt, function ($a, $b) {
        $score = function ($v) {
            $s = 0;
            $lang = strtolower((string)($v['iso_639_1'] ?? ''));
            $type = strtolower((string)($v['type'] ?? ''));
            $name = strtolower((string)($v['name'] ?? ''));

            if ($lang === 'en') $s += 100;
            if ($type === 'trailer') $s += 60;
            if (!empty($v['official'])) $s += 25;
            if (strpos($name, 'official') !== false) $s += 10;
            if (strpos($name, 'trailer') !== false) $s += 8;
            if (in_array($lang, ['hi','te','ta','ml','bn'], true)) $s -= 20;

            return $s;
        };
        return $score($b) <=> $score($a);
    });

    return 'https://www.youtube.com/watch?v=' . urlencode($yt[0]['key']);
}

function tmdbGetReleaseDate($tmdbMovieId, $tmdbKey) {
    $url = 'https://api.themoviedb.org/3/movie/' . (int)$tmdbMovieId . '?api_key=' . urlencode($tmdbKey) . '&language=en-US';
    $data = httpGetJson($url);
    $date = $data['release_date'] ?? '';
    return preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) ? $date : null;
}

function getImdbIdsFromFile() {
    $path = __DIR__ . '/movie_ids.txt';
    if (!file_exists($path)) return [];
    $out = [];
    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0) continue;
        if (preg_match('/^tt\d+$/i', $line)) $out[$line] = 0;
    }
    return $out;
}

function getImdbIdsFromDiscover($tmdbKey, $limit = 40, $region = 'GB') {
    $limit = max(1, min(100, (int)$limit));
    $imdbToTmdb = [];
    $seen = [];

    // Prefer cinema-oriented TMDB endpoints (theatrical windows)
    $feeds = [
        // Upcoming theatrical
        'https://api.themoviedb.org/3/movie/upcoming?api_key=' . urlencode($tmdbKey) . '&language=en-US&region=' . urlencode($region) . '&page=%d',
        // Now playing theatrical
        'https://api.themoviedb.org/3/movie/now_playing?api_key=' . urlencode($tmdbKey) . '&language=en-US&region=' . urlencode($region) . '&page=%d',
        // Fallback discover restricted to theatrical release types only
        'https://api.themoviedb.org/3/discover/movie?api_key=' . urlencode($tmdbKey) . '&language=en-US&region=' . urlencode($region) . '&sort_by=popularity.desc&include_adult=false&include_video=false&with_release_type=2|3&vote_count.gte=200&page=%d'
    ];

    foreach ($feeds as $feedPattern) {
        for ($page = 1; $page <= 10 && count($imdbToTmdb) < $limit; $page++) {
            $url = sprintf($feedPattern, $page);
            $data = httpGetJson($url);
            if (!$data || empty($data['results']) || !is_array($data['results'])) {
                continue;
            }

            foreach ($data['results'] as $m) {
                $id = (int)($m['id'] ?? 0);
                if ($id <= 0 || isset($seen[$id])) continue;
                $seen[$id] = true;

                $imdb = tmdbGetImdbId($id, $tmdbKey);
                if ($imdb !== '') {
                    $imdbToTmdb[$imdb] = $id;
                    if (count($imdbToTmdb) >= $limit) break 2;
                }
            }
        }
    }

    return $imdbToTmdb;
}

$mode = isset($_GET['mode']) ? strtolower(trim($_GET['mode'])) : 'discover';
$limit = isset($_GET['limit']) ? max(1, min(100, (int)$_GET['limit'])) : 40;
$requireComplete = !isset($_GET['require_complete']) || $_GET['require_complete'] !== '0';

$imdbToTmdb = ($mode === 'file') ? getImdbIdsFromFile() : getImdbIdsFromDiscover($tmdbKey, $limit);

echo 'Mode: ' . htmlspecialchars($mode) . ', target limit: ' . (int)$limit . ', require_complete=' . ($requireComplete ? '1' : '0') . '<br>';
if (empty($imdbToTmdb)) die('No IMDb IDs found for selected mode.');

$sql = 'INSERT INTO movie_listings (movie_id, movie_title, genre, age_rating, show1, show2, show3, theatre, further_info, `release`, img, preview, mov_price)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
ON DUPLICATE KEY UPDATE movie_title=VALUES(movie_title), genre=VALUES(genre), age_rating=VALUES(age_rating),
show1=VALUES(show1), show2=VALUES(show2), show3=VALUES(show3), theatre=VALUES(theatre), further_info=VALUES(further_info),
`release`=VALUES(`release`), img=VALUES(img), preview=VALUES(preview), mov_price=VALUES(mov_price)';
$stmt = mysqli_prepare($link, $sql);
if (!$stmt) die('Prepare failed: ' . mysqli_error($link));

$inserted = 0; $updated = 0; $failed = 0;

foreach ($imdbToTmdb as $imdbId => $tmdbId) {
    $omdb = omdbFetch($imdbId, $omdbKey);
    if (!$omdb) {
        echo 'Skip ' . htmlspecialchars($imdbId) . ' (OMDB fetch failed)<br>';
        $failed++;
        continue;
    }

    $movie_id = $omdb['imdbID'] ?? $imdbId;
    $movie_title = $omdb['Title'] ?? 'Unknown';
    $genre = $omdb['Genre'] ?? '';
    $age_rating = $omdb['Rated'] ?? '';
    $show1 = '10:00'; $show2 = '14:00'; $show3 = '18:00';
    $theatre = 'Main Theatre';
    $further_info = $omdb['Plot'] ?? '';

    $release = null;
    if ((int)$tmdbId > 0) $release = tmdbGetReleaseDate((int)$tmdbId, $tmdbKey);
    if ($release === null && !empty($omdb['Released']) && $omdb['Released'] !== 'N/A') {
        $dt = DateTime::createFromFormat('d M Y', $omdb['Released']);
        if ($dt) $release = $dt->format('Y-m-d');
    }

    $img = (!empty($omdb['Poster']) && $omdb['Poster'] !== 'N/A') ? $omdb['Poster'] : '';
    $preview = ((int)$tmdbId > 0)
        ? tmdbGetTrailerUrl((int)$tmdbId, $tmdbKey, $movie_title)
        : ('https://www.youtube.com/results?search_query=' . urlencode($movie_title . ' official trailer'));
    $mov_price = 10.00;

    if ($requireComplete) {
        if (trim((string)$movie_title) === '' || trim((string)$further_info) === '' || $release === null || $img === '') {
            echo 'Skip ' . htmlspecialchars($movie_id) . ' (incomplete data)<br>';
            $failed++;
            continue;
        }
    }

    mysqli_stmt_bind_param($stmt, 'ssssssssssssd',
        $movie_id, $movie_title, $genre, $age_rating, $show1, $show2, $show3, $theatre,
        $further_info, $release, $img, $preview, $mov_price
    );

    if (mysqli_stmt_execute($stmt)) {
        if (mysqli_stmt_affected_rows($stmt) === 1) {
            $inserted++;
            echo 'Inserted: ' . htmlspecialchars($movie_title) . ' (' . htmlspecialchars($movie_id) . ')<br>';
        } else {
            $updated++;
            echo 'Updated: ' . htmlspecialchars($movie_title) . ' (' . htmlspecialchars($movie_id) . ')<br>';
        }
    } else {
        $failed++;
        echo 'DB error for ' . htmlspecialchars($movie_id) . ': ' . htmlspecialchars(mysqli_stmt_error($stmt)) . '<br>';
    }
}

mysqli_stmt_close($stmt);
echo '<hr>Done.<br>Inserted: ' . $inserted . '<br>Updated: ' . $updated . '<br>Failed: ' . $failed . '<br>';
?>





