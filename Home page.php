<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <script src="calender.js"></script>
    <div>
        <nav>
            <img src="logo.png" alt="logo" id="logo">
            <ul>
                <li><a href="Home page.php" style="color:#1d5488;">Home</a></li>
                <li><a href="Inventory.php">Inventory</a></li>
                <li><a href="Orders.php">Orders</a></li>
                <li><a href="Employee.php">Employees</a></li>
                <li><a href="Customers.php">Customers</a></li>
                <li><a href="Settings.html">Settings</a></li>
                <li><a href="logout.php" style="color:red; margin-left:400px;">Logout</a></li>
            </ul>
        </nav>
    </div>

    <h2 id="greet"><span style="color: red;">Hello</span>, Admin</h2>

    <br>
    <div class="container">
        <div class="calendar">
            <div class="header">
                <div class="month">

                </div>
                <div class="btns">
                    <div class="btn today-btn">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <div class="btn prev-btn">
                        <i class="fas fa-chevron-left"></i>
                    </div>
                    <div class="btn next-btn">
                        <i class="fas fa-chevron-right"></i>
                    </div>
                </div>
            </div>
            <div class="weekdays">
                <div class="day">Sun</div>
                <div class="day">Mon</div>
                <div class="day">Tue</div>
                <div class="day">Wed</div>
                <div class="day">Thu</div>
                <div class="day">Fri</div>
                <div class="day">Sat</div>
            </div>
            <div class="days">
                <!-- lets add days using js -->
            </div>
        </div>
    </div>
    <script src="script.js"></script>
</body>

</html>