<?php
require_once '../config/config.php';

if (!isAdminLoggedIn()) {
    header('Location: ../public/admin_login.php');
    exit();
}

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize_input($_POST['username']);
    $password = sanitize_input($_POST['password']);
    $full_name = sanitize_input($_POST['full_name']);
    $email = sanitize_input($_POST['email']);
    
    // Check if username already exists
    $check = $conn->prepare("SELECT * FROM employees WHERE username = ?");
    $check->bind_param("s", $username);
    $check->execute();
    
    if ($check->get_result()->num_rows > 0) {
        $error = 'Username already exists!';
    } else {
        $sql = "INSERT INTO employees (username, password, full_name, email) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $username, $password, $full_name, $email);
        
        if ($stmt->execute()) {
            $success = "Employee added successfully!";
        } else {
            $error = 'Error adding employee: ' . $conn->error;
        }
    }
}

// Get all employees
$employees = $conn->query("SELECT * FROM employees ORDER BY employee_id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Employee</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body class="dashboard">
    <nav class="navbar">
        <h1>👨‍💼 Add Employee</h1>
        <div class="navbar-right">
            <a href="../public/admin_dashboard.php">← Back to Dashboard</a>
        </div>
    </nav>
    
    <div class="container">
        <div class="card">
            <h2>Add New Employee</h2>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label>Username *</label>
                        <input type="text" name="username" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Password *</label>
                        <input type="password" name="password" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Full Name *</label>
                        <input type="text" name="full_name" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email">
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">Add Employee</button>
            </form>
        </div>
        
        <div class="card">
            <h2>All Employees</h2>
            
            <?php if ($employees->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $employees->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['employee_id']; ?></td>
                        <td><?php echo $row['username']; ?></td>
                        <td><?php echo $row['full_name']; ?></td>
                        <td><?php echo $row['email'] ?? 'N/A'; ?></td>
                        <td><?php echo date('d-M-Y', strtotime($row['created_at'])); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
            <p style="text-align: center; padding: 40px; color: #666;">No employees added yet.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>