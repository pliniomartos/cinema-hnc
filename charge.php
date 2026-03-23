<?php
session_start();

require_once 'config.php';
require_once __DIR__ . '/vendor/autoload.php';
require_once 'connect_db.php';

include 'includes/navbar.php';

if (!isset($_SESSION['id'])) {
    http_response_code(403);
    die('Error: Not authorised.');
}

if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    die('Error: Cart is empty.');
}

if (!isset($_POST['stripeToken']) || $_POST['stripeToken'] === '') {
    die('Error: Payment token missing.');
}

if (empty(STRIPE_SECRET_KEY) || strpos(STRIPE_SECRET_KEY, 'sk_test_') !== 0) {
    die('Error: Stripe secret key not configured correctly in .env');
}

$token = $_POST['stripeToken'];
$bookingDate = isset($_POST['booking_date']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_POST['booking_date'])
    ? $_POST['booking_date']
    : date('Y-m-d');
$bookingDateTime = $bookingDate . ' 00:00:00';

$postedQty = isset($_POST['qty']) ? (int)$_POST['qty'] : 1;
if ($postedQty < 1) $postedQty = 1;
if ($postedQty > 20) $postedQty = 20;
foreach ($_SESSION['cart'] as $mid => $item) {
    $_SESSION['cart'][$mid]['quantity'] = $postedQty;
}

$link = getDBConnection();
$total = 0.0;
$bookingDateObj = DateTime::createFromFormat('Y-m-d', $bookingDate);
$todayObj = new DateTime('today');

foreach ($_SESSION['cart'] as $movieId => $item) {
    $qty = (int)($item['quantity'] ?? 1);
    if ($qty < 1) $qty = 1;

    $stmt = mysqli_prepare($link, "SELECT mov_price, `release` FROM movie_listings WHERE movie_id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, 's', $movieId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$row) {
        mysqli_close($link);
        die('Error: Movie not found in listings.');
    }

    $releaseObj = !empty($row['release']) ? DateTime::createFromFormat('Y-m-d', $row['release']) : false;
    if (!$releaseObj || !$bookingDateObj) {
        mysqli_close($link);
        die('Error: Invalid release/booking date.');
    }

    $minObj = clone $releaseObj;
    if ($minObj < $todayObj) $minObj = clone $todayObj;
    $maxObj = clone $releaseObj;
    $maxObj->modify('+2 months');

    if ($minObj > $maxObj || $bookingDateObj < $minObj || $bookingDateObj > $maxObj) {
        mysqli_close($link);
        die('Error: Selected booking date is outside allowed window for this movie.');
    }

    $price = (float)$row['mov_price'];
    $_SESSION['cart'][$movieId]['price'] = $price;
    $_SESSION['cart'][$movieId]['quantity'] = $qty;
    $total += ($price * $qty);
}

$amountPence = (int)round($total * 100);
if ($amountPence <= 0) {
    mysqli_close($link);
    die('Error: Invalid booking total.');
}

try {
    \Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

    $charge = \Stripe\Charge::create([
        'amount' => $amountPence,
        'currency' => 'gbp',
        'source' => $token,
        'description' => 'EcCinema booking payment',
    ]);

    if ($charge->status !== 'succeeded') {
        throw new Exception('Payment failed.');
    }

    $totalFormatted = number_format($amountPence / 100, 2, '.', '');
    $q = "INSERT INTO movie_booking (id, total, booking_date) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($link, $q);
    mysqli_stmt_bind_param($stmt, 'ids', $_SESSION['id'], $totalFormatted, $bookingDateTime);
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('MySQL query error: ' . mysqli_error($link));
    }
    mysqli_stmt_close($stmt);

    $booking_id = mysqli_insert_id($link);

    foreach ($_SESSION['cart'] as $movieId => $item) {
        $qty = (int)($item['quantity'] ?? 1);
        $price = (float)($item['price'] ?? 0);

        $ins = mysqli_prepare($link, "INSERT INTO booking_content (booking_id, movie_id, quantity, price) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($ins, 'isid', $booking_id, $movieId, $qty, $price);
        if (!mysqli_stmt_execute($ins)) {
            throw new Exception('MySQL query error: ' . mysqli_error($link));
        }
        mysqli_stmt_close($ins);
    }

    $qrData = 'Booking Reference: #EC1000' . $booking_id . ' Total Paid: £' . $totalFormatted;
    $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?data=' . urlencode($qrData) . '&size=200x200';

    echo '
    <div class="container col-sm-3 m-auto py-5 rounded-3 bg-black text-orange0 rounded-2">
        <h1 class="text-orange0">Payment successful!</h1>
        <p class="text-white">Thank you for your purchase. Your payment has been processed successfully.</p>
        <p class="text-white">Booking Reference: #EC1000' . $booking_id . '</p>
        <p class="text-white">Total Paid: &pound ' . $totalFormatted . '</p>
        <div class="container text-center"><img class="img-fluid pb-3" src="' . $qrUrl . '" alt="QR Code"></div>
    </div>';

    $_SESSION['cart'] = NULL;

} catch (Exception $e) {
    echo '
    <div class="container col-sm-4 m-auto bg-black text-orange0 rounded-2 opacity-75 p-3">
        <h1>Error</h1>
        <p class="text-white">There was an error processing your payment.</p>
        <p class="text-white small">' . e($e->getMessage()) . '</p>
        <p><a href="movie_listing.php" class="link-orange0"><strong>Please try again</strong></a></p>
    </div>';
}

mysqli_close($link);
include 'includes/footer_element.php';
?>
