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

// Check if form is submitted for updating
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['idemp'];
    $username = $_POST['useremp'];
    $password = $_POST['passwordemp'];
    $name = $_POST['empname'];
    $email = $_POST['empemail'];
    $phone = $_POST['empphone'];
    $address = $_POST['empaddress'];


    // Update query
    $sql = "UPDATE employee SET useremp=?, passwordemp=?, empname=?, empemail=?, empphone=?, empaddress=? WHERE idemp=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssi", $username, $password, $name, $email, $phone, $address, $id);

    if ($stmt->execute()) {
        header("Location: Employee.php");
        exit();
    } else {
        echo "Error updating record: " . $conn->error;
    }

    $stmt->close();
}

// Fetch employee data for editing
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "SELECT * FROM employee WHERE idemp=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $employee = $result->fetch_assoc();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="style.css" rel="stylesheet">
    <title>Edit Employee</title>
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
    <form method="post" action="edit_employee.php">
        <input type="hidden" name="idemp" value="<?php echo $employee['idemp']; ?>">

        <label for="useremp" id="edit-label">Username</label>
        <input type="text" id="useremp" name="useremp" value="<?php echo $employee['useremp']; ?>" style="margin-left:10px;"><br>

        <label for="emppassword" id="edit-label">Password</label>
        <input type="password" id="emppassword" name="emppassword" value="<?php echo $employee['passwordemp']; ?>" style="margin-left:10px;"><br>

        <label for="empname" id="edit-label">Name</label>
        <input type="text" id="empname" name="empname" value="<?php echo $employee['empname']; ?>" style="margin-left:10px;"><br>

        <label for="empemail" id="edit-label">Email</label>
        <input type="email" id="empemail" name="empemail" value="<?php echo $employee['empemail']; ?>" style="margin-left:55px;"><br>

        <label for="empphone" id="edit-label">Phone</label>
        <input type="text" id="empphone" name="empphone" value="<?php echo $employee['empphone']; ?>" style="margin-left:50px;"><br>

        <label for="empaddress" id="edit-label">Address</label>
        <input type="text" id="empaddress" name="empaddress" value="<?php echo $employee['empaddress']; ?>" style="margin-left:35px;"><br>

        <input type="submit" value="Update" style="background-color: black; color:white;height:35px;font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size:large; padding:0px 0px 3px 0px; border-color:transparent;">
    </form>
</body>

</html>