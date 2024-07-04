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

$inventoryData = array();

// SQL query to retrieve data (executed only once on page load)
$sql = "SELECT itemnumber, itemsize, itemmaterial, itemname, itemcolor, itemstock, itemprice FROM item";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $inventoryData[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Orders</title>
    <script>
    function addProduct(itemnumber, itemname, itemprice, itemsize, itemcolor, itemmaterial, itemstock) {
        const quantityInput = document.getElementById(`quantity-${itemnumber}`);
        const quantity = parseInt(quantityInput.value);

        if (quantity > itemstock) {
            alert("The stock is insufficient.");
            return;
        }

        if (quantity > 0) {
            const addedProductsTable = document.getElementById('added-products').getElementsByTagName('tbody')[0];
            let existingRow = null;

            // Check if the product already exists in the added products table
            for (let i = 0; i < addedProductsTable.rows.length; i++) {
                if (addedProductsTable.rows[i].cells[0].innerText == itemnumber) {
                    existingRow = addedProductsTable.rows[i];
                    break;
                }
            }

            if (existingRow) {
                // Update the quantity and total price if the product exists
                const existingQuantity = parseInt(existingRow.cells[5].innerText);
                const newQuantity = existingQuantity + quantity;
                existingRow.cells[5].innerText = newQuantity;
                existingRow.cells[6].innerText = (itemprice * newQuantity).toFixed(2);
            } else {
                // Add a new row if the product does not exist
                const newRow = addedProductsTable.insertRow();
                newRow.insertCell(0).innerText = itemnumber;
                newRow.insertCell(1).innerText = itemname;
                newRow.insertCell(2).innerText = itemsize;
                newRow.insertCell(3).innerText = itemcolor;
                newRow.insertCell(4).innerText = itemmaterial;
                newRow.insertCell(5).innerText = quantity;
                newRow.insertCell(6).innerText = (itemprice * quantity).toFixed(2);
                newRow.insertCell(7).innerHTML =
                    `<button onclick="deleteProduct('${itemnumber}')" style="background:#1d5488; color:aliceblue; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; width:60px; border-radius:25px; border-color:transparent;cursor:pointer;">Delete</button>`;
            }

            // Update item stock in the inventory table
            const currentStock = parseInt(document.getElementById(`stock-${itemnumber}`).innerText);
            const newStock = currentStock - quantity;
            document.getElementById(`stock-${itemnumber}`).innerText = newStock;

            updateTotalPrice();
            // Reset quantity input to 1 after adding product
            quantityInput.value = 1;
        } else {
            alert("Please enter a valid quantity.");
            return;
        }
    }

    function deleteProduct(itemnumber) {
        const addedProductsTable = document.getElementById('added-products').getElementsByTagName('tbody')[0];

        // Find the row to delete
        for (let i = 0; i < addedProductsTable.rows.length; i++) {
            if (addedProductsTable.rows[i].cells[0].innerText == itemnumber) {
                // Fetch the quantity of the product from the table
                const quantity = parseInt(addedProductsTable.rows[i].cells[5].innerText);

                // Increment item stock by the quantity being deleted
                const newStock = parseInt(document.getElementById(`stock-${itemnumber}`).innerText) + quantity;
                document.getElementById(`stock-${itemnumber}`).innerText = newStock;

                // Remove the row from the table
                addedProductsTable.deleteRow(i);
                break;
            }
        }

        updateTotalPrice();
    }

    function updateTotalPrice() {
        const addedProductsTable = document.getElementById('added-products').getElementsByTagName('tbody')[0];
        let totalPrice = 0;

        for (let i = 0; i < addedProductsTable.rows.length; i++) {
            totalPrice += parseFloat(addedProductsTable.rows[i].cells[6].innerText);
        }

        document.getElementById('total-price').innerText = totalPrice.toFixed(2) + ' L.E';
    }

    function confirmOrder() {
        const addedProductsTable = document.getElementById('added-products').getElementsByTagName('tbody')[0];
        const orderData = [];

        // Collect order data from the table
        for (let i = 0; i < addedProductsTable.rows.length; i++) {
            const cells = addedProductsTable.rows[i].cells;
            const item = {
                itemnumber: cells[0].innerText,
                itemname: cells[1].innerText,
                itemsize: cells[2].innerText,
                itemcolor: cells[3].innerText,
                itemmaterial: cells[4].innerText,
                quantity: cells[5].innerText,
                totalprice: cells[6].innerText
            };
            orderData.push(item);
        }

        // Convert order data to JSON and set it to the hidden input
        document.getElementById('order-data').value = JSON.stringify(orderData);

        // Submit the form
        document.getElementById('confirm-order-form').submit();
    }
    </script>
</head>

<body>
    <div>
        <nav>
            <img src="logo.png" alt="logo" id="logo">
            <ul>
                <li><a href="Home page.html">Home</a></li>
                <li><a href="Inventory.php">Inventory</a></li>
                <li><a href="Orders.php" style="color:#1d5488;">Orders</a></li>
                <li><a href="Employee.php">Employees</a></li>
                <li><a href="Customers.php">Customers</a></li>
                <li><a href="Settings.html">Settings</a></li>
            </ul>
        </nav>
    </div>
    <table>
        <thead>
            <tr>
                <th style="color: black;text-align: center;">Item Number</th>
                <th style="color: black;text-align: center;">Item Name</th>
                <th style="color: black;text-align: center;">Item Size</th>
                <th style="color: black;text-align: center;">Item Color</th>
                <th style="color: black;text-align: center;">Item Material</th>
                <th style="color: black;text-align: center;">Item Price</th>
                <th style="color: black;text-align: center;">Item Stock</th>
                <th style="color: black;text-align: center;">Quantity</th>
                <th style="color: black;text-align: center;">Add</th>
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
            <tr>
                <td><?= $item['itemnumber'] ?></td>
                <td><?= $item['itemname'] ?></td>
                <td><?= $item['itemsize'] ?></td>
                <td><?= $item['itemcolor'] ?></td>
                <td><?= $item['itemmaterial'] ?></td>
                <td><?= $item['itemprice'] ?> L.E</td>
                <td id="stock-<?= $item['itemnumber'] ?>"><?= $item['itemstock'] ?></td>
                <td>
                    <input type="number" id="quantity-<?= $item['itemnumber'] ?>" min="1" value="1"
                        style="border-radius:25px; border-color:transparent; color:white;background-color:#1d5488;">
                </td>
                <td>
                    <button
                        onclick="addProduct('<?= $item['itemnumber'] ?>', '<?= $item['itemname'] ?>', <?= $item['itemprice'] ?>, '<?= $item['itemsize'] ?>', '<?= $item['itemcolor'] ?>', '<?= $item['itemmaterial'] ?>', <?= $item['itemstock'] ?>)"
                        style="background:#1d5488; color:aliceblue; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; width:50px; border-radius:25px; border-color:transparent; cursor: pointer;">Add</button>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif;?>
        </tbody>
    </table>

    <table id="added-products">
        <thead>
            <tr>
                <th style="color: black;text-align: center;">Item Number</th>
                <th style="color: black;text-align: center;">Item Name</th>
                <th style="color: black;text-align: center;">Item Size</th>
                <th style="color: black;text-align: center;">Item Color</th>
                <th style="color: black;text-align: center;">Item Material</th>
                <th style="color: black;text-align: center;">Quantity</th>
                <th style="color: black;text-align: center;">Total Price</th>
                <th style="color: black;text-align: center;">Actions</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6" style="text-align:right"><b>Total Price</b></td>
                <b>
                    <td id="total-price"><b>0.00 L.E</b></td>
                </b>
            </tr>
        </tfoot>
    </table>

    <form id="confirm-order-form" action="confirm_order.php" method="post">
        <input type="hidden" name="orderData" id="order-data">
        <button type="button" onclick="confirmOrder()"
            style="background:black; color:aliceblue; font-size:large; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; border-radius:25px; border-color:transparent; cursor: pointer; margin-top: 20px; width: auto; height:auto; text-align:center; margin-left:90%;">Confirm
            order</button>
    </form>
</body>

</html>