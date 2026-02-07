<?php
session_start();
// Set timezone for any timestamps
date_default_timezone_set('Asia/Manila');

$host = "sql202.infinityfree.com";
$username = "if0_41088255";
$password = "adminicct10";
$database = "if0_41088255_icct_emp";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION['id'])) {
    die("User not logged in.");
}

$user_id = $_SESSION['id'];

// 1. Handle Password Update (users_tb)
if (!empty($_POST['password'])) {
    $new_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $stmt_pw = $conn->prepare("UPDATE users_tb SET password = ? WHERE id = ?");
    $stmt_pw->bind_param("si", $new_password, $user_id);
    $stmt_pw->execute();
    $stmt_pw->close();
}

// 2. Handle Profile Picture
$profile_picture_path = null;
if (isset($_FILES['profilePicture']) && $_FILES['profilePicture']['error'] === UPLOAD_ERR_OK) {
    // Delete old image first
    $check_sql = "SELECT profile_picture FROM dash_inf WHERE user_id = ?";
    $stmt_check = $conn->prepare($check_sql);
    $stmt_check->bind_param("i", $user_id);
    $stmt_check->execute();
    $res_check = $stmt_check->get_result();
    if ($row = $res_check->fetch_assoc()) {
        if ($row['profile_picture'] && file_exists($row['profile_picture']) && strpos($row['profile_picture'], 'default') === false) {
            unlink($row['profile_picture']);
        }
    }
    $stmt_check->close();

    // Upload new
    $file = $_FILES['profilePicture'];
    $allowed = ['image/jpeg', 'image/png', 'image/jpg'];
    if (in_array($file['type'], $allowed)) {
        if (!is_dir('../img')) mkdir('../img', 0777, true);
        $filename = 'user_' . $user_id . '_' . time() . '.jpg';
        $destination = '../img/' . $filename;
        if (move_uploaded_file($file['tmp_name'], $destination)) {
            $profile_picture_path = $destination;
        }
    }
}

// 3. Update or Insert Data (Strict Logic)
$email = $_POST['email'] ?? '';
$address = $_POST['address'] ?? '';
$gender = $_POST['gender'] ?? '';
$age = $_POST['age'] ?? 0;
$dob = $_POST['date_of_birth'] ?? '';
$contact = $_POST['contact'] ?? '';
$marital = $_POST['marital_status'] ?? '';
$bio = $_POST['bio'] ?? '';

// Check if row exists
$check_query = "SELECT id FROM dash_inf WHERE user_id = ?";
$stmt_check = $conn->prepare($check_query);
$stmt_check->bind_param("i", $user_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();
$exists = $result_check->num_rows > 0;
$stmt_check->close();

if ($exists) {
    // UPDATE
    $sql = "UPDATE dash_inf SET email=?, address=?, gender=?, age=?, date_of_birth=?, contact=?, marital_status=?, bio=?";
    if ($profile_picture_path) $sql .= ", profile_picture='$profile_picture_path'";
    $sql .= " WHERE user_id=?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssissssi", $email, $address, $gender, $age, $dob, $contact, $marital, $bio, $user_id);
} else {
    // INSERT
    $sql = "INSERT INTO dash_inf (user_id, email, address, gender, age, date_of_birth, contact, marital_status, bio, profile_picture) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $pic = $profile_picture_path ? $profile_picture_path : '../img/default_profile_picture.jpg';
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isssisssss", $user_id, $email, $address, $gender, $age, $dob, $contact, $marital, $bio, $pic);
}

if ($stmt->execute()) {
    echo "Account updated successfully!";
} else {
    echo "Error: " . $stmt->error;
}

$conn->close();
?>
