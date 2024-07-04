<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
} ?>
<?php
// Database connection parameters
$servername = "localhost";
$username = "root"; // Your MySQL username
$password = ""; // Your MySQL password
$dbname = "trendy"; // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the item ID is set in the query string
if (isset($_GET['id'])) {
    $itemId = $_GET['id'];

    // SQL query to retrieve item data based on item ID
    $sql = "SELECT itemnumber, itemsize, itemmaterial, itemname, itemcolor, itemstock, itemprice FROM item WHERE itemnumber = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $itemId);
    $stmt->execute();
    $result = $stmt->get_result();
    $itemData = $result->fetch_assoc();
} else {
    // Redirect back to the inventory page if no item ID is provided
    header("Location: Inventory.php");
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Item</title>
    <link rel="stylesheet" href="style.css">
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
    <div>
        <?php if ($itemData) : ?>
            <form action="update_item.php" method="post">
                <input type="hidden" id="edit-iditem" name="itemnumber" value="<?php echo htmlspecialchars($itemData['itemnumber']); ?>">

                <label for="edit-itemname" id="edit-label">Item Name</label>
                <input type="text" id="edit-itemname" name="itemname" value="<?php echo htmlspecialchars($itemData['itemname']); ?>" style="margin-left:30px;"><br>

                <label for="edit-itemmaterial" id="edit-label">Material</label>
                <input type="text" id="edit-itemmaterial" name="itemmaterial" value="<?php echo htmlspecialchars($itemData['itemmaterial']); ?>" style="margin-left:50px;"><br>

                <label for="edit-itemsize" id="edit-label">Size</label>
                <input type="text" id="edit-itemsize" name="itemsize" value="<?php echo htmlspecialchars($itemData['itemsize']); ?>" style="margin-left:80px;"><br>

                <label for="edit-itemcolor" id="edit-label">Color</label>
                <input type="text" id="edit-itemcolor" name="itemcolor" value="<?php echo htmlspecialchars($itemData['itemcolor']); ?>" style="margin-left:75px;"><br>

                <label for="edit-itemstock" id="edit-label">Quantity in Stock</label>
                <input type="text" id="edit-itemstock" name="itemstock" value="<?php echo htmlspecialchars($itemData['itemstock']); ?>"><br>

                <label for="edit-itemprice" id="edit-label">Price per Item</label>
                <input type="text" id="edit-itemprice" name="itemprice" value="<?php echo htmlspecialchars($itemData['itemprice']); ?>" style="margin-left:40px;"><br>

                <input type="submit" value="Save Changes" style="background-color: black; color:white;height:35px;font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size:large; padding:0px 0px 3px 0px; border-color:transparent;">
            </form>
        <?php else : ?>
            <p>Item not found.</p>
        <?php endif; ?>
    </div>
</body>

</html>