<?php
include 'navbar.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Start the session
    session_start();

    // Retrieve form data
    $fullName = $_POST['fullName'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $description = $_POST['description'];

    // Open database connection
    require '../connect_db.php';

    // Create table if it doesn't exist
    $createTableQuery = "
        CREATE TABLE IF NOT EXISTS contact_requests (
            id INT AUTO_INCREMENT PRIMARY KEY,
            full_name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL,
            phone VARCHAR(20) NOT NULL,
            description TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ";
    mysqli_query($link, $createTableQuery);

    // Insert form data into the table
    $insertQuery = "
        INSERT INTO contact_requests (full_name, email, phone, description)
        VALUES ('$fullName', '$email', '$phone', '$description')
    ";
    mysqli_query($link, $insertQuery);

    // Close database connection
    mysqli_close($link);

    // Return a response to trigger the modal
    header('Location: contact_us_offcanvas.php?submitted=true');
    exit();
}
include 'footer_element.php';
include 'modal_thank_you.php';
?>