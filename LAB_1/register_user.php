<?php
require_once 'config.php';

if (!isAdminLoggedIn()) {
    header('Location: admin_login.php');
    exit();
}

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $meter_id = sanitize_input($_POST['meter_id']);
    $user_name = sanitize_input($_POST['user_name']);

// ✅ Name validation: alphabets & spaces only

    $address = sanitize_input($_POST['address']);
    $pincode = sanitize_input($_POST['pincode']);
    $phone_number = sanitize_input($_POST['phone_number']);
    $category_id = (int)$_POST['category_id'];
    
    // Validation
    if (!preg_match("/^[A-Za-z ]+$/", $user_name)) {
    $error = "Name should contain only alphabets and spaces.";
} elseif (strlen($user_name) > 32) {
    $error = "Name cannot exceed 32 characters!";
} elseif (!preg_match('/^[0-9]{10}$/', $phone_number)) {
    $error = "Phone number must be exactly 10 digits!";
} elseif (!preg_match('/^[0-9]{6}$/', $pincode)) {
    $error = "Pincode must be exactly 6 digits!";
}else {
        // Check if meter_id already exists
        $check = $conn->prepare("SELECT * FROM users WHERE meter_id = ?");
        $check->bind_param("s", $meter_id);
        $check->execute();
        
        if ($check->get_result()->num_rows > 0) {
            $error = 'Meter ID already exists!';
        } else {
            // Generate service number
            $service_no = generateServiceNo($category_id);
            
            $sql = "INSERT INTO users (service_no, meter_id, user_name, address, pincode, phone_number, category_id, prev_reading) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, 0)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssssi", $service_no, $meter_id, $user_name, $address, $pincode, $phone_number, $category_id);
            if (empty($error)) {
    if ($stmt->execute()) {
        $success = "User registered successfully! Service No: <strong>$service_no</strong>";
    } else {
        $error = "Error registering user: " . $stmt->error;
    }
}
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register User</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="dashboard">
    <nav class="navbar">
        <h1>➕ Register User</h1>
        <div class="navbar-right">
            <a href="admin_dashboard.php">← Back to Dashboard</a>
        </div>
    </nav>
    
    <div class="container">
        <div class="card">
            <h2>Register New User</h2>
            
           <?php if (isset($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<?php if (isset($success)): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>
            
            <form method="POST" action="">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label>Meter ID *</label>
                        <input type="text" name="meter_id" required placeholder="e.g., MTR-H-001">
                    </div>
                    
                    <div class="form-group">
                        <label>Category *</label>
                        <select name="category_id" required>
                            <option value="">Select Category</option>
                            <option value="1">1 - Household</option>
                            <option value="2">2 - Commercial</option>
                            <option value="3">3 - Industry</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>User Name * (Max 32 chars)</label>
                        <input type="text"
       name="user_name"
       required
       pattern="[A-Za-z ]+"
       title="Only alphabets and spaces allowed" maxlength="32">
                    </div>
                    
                    <div class="form-group">
                        <label>Phone Number * (10 digits)</label>
                        <input type="text"
       name="phone_number"
       required
       pattern="[0-9]{10}"
       maxlength="10"
       minlength="10"
       title="Phone number must be exactly 10 digits">
                    </div>
                    
                    <div class="form-group">
                        <label>Pincode * (6 digits)</label>
                        <input type="text" name="pincode" required pattern="[0-9]{6}" maxlength="6">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Address *</label>
                    <textarea name="address" required></textarea>
                </div>
                
                <button type="submit" class="btn btn-success">Register User</button>
            </form>
        </div>
    </div>
</body>
</html>