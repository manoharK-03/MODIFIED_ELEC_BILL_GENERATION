<?php
require_once 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $service_no = sanitize_input($_POST['service_no']);
    
    $sql = "SELECT * FROM users WHERE service_no = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $service_no);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['user_name'] = $user['user_name'];
        $_SESSION['service_no'] = $user['service_no'];
        header('Location: user_dashboard.php');
        exit();
    } else {
        $error = 'Invalid Service Number!';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <h1>👤 User Login</h1>
                <p>Electricity Billing System</p>
            </div>
            
            <div style="background: #e3f2fd; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-size: 13px; color: #1565c0;">
                ℹ️ Enter your Service Number to view your bills
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label>Service Number</label>
                    <input type="text" name="service_no" required placeholder="e.g., 1-000001">
                </div>
                
                <button type="submit" class="btn btn-primary">Login</button>
            </form>
            
            <div class="back-link">
                <a href="index.html">← Back to Home</a>
            </div>
        </div>
    </div>
</body>
</html>