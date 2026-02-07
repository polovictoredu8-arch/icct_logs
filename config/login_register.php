<?php
session_start(); // Start the session

// Database credentials
$host = "sql202.infinityfree.com";
$username = "if0_41088255";
$password = "adminicct10";
$database = "if0_41088255_icct_emp";

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Prepare SQL query - Include the 'role' in the select statement
    $sql = "SELECT id, firstname, password, role FROM users_tb WHERE username = ?";

    // Prepare statement
    $stmt = $conn->prepare($sql);

    // Bind parameters
    $stmt->bind_param("s", $username);

    // Execute query
    $stmt->execute();

    // Get result
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            // Password is correct, so start a new session
            $_SESSION['loggedin'] = true;
            $_SESSION['id'] = $row['id'];
            $_SESSION['username'] = $username;
            $_SESSION['firstname'] = $row['firstname']; // Use $row['firstname']
            $_SESSION['role'] = $row['role']; // Store the user's role
            $_SESSION['last_activity'] = time(); // ito yung sa session timeout ng mga users

            // Redirect to dashboard based on role
            if ($_SESSION['role'] == 'admin') {
                header("Location: ../admin/admin.html"); // Or whatever your admin dashboard is
            } else {
                header("Location: ../employee/dashboard.html");
            }
            exit();
        } else {
            echo "Incorrect password.";
        }
    } else {
        echo "Incorrect username.";
    }

    // Close statement
    $stmt->close();
}

// Close connection
$conn->close();
?>
