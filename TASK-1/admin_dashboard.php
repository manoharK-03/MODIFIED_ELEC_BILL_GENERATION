<?php
require_once 'config.php';

if (!isAdminLoggedIn()) {
    header('Location: admin_login.php');
    exit();
}

// Get statistics
$total_employees = $conn->query("SELECT COUNT(*) as count FROM employees")->fetch_assoc()['count'];
$total_users = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$total_bills = $conn->query("SELECT COUNT(*) as count FROM bills")->fetch_assoc()['count'];

// Get all registered users
$users = $conn->query("SELECT u.*, c.category_name FROM users u 
                       JOIN categories c ON u.category_id = c.category_id 
                       ORDER BY u.user_id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="dashboard">
    <nav class="navbar">
        <h1>🔐 Admin Dashboard</h1>
        <div class="navbar-right">
            <span>Welcome, <?php echo $_SESSION['admin_username']; ?></span>
            <a href="logout.php">Logout</a>
        </div>
    </nav>
    
    <div class="container">
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Employees</h3>
                <div class="number"><?php echo $total_employees; ?></div>
            </div>
            
            <div class="stat-card">
                <h3>Total Users</h3>
                <div class="number"><?php echo $total_users; ?></div>
            </div>
            
            <div class="stat-card">
                <h3>Total Bills</h3>
                <div class="number"><?php echo $total_bills; ?></div>
            </div>
        </div>
        
        <div class="card">
            <h2>Quick Actions</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                <a href="add_employee.php" class="btn btn-primary" style="text-align: center; text-decoration: none;">
                    👨‍💼 Add Employee
                </a>
                <a href="register_user.php" class="btn btn-success" style="text-align: center; text-decoration: none;">
                    ➕ Register User
                </a>
                <a href="view_all_bills.php" class="btn btn-primary" style="text-align: center; text-decoration: none;">
                    📋 View All Bills
                </a>
            </div>
        </div>
        
        <div class="card">
            <h2>All Registered Users</h2>
            
            <?php if ($users->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Service No</th>
                        <th>Meter ID</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Category</th>
                        <th>Address</th>
                        <th>Prev Reading</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $users->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['service_no']; ?></td>
                        <td><?php echo $row['meter_id']; ?></td>
                        <td><?php echo $row['user_name']; ?></td>
                        <td><?php echo $row['phone_number']; ?></td>
                        <td><span class="badge" style="background: #e3f2fd; color: #1565c0;"><?php echo $row['category_name']; ?></span></td>
                        <td><?php echo $row['address'] . ', ' . $row['pincode']; ?></td>
                        <td><?php echo $row['prev_reading']; ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
            <p style="text-align: center; padding: 40px; color: #666;">No users registered yet.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>