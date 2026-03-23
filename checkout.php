<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config.php';
require_once 'connect_db.php';
include 'includes/navbar.php';

if (!isset($_SESSION['id'])) {
    http_response_code(403);
    echo '<div class="container mt-5"><div class="alert alert-danger">Please log in first.</div></div>';
    include 'includes/footer_element.php';
    exit();
}

if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    echo '<div class="container mt-5"><div class="alert alert-warning">Your cart is empty.</div></div>';
    include 'includes/footer_element.php';
    exit();
}

if (empty(STRIPE_PUBLISHABLE_KEY) || strpos(STRIPE_PUBLISHABLE_KEY, 'pk_test_') !== 0) {
    echo '<div class="container mt-5"><div class="alert alert-danger">Stripe publishable key not configured in .env</div></div>';
    include 'includes/footer_element.php';
    exit();
}

$bookingDate = isset($_GET['booking_date']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['booking_date'])
    ? $_GET['booking_date']
    : date('Y-m-d');

$requestedQty = isset($_GET['qty']) ? (int)$_GET['qty'] : 1;
if ($requestedQty < 1) $requestedQty = 1;
if ($requestedQty > 20) $requestedQty = 20;

foreach ($_SESSION['cart'] as $movieId => $item) {
    $_SESSION['cart'][$movieId]['quantity'] = $requestedQty;
}

$link = getDBConnection();
$total = 0.0;
$bookingDateObj = DateTime::createFromFormat('Y-m-d', $bookingDate);
$todayObj = new DateTime('today');
$errors = [];

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
        $errors[] = 'Movie not found in listings.';
        continue;
    }

    $releaseObj = !empty($row['release']) ? DateTime::createFromFormat('Y-m-d', $row['release']) : false;
    if (!$releaseObj || !$bookingDateObj) {
        $errors[] = 'Invalid release/booking date.';
        continue;
    }

    $minObj = clone $releaseObj;
    if ($minObj < $todayObj) $minObj = clone $todayObj;
    $maxObj = clone $releaseObj;
    $maxObj->modify('+2 months');

    if ($minObj > $maxObj || $bookingDateObj < $minObj || $bookingDateObj > $maxObj) {
        $errors[] = 'Selected booking date is outside allowed window for this movie.';
        continue;
    }

    $price = (float)$row['mov_price'];
    $_SESSION['cart'][$movieId]['price'] = $price;
    $_SESSION['cart'][$movieId]['quantity'] = $qty;
    $total += ($price * $qty);
}

if (!empty($errors)) {
    mysqli_close($link);
    echo '<div class="container mt-5"><div class="alert alert-danger">' . e(implode(' ', array_unique($errors))) . '</div></div>';
    include 'includes/footer_element.php';
    exit();
}

mysqli_close($link);
$amountPence = (int)round($total * 100);
?>
<div class="container mt-5 sticky-top " style="width: fit-content;"><h1 class="text-center display-4 fw-bold text-orange0">Checkout</h1></div>
<div class="container" data-bs-theme="dark">
  <h3 class="text-center col-lg-4 mx-auto text-bg-orange0">Total Amount: £<?php echo number_format($total, 2); ?></h3>
    <div class="container">
      <h3 class="text-center text-white">Payment Details</h3>
    <form class="text-center" action="charge.php" method="post">
      <input type="hidden" name="booking_date" value="<?php echo e($bookingDate); ?>">
      <input type="hidden" name="qty" value="<?php echo (int)$requestedQty; ?>">
      <script
        src="https://checkout.stripe.com/checkout.js" class="stripe-button"
        data-key="<?php echo e(STRIPE_PUBLISHABLE_KEY); ?>"
        data-amount="<?php echo $amountPence; ?>"
        data-name="EcCinema"
        data-description="Cinema booking"
        data-image="https://stripe.com/img/documentation/checkout/marketplace.png"
        data-locale="auto"
        data-currency="gbp">
      </script>
    </form>
    </div>
</div>

<?php include 'includes/footer_element.php' ?>
