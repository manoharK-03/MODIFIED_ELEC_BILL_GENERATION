<?php
require_once 'config.php';

if (!isUserLoggedIn()) {
    header('Location: user_login.php');
    exit();
}

$service_no = $_SESSION['service_no'];

if (isset($_POST['pay_bill'])) {

    $sql = "
        UPDATE bills
        SET status = 'Paid',
            payment_date = NOW()
        WHERE service_no = ?
          AND status = 'Pending'
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $service_no);
    $stmt->execute();
}

// Get user details
$sql = "SELECT u.*, c.category_name FROM users u 
        JOIN categories c ON u.category_id = c.category_id 
        WHERE u.service_no = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $service_no);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Get all bills
$sql = "SELECT * FROM bills WHERE service_no = ? ORDER BY bill_id DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $service_no);
$stmt->execute();
$bills = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="dashboard">
    <nav class="navbar" style="background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);">
        <h1>👤 My Dashboard</h1>
        <div class="navbar-right">
            <span>Welcome, <?php echo $_SESSION['user_name']; ?></span>
            <a href="logout.php">Logout</a>
        </div>
    </nav>
    
    <div class="container">
        <div class="card">
            <h2>Account Information</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                <div style="background: #f8f9fa; padding: 15px; border-radius: 8px;">
                    <div style="font-size: 12px; color: #666; margin-bottom: 5px;">Service Number</div>
                    <div style="font-size: 16px; font-weight: 600;"><?php echo $user['service_no']; ?></div>
                </div>
                <div style="background: #f8f9fa; padding: 15px; border-radius: 8px;">
                    <div style="font-size: 12px; color: #666; margin-bottom: 5px;">Meter ID</div>
                    <div style="font-size: 16px; font-weight: 600;"><?php echo $user['meter_id']; ?></div>
                </div>
                <div style="background: #f8f9fa; padding: 15px; border-radius: 8px;">
                    <div style="font-size: 12px; color: #666; margin-bottom: 5px;">Category</div>
                    <div style="font-size: 16px; font-weight: 600;"><?php echo $user['category_name']; ?></div>
                </div>
                <div style="background: #f8f9fa; padding: 15px; border-radius: 8px;">
                    <div style="font-size: 12px; color: #666; margin-bottom: 5px;">Phone</div>
                    <div style="font-size: 16px; font-weight: 600;"><?php echo $user['phone_number']; ?></div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <h2>My Bills</h2>
            
            <?php if ($bills->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Bill No</th>
                        <th>Bill Date</th>
                        <th>Units</th>
                        <th>Amount</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $bills->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['bill_no']; ?></td>
                        <td><?php echo date('d-M-Y', strtotime($row['bill_date'])); ?></td>
                        <td><?php echo $row['units_consumed']; ?></td>
                       <td><?php echo number_format($row['total_amount'] + $row['pending_amount'], 2); ?></td>
                        <td><?php echo date('d-M-Y', strtotime($row['due_date_without_fine'])); ?></td>
                        <td>
                            <span class="badge badge-<?php echo strtolower($row['status']); ?>">
                                <?php echo $row['status']; ?>
                            </span>
                        </td>
                        <td>
                            <a href="view_bill.php?bill_no=<?php echo $row['bill_no']; ?>" 
                               class="btn-sm btn-view" target="_blank">View</a>
                            <?php if ($row['status'] == 'Pending'): ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="bill_no" value="<?php echo $row['bill_no']; ?>">
                                    <button type="submit" name="pay_bill" 
                                            class="btn-sm" 
                                            style="background: #2ecc71; color: white; border: none; cursor: pointer;"
                                            onclick="return confirm('Confirm payment?')">Pay</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
            <p style="text-align: center; padding: 40px; color: #666;">No bills generated yet.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>