<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

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

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $customernumber = $_POST['customernumber'];
    $customername = $_POST['customername'];
    $customerphone = $_POST['customerphone'];

    // Update query
    $sql = "UPDATE customer SET customername = ?, customerphone = ? WHERE customerid = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $customername, $customerphone, $customernumber);

    if ($stmt->execute()) {
        // Redirect to customers.php after successful update
        header("Location: Customers.php");
        exit();
    } else {
        echo "Error updating record: " . $conn->error;
    }

    $stmt->close();
} else {
    // Fetch the customer data to pre-fill the form
    $customernumber = $_GET['id'];
    $sql = "SELECT customerid, customername, customerphone FROM customer WHERE customerid = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $customernumber);
    $stmt->execute();
    $result = $stmt->get_result();
    $customer = $result->fetch_assoc();
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css"> <!-- Ensure style.css exists and is linked correctly -->
    <title>Edit Customer</title>
</head>
<style>
body {
    text-align: center;
}

#edit-label {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    color: white;
    font-size: larger;
    text-align: left;
}

input {
    border-color: transparent;
    border-radius: 25px;
    margin-top: 20px;
    width: 200px;
    height: 10px;
    padding: 10px;

}
</style>

<body>
    <form method="post" action="edit_customers.php">
        <input type="hidden" name="customernumber" value="<?php echo htmlspecialchars($customer['customerid']); ?>">

        <label for="customername" id='edit-label'>Name</label>
        <input type="text" id="customername" name="customername"
            value="<?php echo htmlspecialchars($customer['customername']); ?>" required><br>

        <label for="customerphone" id='edit-label'>Phone</label>
        <input type="text" id="customerphone" name="customerphone"
            value="<?php echo htmlspecialchars($customer['customerphone']); ?>" required><br>

        <input type="submit" value="Update Customer"
            style="background-color: black; color:white;height:35px;font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size:large; padding:0px 0px 3px 0px; border-color:transparent;">
    </form>
</body>

</html>