<?php
session_start();

if (!isset($_SESSION['id'])) {
    require 'login_tools.php';
    load();
}

include 'includes/navbar.php';
$total = 0;
$selectedDate = date('Y-m-d');
if (isset($_GET['booking_date']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['booking_date'])) {
    $selectedDate = $_GET['booking_date'];
}

if (isset($_GET['movie_id'])) {
    $movie_id = preg_replace('/[^a-zA-Z0-9_-]/', '', $_GET['movie_id']);
    $_SESSION['cart'] = array();
    $_SESSION['cart'][$movie_id] = array('quantity' => 1, 'price' => 0);
}

if (!empty($_SESSION['cart'])) {
    require 'connect_db.php';

    $q = "SELECT * FROM movie_listings WHERE movie_id IN (";
    foreach ($_SESSION['cart'] as $id => $value) {
        $safe_id = mysqli_real_escape_string($link, $id);
        $q .= "'$safe_id',";
    }
    $q = substr($q, 0, -1) . ') ORDER BY movie_id ASC';
    $r = mysqli_query($link, $q);

    if (!$r) {
        die('Error: ' . mysqli_error($link));
    }

    echo '<div class="row mt-5" data-bs-theme="dark"><div class="col-sm-4 mx-auto"><div class="card bg-black mb-3"><div class="card-header text-orange0"><h3 class="card-title fw-bolder">Booking Summary</h3></div><div class="card-body"><form action="show3.php" method="post">';

    while ($row = mysqli_fetch_array($r, MYSQLI_ASSOC)) {
        $_SESSION['cart'][$row['movie_id']]['price'] = (float)$row['mov_price'];
        $qty = (int)($_SESSION['cart'][$row['movie_id']]['quantity'] ?? 1);
        if ($qty < 1) $qty = 1;
        $_SESSION['cart'][$row['movie_id']]['quantity'] = $qty;

        $subtotal = $qty * (float)$row['mov_price'];
        $total += $subtotal;

        $releaseObj = !empty($row['release']) ? DateTime::createFromFormat('Y-m-d', $row['release']) : false;
        $todayObj = new DateTime('today');
        $minObj = $releaseObj ? clone $releaseObj : clone $todayObj;
        if ($minObj < $todayObj) $minObj = clone $todayObj;
        $maxObj = $releaseObj ? clone $releaseObj : clone $todayObj;
        $maxObj->modify('+2 months');

        if ($minObj > $maxObj) {
            echo '<div class="alert alert-warning">Booking is closed for this movie (older than 2 months from release).</div>';
            continue;
        }

        $minDate = $minObj->format('Y-m-d');
        $maxDate = $maxObj->format('Y-m-d');
        if ($selectedDate < $minDate || $selectedDate > $maxDate) {
            $selectedDate = $minDate;
        }

        echo '<ul class="list-group list-group-flush"><li class="list-group-item"><div class="form-group row"><h4 class="text-bg-black py-2">' . htmlspecialchars($row['theatre']) . '</h4><label class="col-sm-12 col-form-label">Movie Title: ' . htmlspecialchars($row['movie_title']) . '</label></div></li><li class="list-group-item"><div class="form-group row"><label class="col-sm-12 col-form-label">Starting @ ' . htmlspecialchars($row['show1']) . '</label></div></li></ul><br>';

        echo '<div class="mb-3"><label for="booking_date" class="form-label">Booking date</label><input type="date" class="form-control form-control-sm" id="booking_date" min="' . htmlspecialchars($minDate) . '" max="' . htmlspecialchars($maxDate) . '" value="' . htmlspecialchars($selectedDate) . '"><small class="text-muted">Available from ' . htmlspecialchars($minObj->format('d/m/Y')) . ' to ' . htmlspecialchars($maxObj->format('d/m/Y')) . '</small></div>';

        echo '<div class="input-group mb-3 btn-group mx-auto"><button type="button" class="btn btn-outline-yellow0" onclick="updateQuantity(-1)">-</button><input type="text" class="form-control text-center" id="quantity" value="' . $qty . '" data-price="' . (float)$row['mov_price'] . '" readonly><button type="button" class="btn btn-outline-yellow0" onclick="updateQuantity(1)">+</button></div>';
    }

    echo '<ul class="list-group list-group-flush"><li class="list-group-item"><div class="form-group row"><label class="col-sm-12 col-form-label fw-bold">To Pay: &pound <span id="total">' . number_format($total, 2) . '</span></label></div></li><li class="list-group-item"><a id="checkout-link" href="checkout.php?booking_date=' . urlencode($selectedDate) . '&qty=1"><button type="button" class="btn btn-outline-orange0 btn-block" role="button">Book Now</button></a></li></ul></form></div></div></div></div>';
} else {
    echo '<div class="container"><div class="alert alert-secondary" role="alert"><h2>No reservations have been made.</h2><a href="movie_listing.php" class="alert-link">View What\'s On Now</a></div></div>';
}

if (isset($link)) {
    mysqli_close($link);
}
?>
<script>
function updateCheckoutLink() {
    var quantityInput = document.getElementById('quantity');
    var totalSpan = document.getElementById('total');
    var bookingDateInput = document.getElementById('booking_date');
    var checkoutLink = document.getElementById('checkout-link');
    if (!quantityInput || !totalSpan || !bookingDateInput || !checkoutLink) return;

    var qty = parseInt(quantityInput.value, 10) || 1;
    var price = parseFloat(quantityInput.dataset.price || '0');
    totalSpan.innerText = (qty * price).toFixed(2);
    checkoutLink.setAttribute('href', 'checkout.php?booking_date=' + encodeURIComponent(bookingDateInput.value) + '&qty=' + encodeURIComponent(qty));
}

function updateQuantity(change) {
    var quantityInput = document.getElementById('quantity');
    if (!quantityInput) return;
    var quantity = (parseInt(quantityInput.value, 10) || 1) + change;
    if (quantity < 1) quantity = 1;
    quantityInput.value = quantity;
    updateCheckoutLink();
}

document.addEventListener('DOMContentLoaded', function() {
    var bookingDateInput = document.getElementById('booking_date');
    if (bookingDateInput) {
        bookingDateInput.addEventListener('change', updateCheckoutLink);
    }
    updateCheckoutLink();
});
</script>
<?php include 'includes/footer_element.php'; ?>

