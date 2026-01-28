<?php
require_once 'config.php';

if (!isEmployeeLoggedIn()) {
    header('Location: employee_login.php');
    exit();
}

$success = $error = '';
$user_data = null;

// Get meter_id from URL if provided
$meter_id_param = isset($_GET['meter_id']) ? sanitize_input($_GET['meter_id']) : '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $meter_id = sanitize_input($_POST['meter_id']);
    $curr_reading = (int)$_POST['curr_reading'];
    
    // Get user details
    $sql = "SELECT u.*, c.rate_per_unit, c.basic_charge, c.category_name 
            FROM users u 
            JOIN categories c ON u.category_id = c.category_id 
            WHERE u.meter_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $meter_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        $error = 'Meter ID not found!';
    } else {
        $user = $result->fetch_assoc();
        $prev_reading = $user['prev_reading'];
        
        if ($curr_reading < $prev_reading) {
            $error = 'Current reading cannot be less than previous reading!';
        } else {
            // Calculate bill
            $units_consumed = $curr_reading - $prev_reading;
            $basic_charge = $user['basic_charge'];
            $rate_per_unit = $user['rate_per_unit'];
            $energy_charge = $units_consumed * $rate_per_unit;
            $total_amount = $basic_charge + $energy_charge;
            
            // Generate bill number
            $bill_no = generateBillNo();
            
            // Set dates
            $bill_date = date('Y-m-d');
            $due_date_without_fine = date('Y-m-d', strtotime('+15 days'));
            $due_date_with_fine = date('Y-m-d', strtotime('+30 days'));
            
            // Insert bill
            $sql = "INSERT INTO bills (bill_no, service_no, meter_id, user_name, address, pincode, 
                    phone_number, prev_reading, curr_reading, units_consumed, rate_per_unit, 
                    basic_charge, energy_charge, total_amount, bill_date, due_date_without_fine, 
                    due_date_with_fine, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssssiiiddddsss", 
                $bill_no, $user['service_no'], $meter_id, $user['user_name'], 
                $user['address'], $user['pincode'], $user['phone_number'], 
                $prev_reading, $curr_reading, $units_consumed, $rate_per_unit,
                $basic_charge, $energy_charge, $total_amount, 
                $bill_date, $due_date_without_fine, $due_date_with_fine);
            
            if ($stmt->execute()) {
                // Update prev_reading in users table
                $update = "UPDATE users SET prev_reading = ? WHERE meter_id = ?";
                $stmt = $conn->prepare($update);
                $stmt->bind_param("is", $curr_reading, $meter_id);
                $stmt->execute();
                
                $success = "Bill generated successfully! Bill No: <strong>$bill_no</strong>";
            } else {
                $error = 'Error generating bill: ' . $conn->error;
            }
        }
    }
}

// Pre-load user data if meter_id is in URL
if ($meter_id_param) {
    $sql = "SELECT * FROM users WHERE meter_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $meter_id_param);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $user_data = $result->fetch_assoc();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Bill</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="dashboard">
    <nav class="navbar">
        <h1>📊 Generate Bill</h1>
        <div class="navbar-right">
            <a href="employee_dashboard.php">← Back to Dashboard</a>
        </div>
    </nav>
    
    <div class="container">
        <div class="card">
            <h2>Generate Electricity Bill</h2>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label>Meter ID *</label>
                        <input type="text" name="meter_id" required 
                               value="<?php echo $user_data ? $user_data['meter_id'] : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Current Meter Reading *</label>
                        <input type="number" name="curr_reading" required min="0">
                    </div>
                </div>
                
                <?php if ($user_data): ?>
                <div style="background: #e3f2fd; padding: 20px; border-radius: 8px; margin: 20px 0;">
                    <h3 style="color: #1565c0; margin-bottom: 15px;">User Details</h3>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div>
                            <strong>Name:</strong> <?php echo $user_data['user_name']; ?>
                        </div>
                        <div>
                            <strong>Service No:</strong> <?php echo $user_data['service_no']; ?>
                        </div>
                        <div>
                            <strong>Phone:</strong> <?php echo $user_data['phone_number']; ?>
                        </div>
                        <div>
                            <strong>Previous Reading:</strong> <?php echo $user_data['prev_reading']; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <button type="submit" class="btn btn-success">Generate Bill</button>
            </form>
        </div>
    </div>
</body>
</html>