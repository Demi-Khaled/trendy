<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
} ?>
<?php

// Database connection parameters
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "trendy";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the ID is provided
if (isset($_GET['id'])) {
    $customernumber = $_GET['id'];

    // Delete query
    $sql = "DELETE FROM customer WHERE customerid = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $customernumber);

    if ($stmt->execute()) {
        // Redirect to customers.php after successful deletion
        header("Location: Customers.php");
        exit();
    } else {
        echo "Error deleting record: " . $conn->error;
    }

    $stmt->close();
}

$conn->close();
