<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'electricity_billing_system');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset
$conn->set_charset("utf8mb4");

// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Helper function to sanitize input
function sanitize_input($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $conn->real_escape_string($data);
}

// Function to generate service number based on category
function generateServiceNo($category_id) {
    global $conn;
    
    $sql = "SELECT service_no FROM users WHERE category_id = ? ORDER BY user_id DESC LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $lastServiceNo = $row['service_no'];
        $parts = explode('-', $lastServiceNo);
        $newNumber = str_pad((int)$parts[1] + 1, 6, '0', STR_PAD_LEFT);
        return $category_id . '-' . $newNumber;
    } else {
        return $category_id . '-000001';
    }
}

// Function to generate bill number
function generateBillNo() {
    global $conn;
    
    $sql = "SELECT bill_no FROM bills ORDER BY bill_id DESC LIMIT 1";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $lastBillNo = $row['bill_no'];
        $parts = explode('-', $lastBillNo);
        $newNumber = str_pad((int)$parts[2] + 1, 6, '0', STR_PAD_LEFT);
        return 'BILL-' . date('Y') . '-' . $newNumber;
    } else {
        return 'BILL-' . date('Y') . '-000001';
    }
}

// Check if admin is logged in
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

// Check if employee is logged in
function isEmployeeLoggedIn() {
    return isset($_SESSION['employee_id']) && !empty($_SESSION['employee_id']);
}

// Check if user is logged in
function isUserLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}
?>