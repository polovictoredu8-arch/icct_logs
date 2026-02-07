<?php

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
    $firstname = $_POST['firstname'];
    $surname = $_POST['surname'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password
    $role = 'user'; // Default role

    // Prepare SQL query
    $sql = "INSERT INTO users_tb (firstname, surname, username, password, role) VALUES (?, ?, ?, ?, ?)";

    // Prepare statement
    $stmt = $conn->prepare($sql);

    // Bind parameters
    $stmt->bind_param("sssss", $firstname, $surname, $username, $password, $role);

    // Execute query
    if ($stmt->execute()) {
        echo "New record created successfully";
        header("Location: ../index.html"); // Redirect to login page
        exit();
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    // Close statement
    $stmt->close();
}

// Close connection
$conn->close();


?>
