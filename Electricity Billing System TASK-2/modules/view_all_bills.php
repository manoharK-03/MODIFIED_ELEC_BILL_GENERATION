<?php
require_once '../config/config.php';

if (!isAdminLoggedIn()) {
    header('Location: ../authentication/admin_login.php');
    exit();
}

// Get all bills
$bills = $conn->query("SELECT * FROM bills ORDER BY bill_id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View All Bills</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body class="dashboard">
    <nav class="navbar">
        <h1>📋 All Bills</h1>
        <div class="navbar-right">
            <a href="../public/admin_dashboard.php">← Back to Dashboard</a>
        </div>
    </nav>
    
    <div class="container">
        <div class="card">
            <h2>All Generated Bills</h2>
            
            <?php if ($bills->num_rows > 0): ?>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Bill No</th>
                            <th>Service No</th>
                            <th>User Name</th>
                            <th>Units</th>
                            <th>Amount</th>
                            <th>Bill Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $bills->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['bill_no']; ?></td>
                            <td><?php echo $row['service_no']; ?></td>
                            <td><?php echo $row['user_name']; ?></td>
                            <td><?php echo $row['units_consumed']; ?></td>
                            <td>₹<?php echo number_format($row['total_amount'], 2); ?></td>
                            <td><?php echo date('d-M-Y', strtotime($row['bill_date'])); ?></td>
                            <td>
                                <span class="badge badge-<?php echo strtolower($row['status']); ?>">
                                    <?php echo $row['status']; ?>
                                </span>
                            </td>
                            <td>
                                <a href="../modules/view_bill.php?bill_no=<?php echo $row['bill_no']; ?>" 
                                   class="btn-sm btn-view" target="_blank">View</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <p style="text-align: center; padding: 40px; color: #666;">No bills generated yet.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>