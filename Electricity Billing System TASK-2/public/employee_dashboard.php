<?php
require_once '../config/config.php';

if (!isEmployeeLoggedIn()) {
    header('Location: ../public/employee_login.php');
    exit();
}

// Get all registered users
$users = $conn->query("
    SELECT 
        u.*, 
        c.category_name,
        COALESCE(
            (SELECT total_amount 
             FROM bills b 
             WHERE b.service_no = u.service_no 
             ORDER BY b.bill_date DESC 
             LIMIT 1),
            0
        ) AS bill_amount
    FROM users u
    JOIN categories c ON u.category_id = c.category_id
    ORDER BY u.user_id DESC
");

// Get statistics
$total_bills = $conn->query("SELECT COUNT(*) as count FROM bills")->fetch_assoc()['count'];
$pending_bills = $conn->query("SELECT COUNT(*) as count FROM bills WHERE status = 'Pending'")->fetch_assoc()['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Dashboard</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body class="dashboard">
    <nav class="navbar">
        <h1>👨‍💼 Employee Dashboard</h1>
        <div class="navbar-right">
            <span>Welcome, <?php echo $_SESSION['employee_name']; ?></span>
            <a href="../authentication/logout.php">Logout</a>
        </div>
    </nav>
    
    <div class="container">
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Bills Generated</h3>
                <div class="number"><?php echo $total_bills; ?></div>
            </div>
            
            <div class="stat-card">
                <h3>Pending Bills</h3>
                <div class="number" style="color: #f59e0b;"><?php echo $pending_bills; ?></div>
            </div>
        </div>
        
        <div class="card">
            <h2>All Registered Users</h2>
            <p style="color: #666; margin-bottom: 20px;">Click "Generate Bill" to create a bill for any user</p>
            
            <?php if ($users->num_rows > 0): ?>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Service No</th>
                            <th>Meter ID</th>
                            <th>Name</th>
                            <th>Phone</th>
                            <th>Category</th>
                            <th>Address</th>
                            <th>Bill Amount</th>
                            <th>Action</th>
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
                            <td>₹<?php echo number_format($row['bill_amount'], 2); ?></td>
                            <td>
                                <a href="../modules/generate_bill.php?meter_id=<?php echo $row['meter_id']; ?>" 
                                   class="btn-sm btn-generate">Generate Bill</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <p style="text-align: center; padding: 40px; color: #666;">No users registered yet.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>