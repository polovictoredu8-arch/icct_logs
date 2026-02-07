<?php
session_start();
header('Content-Type: application/json');
date_default_timezone_set('Asia/Manila');

$host = "sql202.infinityfree.com";
$username = "if0_41088255";
$password = "adminicct10";
$database = "if0_4108825_icct_emp";

$conn = new mysqli($host, $username, $password, $database);

if (!isset($_SESSION['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['id'];
$current_date = date('Y-m-d');
$current_time = date('H:i:s');
$day_of_week = date('l'); // Monday, Tuesday...

// Restrict to Mon-Sat
if ($day_of_week === 'Sunday') {
    echo json_encode(['status' => 'error', 'message' => 'Attendance not allowed on Sundays.']);
    exit;
}

// Check for existing record today
$sql = "SELECT * FROM attendance_tb WHERE user_id = ? AND date = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $user_id, $current_date);
$stmt->execute();
$result = $stmt->get_result();
$record = $result->fetch_assoc();

if (!$record) {
    // --- TIME IN LOGIC ---
    $status = "On Time";
    $late_minutes = 0;
    
    // Check if late (After 8:00 AM)
    $limit_time = strtotime('08:00:00');
    $now_time = strtotime($current_time);
    
    if ($now_time > $limit_time) {
        $diff = $now_time - $limit_time;
        $late_minutes = floor($diff / 60);
        $status = "Late ($late_minutes mins)";
    }

    $insert = "INSERT INTO attendance_tb (user_id, date, day_of_week, time_in, status, late_minutes) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt_ins = $conn->prepare($insert);
    $stmt_ins->bind_param("issssi", $user_id, $current_date, $day_of_week, $current_time, $status, $late_minutes);
    
    if ($stmt_ins->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Time In Recorded: ' . $status]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error']);
    }

} else {
    // --- TIME OUT LOGIC ---
    if ($record['time_out'] == NULL) {
        $update = "UPDATE attendance_tb SET time_out = ? WHERE id = ?";
        $stmt_up = $conn->prepare($update);
        $stmt_up->bind_param("si", $current_time, $record['id']);
        
        if ($stmt_up->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Time Out Recorded Successfully']);
        }
    } else {
        echo json_encode(['status' => 'info', 'message' => 'You have already timed out for today.']);
    }
}
$conn->close();
?>
