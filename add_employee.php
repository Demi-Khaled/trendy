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

// Check if form is submitted for adding a new employee
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['useremp'];
    $password = $_POST['emppassword'];
    $name = $_POST['empfname'];
    $job = $_POST['job'];
    $email = $_POST['empemail'];
    $phone = $_POST['empphone'];
    $address = $_POST['empaddress'];

    // Insert query
    $sql = "INSERT INTO employee (useremp,passwordemp,empname,job, empemail, empphone, empaddress) VALUES (?,?,?,?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssss", $username, $password, $name, $job, $email, $phone, $address);

    if ($stmt->execute()) {
        // Redirect to employee.php after successful insertion
        header("Location: Employee.php");
        exit();
    } else {
        echo "Error adding record: " . $conn->error;
    }

    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="style.css" rel="stylesheet">
    <title>Add Employee</title>
</head>
<style>
    #edit-label {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        color: #1d5488;
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

    form {
        margin-top: 8%;
        background-color: black;
        width: 50%;
        text-align: center;
        margin-left: 25%;
        border-radius: 25px;
    }
</style>

<body>
    <h1 style="color: aliceblue; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin:30px 0px 0px 50px">
        <span style="color:red;">New</span> employee
    </h1>
    <form method="post" action="add_employee.php">
        <label for="useremp" id="edit-label">Username</label>
        <input type="text" id="useremp" name="useremp" style="margin-left: 20px;" required><br>

        <label for="emppassword" id="edit-label">Password</label>
        <input type="password" id="emppassword" name="emppassword" style="margin-left: 30px;" required><br>

        <label for="empfname" id="edit-label">Name</label>
        <input type="text" id="empfname" name="empfname" style="margin-left: 50px;" required><br><br>

        <label for="job" id="edit-label" style="margin-right:70px;">Job</label>
        <select name="job" style="background-color: white; color:#1d5488; height: 40px; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size: large; border-radius: 25px; padding-left:15px; width:6cm;" required>
            <option value="Select job" selected disabled>Select job</option>
            <option value="Cashier">Cashier</option>
            <option value="Sales">Sales</option>
            <option value="Intern">Intern</option>
        </select>
        <br>
        <label for="empemail" id="edit-label">Email</label>
        <input type="email" id="empemail" name="empemail" style="margin-left: 60px;" required><br>

        <label for="empphone" id="edit-label">Phone</label>
        <input type="text" id="empphone" name="empphone" style="margin-left: 50px;" required><br>

        <label for="empaddress" id="edit-label">Address</label>
        <input type="text" id="empaddress" name="empaddress" style="margin-left: 40px;" required><br>

        <input type="submit" value="Add Employee" style="background-color:#1d5488; color:white; width:175px;height: 45px;
            font-size:large;
            text-align:center;
            font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;"><br><br>
    </form>
</body>

</html>