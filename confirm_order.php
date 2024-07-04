<?php
session_start();

// Redirect to login if user is not authenticated
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Establishing database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "trendy";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$orderData = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['orderData'])) {
    $orderData = json_decode($_POST['orderData'], true);
    if (!$orderData) {
        header('Location: Orders.php');
        exit;
    }
}

// Function to check if customer exists and optionally insert
function checkCustomer($conn, $name, $phone)
{
    $check_query = "SELECT * FROM customer WHERE customername=? AND customerphone=?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("ss", $name, $phone);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        // Customer already exists
        $customer = $result->fetch_assoc();
        return array("exists" => true, "customerid" => $customer['customerid']);
    } else {
        // Insert customer into database
        $insert_query = "INSERT INTO customer (customername, customerphone) VALUES (?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("ss", $name, $phone);
        if ($stmt->execute() === TRUE) {
            return array("exists" => false, "customerid" => $stmt->insert_id);
        } else {
            return array("error" => $stmt->error);
        }
    }
}

// Handling form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['customerName'], $_POST['customerPhoneNumber'])) {
    $name = $conn->real_escape_string($_POST['customerName']);
    $phone = $conn->real_escape_string($_POST['customerPhoneNumber']);

    // Check for empty fields before calling checkCustomer
    if (empty($name) || empty($phone)) {
        echo json_encode(array("error" => "Please fill in both customer name and phone number."));
        exit;
    }

    // Check or insert customer
    $customerData = checkCustomer($conn, $name, $phone);
    if (isset($customerData['error'])) {
        echo json_encode(array("error" => $customerData['error']));
        exit;
    }

    // Extract customerId from customerData
    $customerId = $customerData['customerid'];

    // Proceed with order insertion
    if (isset($_POST['Payment'], $_POST['moneyReceived'])) {
        $paymentMethod = $_POST['Payment'];
        $moneyReceived = floatval($_POST['moneyReceived']);

        $totalPrice = array_reduce($orderData, function ($sum, $item) {
            return $sum + $item['totalprice'];
        }, 0);

        $items = json_encode($orderData);
        $orderDate = date('Y-m-d H:i:s');

        // Start a transaction
        $conn->begin_transaction();

        try {
            // Insert the order
            $insert_order_query = "INSERT INTO `order` (customerid, items, totalamount, paytype, orderdate) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_order_query);
            $stmt->bind_param("isdss", $customerId, $items, $totalPrice, $paymentMethod, $orderDate);
            if ($stmt->execute() === FALSE) {
                throw new Exception("Error: " . $stmt->error);
            }

            // Update item stock
            foreach ($orderData as $item) {
                $itemNumber = $item['itemnumber'];
                $quantity = $item['quantity'];

                $update_stock_query = "UPDATE item SET itemstock = itemstock - ? WHERE itemnumber = ?";
                $stmt = $conn->prepare($update_stock_query);
                $stmt->bind_param("is", $quantity, $itemNumber);
                if ($stmt->execute() === FALSE) {
                    throw new Exception("Error: " . $stmt->error);
                }
            }

            // Commit transaction
            $conn->commit();

            // Redirect to success page
            header('Location: Orders.php');
            exit;
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            echo $e->getMessage();
        }
    }
}

