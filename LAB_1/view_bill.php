<?php
require_once 'config.php';

$bill_no = isset($_GET['bill_no']) ? sanitize_input($_GET['bill_no']) : '';

if (!$bill_no) {
    die('Bill number is required!');
}

// Get bill details
$sql = "SELECT * FROM bills WHERE bill_no = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $bill_no);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die('Bill not found!');
}

$bill = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bill - <?php echo $bill['bill_no']; ?></title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background: #f5f7fa;
            padding: 20px;
        }
        
        .bill-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }
        
        .bill-header h1 {
            font-size: 36px;
            margin-bottom: 10px;
        }
        
        .bill-content {
            padding: 40px;
        }
        
        .consumption-table {
            width: 100%;
            border-collapse: collapse;
            margin: 30px 0;
        }
        
        .consumption-table th,
        .consumption-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .consumption-table th {
            background: #f8f9fa;
            font-weight: 600;
        }
        
        .charges-section {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 10px;
            margin: 20px 0;
        }
        
        .charge-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .charge-row:last-child {
            border-bottom: none;
        }
        
        .total-row {
            background: white;
            padding: 20px;
            margin-top: 15px;
            border-radius: 8px;
            border: 2px solid #667eea;
        }
        
        .total-row .charge-label {
            font-size: 20px;
            font-weight: 600;
        }
        
        .total-row .charge-value {
            font-size: 28px;
            font-weight: 700;
            color: #667eea;
        }
        
        .due-dates {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin: 30px 0;
        }
        
        .due-card {
            background: #fff3cd;
            padding: 20px;
            border-radius: 10px;
            border-left: 4px solid #ffc107;
        }
        
        .due-card h4 {
            color: #856404;
            margin-bottom: 10px;
        }
        
        .due-card .date {
            font-size: 20px;
            font-weight: 600;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="bill-container">
        <div class="bill-header">
            <h1>⚡ ELECTRICITY BILL</h1>
            <p>State Electricity Board</p>
        </div>
        
        <div class="bill-content">
            <div class="bill-info-grid">
                <div class="info-section">
                    <h3>Bill Information</h3>
                    <div class="info-row">
                        <span class="info-label">Bill Number:</span>
                        <span class="info-value"><?php echo $bill['bill_no']; ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Service Number:</span>
                        <span class="info-value"><?php echo $bill['service_no']; ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Meter ID:</span>
                        <span class="info-value"><?php echo $bill['meter_id']; ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Bill Date:</span>
                        <span class="info-value"><?php echo date('d-M-Y', strtotime($bill['bill_date'])); ?></span>
                    </div>
                </div>
                
                <div class="info-section">
                    <h3>Customer Details</h3>
                    <div class="info-row">
                        <span class="info-label">Name:</span>
                        <span class="info-value"><?php echo $bill['user_name']; ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Phone:</span>
                        <span class="info-value"><?php echo $bill['phone_number']; ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Address:</span>
                        <span class="info-value"><?php echo $bill['address']; ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Pincode:</span>
                        <span class="info-value"><?php echo $bill['pincode']; ?></span>
                    </div>
                </div>
            </div>
            
            <table class="consumption-table">
                <thead>
                    <tr>
                        <th>Previous Reading</th>
                        <th>Current Reading</th>
                        <th>Units Consumed</th>
                        <th>Rate per Unit</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?php echo $bill['prev_reading']; ?></td>
                        <td><?php echo $bill['curr_reading']; ?></td>
                        <td><?php echo $bill['units_consumed']; ?> Units</td>
                        <td>₹<?php echo number_format($bill['rate_per_unit'], 2); ?></td>
                    </tr>
                </tbody>
            </table>
            
           <div class="charges-section">
    <div class="charge-row">
        <span class="charge-label">Basic Charge:</span>
        <span class="charge-value">
            ₹<?php echo number_format($bill['basic_charge'], 2); ?>
        </span>
    </div>

    <div class="charge-row">
        <span class="charge-label">
            Energy Charge (<?php echo $bill['units_consumed']; ?> × ₹<?php echo number_format($bill['rate_per_unit'], 2); ?>):
        </span>
        <span class="charge-value">
            ₹<?php echo number_format($bill['energy_charge'], 2); ?>
        </span>
    </div>

    <?php if ($bill['pending_amount'] > 0): ?>
    <div class="charge-row">
        <span class="charge-label">Previous Pending Dues:</span>
        <span class="charge-value">
            ₹<?php echo number_format($bill['pending_amount'], 2); ?>
        </span>
    </div>
    <?php endif; ?>

    <div class="total-row charge-row">
        <span class="charge-label">Total Payable Amount:</span>
        <span class="charge-value">
            ₹<?php
                $grand_total = $bill['total_amount'] + $bill['pending_amount'];
                echo number_format($grand_total, 2);
            ?>
        </span>
    </div>
</div>
            
            <div class="due-dates">
                <div class="due-card">
                    <h4>Due Date (Without Fine)</h4>
                    <div class="date"><?php echo date('d-M-Y', strtotime($bill['due_date_without_fine'])); ?></div>
                </div>
                <div class="due-card" style="background: #fee2e2; border-color: #dc2626;">
                    <h4 style="color: #991b1b;">Due Date (With Fine ₹<?php echo $bill['fine_amount']; ?>)</h4>
                    <div class="date"><?php echo date('d-M-Y', strtotime($bill['due_date_with_fine'])); ?></div>
                </div>
            </div>
            
            <div style="text-align: center; margin-top: 30px;">
                <button onclick="window.print()" class="btn btn-primary" style="width: auto; padding: 15px 40px;">
                    🖨️ Print Bill
                </button>
            </div>
        </div>
    </div>
</body>
</html>