<?php
// Define the data to be encoded in the QR code
$data = 'https://example.com';

// Define the URL for the API request
$apiUrl = 'https://api.qrserver.com/v1/create-qr-code/?data=' . urlencode($data) . '&size=300x300';

// Display the QR code image
echo '<img src="' . $apiUrl . '" alt="QR Code">';
?>