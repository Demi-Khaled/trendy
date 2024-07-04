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

// SQL query to retrieve data
$sql = "SELECT itemnumber, itemsize, itemmaterial, itemname, itemcolor, itemstock, itemprice FROM item";
$result = $conn->query($sql);

$inventoryData = array();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $inventoryData[] = $row;
    }
}

// Retrieve item names and sizes for the form dropdown
$sql_names = "SELECT DISTINCT itemname FROM item";
$result_names = $conn->query($sql_names);
$itemNames = array();
if ($result_names->num_rows > 0) {
    while ($row = $result_names->fetch_assoc()) {
        $itemNames[] = $row['itemname'];
    }
}

$sql_sizes = "SELECT DISTINCT itemsize FROM item";
$result_sizes = $conn->query($sql_sizes);
$itemSizes = array();
if ($result_sizes->num_rows > 0) {
    while ($row = $result_sizes->fetch_assoc()) {
        $itemSizes[] = $row['itemsize'];
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory</title>
    <link rel="stylesheet" href="style.css">
    <script>
        function editItem(id) {
            // Redirect to the edit page with the item ID
            window.location.href = 'edit_item.php?id=' + id;
        }

        function deleteItem(id) {
            if (confirm("Are you sure you want to delete this item?")) {
                var form = document.getElementById('deleteForm');
                document.getElementById('delete-iditem').value = id;
                form.submit();
            }
        }
    </script>
</head>
<style>
    input {
        border-color: #1d5488;
        border-radius: 25px;
        margin-top: 5px;
        width: 150px;
        height: 5px;
        padding: 10px;
    }
</style>

<body>
    <div>
        <nav>
            <img src="logo.png" alt="logo" id="logo">
            <ul>
                <li><a href="Home page.php">Home</a></li>
                <li><a href="Inventory.php" style="color:#1d5488;"> Inventory</a></li>
                <li><a href="Orders.php">Orders</a></li>
                <li><a href="Employee.php">Employees</a></li>
                <li><a href="Customers.php">Customers</a></li>
                <li><a href="Settings.html">Settings</a></li>
                <li><a href="logout.php" style="color:red; margin-left:400px;">Logout</a></li>
            </ul>
        </nav>
    </div>
    <div>
        <table id="inventoryTable">
            <thead>
                <tr>
                    <th style="color: black;text-align: center;">Item ID</th>
                    <th style="color: black;text-align: center;">Item Name</th>
                    <th style="color: black;text-align: center;">Material</th>
                    <th style="color: black;text-align: center;">Size</th>
                    <th style="color: black;text-align: center;">Color</th>
                    <th style="color: black;text-align: center;">Quantity in Stock</th>
                    <th style="color: black;text-align: center;">Price per Item</th>
                    <th style="color: black;text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($inventoryData)) : ?>
                    <tr>
                        <td colspan="8" class="no-data" style=" text-align: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-weight: bolder;
            color: red;
            font-size: larger;">The inventory is empty</td>
                    </tr>
                <?php else : ?>
                    <?php foreach ($inventoryData as $item) : ?>
                        <tr id="row-<?php echo htmlspecialchars($item['itemnumber']); ?>">
                            <td><?php echo htmlspecialchars($item['itemnumber']); ?></td>
                            <td class="itemname"><?php echo htmlspecialchars($item['itemname']); ?></td>
                            <td class="itemmaterial"><?php echo htmlspecialchars($item['itemmaterial']); ?></td>
                            <td class="itemsize"><?php echo htmlspecialchars($item['itemsize']); ?></td>
                            <td class="itemcolor"><?php echo htmlspecialchars($item['itemcolor']); ?></td>
                            <td class="itemstock"><?php echo htmlspecialchars($item['itemstock']); ?></td>
                            <td class="itemprice"><?php echo htmlspecialchars($item['itemprice']); ?> L.E</td>
                            <td>
                                <button onclick="editItem('<?php echo htmlspecialchars($item['itemnumber']); ?>')" style="background:#1d5488; color:aliceblue; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; width:50px; border-radius:25px; border-color:transparent;cursor:pointer;">Edit</button>
                                <button onclick="deleteItem('<?php echo htmlspecialchars($item['itemnumber']); ?>')" style="background:#1d5488; color:aliceblue; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; width:60px; border-radius:25px; border-color:transparent;cursor:pointer;">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Delete Form -->
    <form id="deleteForm" action="delete_item.php" method="post" style="display:none;">
        <input type="hidden" id="delete-iditem" name="itemnumber">
    </form>

</body>

</html>