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

    do {
        // Category-based first digit (optional but meaningful)
        // 1 = Household, 2 = Commercial, 3 = Industry
        $prefix = (string)$category_id;

        // Generate remaining digits to make total 10 digits
        $remaining_digits = 10 - strlen($prefix);
        $random_part = '';

        for ($i = 0; $i < $remaining_digits; $i++) {
            $random_part .= random_int(0, 9);
        }

        $service_no = $prefix . $random_part;

        // Ensure uniqueness
        $check = $conn->prepare("SELECT service_no FROM users WHERE service_no = ?");
        $check->bind_param("s", $service_no);
        $check->execute();
        $result = $check->get_result();

    } while ($result->num_rows > 0);

    return $service_no;
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