// Close database connection
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Order Confirmation</title>
    <style>
        h1 {
            font-size: 30px;
            font-weight: 500;
            padding-left: 25px;
            color: rgb(255, 255, 255);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .confirmation-container {
            margin: 20px;
        }

        .confirmation-actions {
            margin-top: 20px;
        }

        .money-input-container {
            margin-top: 20px;
            display: none;
        }

        .money-input-container input {
            width: 100%;
            padding: 10px;
            margin: 5px 0;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
    </style>
    <script>
        function handlePaymentChange() {
            const paymentMethod = document.getElementById('payment').value;
            const moneyInputContainer = document.querySelector('.money-input-container');
            if (paymentMethod === 'Cash') {
                moneyInputContainer.style.display = 'block';
            } else {
                moneyInputContainer.style.display = 'none';
            }
        }

        function calculateReminder() {
            const totalPriceText = document.getElementById('total-price').innerText;
            const totalPrice = parseFloat(totalPriceText.replace(/[^\d.-]/g, ''));
            const moneyReceived = parseFloat(document.getElementById('money-received').value);
            if (!isNaN(moneyReceived)) {
                const reminder = moneyReceived - totalPrice;
                const reminderFormatted = formatCurrency(reminder);
                document.getElementById('reminder-message').innerText = reminderFormatted;
            } else {
                document.getElementById('reminder-message').innerText = formatCurrency(0);
            }
        }

        function formatCurrency(amount) {
            return amount.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,') + ' L.E';
        }

        function checkCustomer() {
            const phoneNumber = document.getElementById('phone-number').value.trim();
            const name = document.getElementById('customerName').value.trim();

            if (name === '' || phoneNumber === '') {
                alert('Please fill in both the customer name and phone number.');
                return;
            }

            fetch('confirm_order.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        customerName: name,
                        customerPhoneNumber: phoneNumber
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert(data.error); // Display specific error message
                        return;
                    }
                    if (data.exists) {
                        alert('Customer already exists!');
                    } else {
                        alert('New customer added!'); // Alert for new customer insertion
                    }
                    document.getElementById('customerId').value = data.customerId;

                    // **Testing purposes only:**
                    // To verify the function works for checking customer existence,
                    // uncomment the following line and inspect the console for the response:
                    console.log(data);
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }

        function validateForm(event) {
            const totalPriceText = document.getElementById('total-price').innerText;
            const totalPrice = parseFloat(totalPriceText.replace(/[^\d.-]/g, ''));
            const moneyReceived = parseFloat(document.getElementById('money-received').value);

            if (moneyReceived < totalPrice) {
                alert('The money received is less than the total price.');
                event.preventDefault();
                return false;
            }

            return true;
        }
    </script>
</head>

<body>
    <h1><span style="color:red;">Order</span> Confirmation</h1>

    <form id="paymentForm" method="POST" action="confirm_order.php" onsubmit="return validateForm(event)">
        <input type="hidden" name="orderData" value='<?php echo json_encode($orderData); ?>'>
        <div style="background-color:aliceblue;width:75%; border-radius: 25px;font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;margin-left:12.5%;">
            <div style="text-align: center; margin-bottom: 20px; ">
                <h2 style=" color: #1d5488;">
                    Customer Information</h2>
            </div>
            <div style="margin-bottom: 10px;">
                <input type="text" id="customerName" name="customerName" style="width: 30%; padding: 10px; border: 1px solid #1d5488; border-radius: 25px; padding-left: 20px; margin-left:25px;" placeholder="Name" required>
            </div>
            <div style="margin-bottom: 20px;">
                <input type="text" name="customerPhoneNumber" id="phone-number" style="width: 30%; padding: 10px; border: 1px solid #1d5488; border-radius: 25px; margin:0px 0px 50px 25px;padding-left: 20px;" placeholder="Phone number" required>
            </div>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Item Number</th>
                    <th>Item Name</th>
                    <th>Item Size</th>
                    <th>Item Color</th>
                    <th>Item Material</th>
                    <th>Quantity</th>
                    <th>Total Price</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orderData as $item) : ?>
                    <tr>
                        <td><?= htmlspecialchars($item['itemnumber']) ?></td>
                        <td><?= htmlspecialchars($item['itemname']) ?></td>
                        <td><?= htmlspecialchars($item['itemsize']) ?></td>
                        <td><?= htmlspecialchars($item['itemcolor']) ?></td>
                        <td><?= htmlspecialchars($item['itemmaterial']) ?></td>
                        <td><?= htmlspecialchars($item['quantity']) ?></td>
                        <td><?= htmlspecialchars($item['totalprice']) ?> L.E</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="6" style="text-align: right;"><b>Total Price</b></td>
                    <td id="total-price"><b>
                            <?php
                            $totalPrice = 0;
                            foreach ($orderData as $item) {
                                $totalPrice += $item['totalprice'];
                            }
                            echo htmlspecialchars(number_format($totalPrice, 2)) . ' L.E';
                            ?></b>
                    </td>
                </tr>
            </tfoot>
        </table>

        <div id="billing">
            <select name="Payment" id="payment" onchange="handlePaymentChange()" style="background-color: black; color: aliceblue; height: 50px; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size: large; border-radius: 25px; margin-top: 55px;padding-left:15px;">
                <option value="Select" selected disabled>Select Payment Method</option>
                <option value="Cash">Cash</option>
                <option value="Credit Card">Credit Card</option>
            </select>
        </div>

        <div class="money-input-container">
            <input type="number" id="money-received" name="moneyReceived" step="1" oninput="calculateReminder()" style="width:13.5%;border-radius: 25px;">
        </div>

        <div class="confirmation-actions">
            <button type="submit" style="background-color: black; color: white; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size: larger; border-color: transparent; border-radius: 25px; width: 15%;margin-left:42.5%;">Confirm
                Order</button>
        </div>
    </form>
    </div>
</body>

</html>