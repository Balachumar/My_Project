<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['process_payment'])) {
    if (!is_logged_in()) {
        $payment_error = "Please login to make a payment";
    } else {
        $appointment_id = sanitize_input($_POST['appointment_id']);
        $payment_method = sanitize_input($_POST['payment_method']);
        $amount = sanitize_input($_POST['amount']);
        $user_id = $_SESSION['user_id'];
        
        $verify_sql = "SELECT * FROM appointments WHERE id = $appointment_id";
        if (!is_admin()) {
            $verify_sql .= " AND user_id = $user_id";
        }
        
        $verify_result = $conn->query($verify_sql);
        
        if ($verify_result->num_rows == 0) {
            $payment_error = "Invalid appointment";
        } else {
            $check_payment_sql = "SELECT id FROM payments WHERE appointment_id = $appointment_id";
            $check_payment_result = $conn->query($check_payment_sql);
            
            if ($check_payment_result->num_rows > 0) {
                $payment_id = $check_payment_result->fetch_assoc()['id'];
                $update_sql = "UPDATE payments SET 
                              amount = $amount,
                              payment_method = '$payment_method',
                              payment_status = 'paid',
                              transaction_date = NOW()
                              WHERE id = $payment_id";
                
                if ($conn->query($update_sql)) {
                    $update_appointment_sql = "UPDATE appointments SET status = 'completed' WHERE id = $appointment_id";
                    $conn->query($update_appointment_sql);
                    
                    $payment_success = "Payment processed successfully!";
                } else {
                    $payment_error = "Failed to process payment";
                }
            } else {
                $insert_sql = "INSERT INTO payments (appointment_id, amount, payment_method, payment_status) 
                              VALUES ($appointment_id, $amount, '$payment_method', 'paid')";
                
                if ($conn->query($insert_sql)) {
                    $update_appointment_sql = "UPDATE appointments SET status = 'completed' WHERE id = $appointment_id";
                    $conn->query($update_appointment_sql);
                    
                    $payment_success = "Payment processed successfully!";
                } else {
                    $payment_error = "Failed to process payment";
                }
            }
        }
    }
}

if (isset($_GET['generate_invoice']) && is_logged_in()) {
    $payment_id = $_GET['generate_invoice'];
    $user_id = $_SESSION['user_id'];
    
    $invoice_sql = "SELECT p.*, a.appointment_date, a.appointment_time,
                   s.name as service_name, s.description as service_description,
                   CONCAT(u.first_name, ' ', u.last_name) as client_name,
                   u.email as client_email, u.phone as client_phone,
                   CONCAT(st_user.first_name, ' ', st_user.last_name) as stylist_name
                   FROM payments p
                   JOIN appointments a ON p.appointment_id = a.id
                   JOIN services s ON a.service_id = s.id
                   JOIN users u ON a.user_id = u.id
                   LEFT JOIN staff st ON a.staff_id = st.id
                   LEFT JOIN users st_user ON st.user_id = st_user.id
                   WHERE p.id = $payment_id";
    
    if (!is_admin()) {
        $invoice_sql .= " AND a.user_id = $user_id";
    }
    
    $invoice_result = $conn->query($invoice_sql);
    
    if ($invoice_result->num_rows > 0) {
        $invoice = $invoice_result->fetch_assoc();
        generateInvoice($invoice);
        exit();
    }
}

function generateInvoice($data) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Invoice #<?php echo str_pad($data['id'], 6, '0', STR_PAD_LEFT); ?></title>
        <style>
            body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
            .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #8a6d3b; padding-bottom: 20px; }
            .invoice-details { margin: 20px 0; }
            .invoice-details table { width: 100%; }
            .invoice-details td { padding: 8px; }
            .total { font-size: 24px; font-weight: bold; text-align: right; margin-top: 20px; color: #8a6d3b; }
            .footer { margin-top: 50px; text-align: center; color: #666; font-size: 12px; }
            @media print { .no-print { display: none; } }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>ELEGANCE SALON</h1>
            <p>123 Beauty Street, Glamour City, GC 12345</p>
            <p>Phone: (555) 123-4567 | Email: info@elegancesalon.com</p>
        </div>
        
        <h2>INVOICE #<?php echo str_pad($data['id'], 6, '0', STR_PAD_LEFT); ?></h2>
        
        <div class="invoice-details">
            <table>
                <tr>
                    <td><strong>Date:</strong></td>
                    <td><?php echo date('F j, Y', strtotime($data['transaction_date'])); ?></td>
                </tr>
                <tr>
                    <td><strong>Client Name:</strong></td>
                    <td><?php echo htmlspecialchars($data['client_name']); ?></td>
                </tr>
                <tr>
                    <td><strong>Email:</strong></td>
                    <td><?php echo htmlspecialchars($data['client_email']); ?></td>
                </tr>
                <tr>
                    <td><strong>Phone:</strong></td>
                    <td><?php echo htmlspecialchars($data['client_phone']); ?></td>
                </tr>
            </table>
        </div>
        
        <h3>Service Details</h3>
        <table border="1" cellpadding="10" cellspacing="0" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background-color: #f0f0f0;">
                    <th>Service</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Provider</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?php echo htmlspecialchars($data['service_name']); ?></td>
                    <td><?php echo date('F j, Y', strtotime($data['appointment_date'])); ?></td>
                    <td><?php echo date('g:i A', strtotime($data['appointment_time'])); ?></td>
                    <td><?php echo $data['stylist_name'] ? htmlspecialchars($data['stylist_name']) : 'N/A'; ?></td>
                    <td>$<?php echo number_format($data['amount'], 2); ?></td>
                </tr>
            </tbody>
        </table>
        
        <div class="total">
            Total: $<?php echo number_format($data['amount'], 2); ?>
        </div>
        
        <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($data['payment_method']); ?></p>
        <p><strong>Payment Status:</strong> <?php echo strtoupper($data['payment_status']); ?></p>
        
        <div class="footer">
            <p>Thank you for choosing Elegance Salon!</p>
            <p>We look forward to serving you again.</p>
        </div>
        
        <div class="no-print" style="margin-top: 20px; text-align: center;">
            <button onclick="window.print()">Print Invoice</button>
            <button onclick="window.close()">Close</button>
        </div>
    </body>
    </html>
    <?php
}

function renderPaymentForm($appointment) {
    ?>
    <div id="paymentModal<?php echo $appointment['id']; ?>" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close" onclick="hideModal('paymentModal<?php echo $appointment['id']; ?>')">&times;</span>
            <h2>Process Payment</h2>
            
            <div style="background: #f8f9fa; padding: 15px; border-radius: 4px; margin-bottom: 20px;">
                <p><strong>Client:</strong> <?php echo htmlspecialchars($appointment['client_name']); ?></p>
                <p><strong>Service:</strong> <?php echo htmlspecialchars($appointment['service_name']); ?></p>
                <p><strong>Date:</strong> <?php echo date('F j, Y', strtotime($appointment['appointment_date'])); ?></p>
                <p><strong>Time:</strong> <?php echo date('g:i A', strtotime($appointment['appointment_time'])); ?></p>
            </div>
            
            <form method="POST" action="">
                <input type="hidden" name="appointment_id" value="<?php echo $appointment['id']; ?>">
                
                <div class="form-group">
                    <label for="amount<?php echo $appointment['id']; ?>">Amount *</label>
                    <input type="number" 
                           id="amount<?php echo $appointment['id']; ?>" 
                           name="amount" 
                           class="form-control" 
                           step="0.01" 
                           min="0" 
                           value="<?php echo isset($appointment['suggested_amount']) ? $appointment['suggested_amount'] : ''; ?>"
                           required>
                </div>
                
                <div class="form-group">
                    <label for="payment_method<?php echo $appointment['id']; ?>">Payment Method *</label>
                    <select id="payment_method<?php echo $appointment['id']; ?>" 
                            name="payment_method" 
                            class="form-control" 
                            required>
                        <option value="">Select payment method</option>
                        <option value="Cash">Cash</option>
                        <option value="Credit Card">Credit Card</option>
                        <option value="Debit Card">Debit Card</option>
                        <option value="Mobile Payment">Mobile Payment</option>
                        <option value="Gift Card">Gift Card</option>
                    </select>
                </div>
                
                <button type="submit" name="process_payment" class="btn btn-primary">Process Payment</button>
            </form>
        </div>
    </div>
    <?php
}

function renderQuickPaymentButton($appointment_id, $amount) {
    ?>
    <button class="btn btn-success btn-sm" onclick="showModal('paymentModal<?php echo $appointment_id; ?>')">
        <i class="fas fa-dollar-sign"></i> Process Payment
    </button>
    <?php
}

function renderPaymentHistory($user_id) {
    global $conn;
    
    $payments_sql = "SELECT p.*, s.name as service_name, a.appointment_date
                     FROM payments p
                     JOIN appointments a ON p.appointment_id = a.id
                     JOIN services s ON a.service_id = s.id
                     WHERE a.user_id = $user_id
                     ORDER BY p.transaction_date DESC";
    
    $payments_result = $conn->query($payments_sql);
    ?>
    
    <h3>Payment History</h3>
    <table>
        <thead>
            <tr>
                <th>Invoice #</th>
                <th>Date</th>
                <th>Service</th>
                <th>Amount</th>
                <th>Payment Method</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($payments_result->num_rows > 0):
                while ($payment = $payments_result->fetch_assoc()):
                    $status_class = $payment['payment_status'] == 'paid' ? 'btn-success' : 'btn-warning';
            ?>
            <tr>
                <td>#<?php echo str_pad($payment['id'], 6, '0', STR_PAD_LEFT); ?></td>
                <td><?php echo date('F j, Y', strtotime($payment['transaction_date'])); ?></td>
                <td><?php echo htmlspecialchars($payment['service_name']); ?></td>
                <td>$<?php echo number_format($payment['amount'], 2); ?></td>
                <td><?php echo htmlspecialchars($payment['payment_method']); ?></td>
                <td><span class="btn <?php echo $status_class; ?> btn-sm"><?php echo ucfirst($payment['payment_status']); ?></span></td>
                <td>
                    <?php if ($payment['payment_status'] == 'paid'): ?>
                        <a href="?generate_invoice=<?php echo $payment['id']; ?>" 
                           target="_blank" 
                           class="btn btn-primary btn-sm">
                            <i class="fas fa-file-invoice"></i> View Invoice
                        </a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php 
                endwhile;
            else:
            ?>
            <tr>
                <td colspan="7" style="text-align: center;">No payment history found</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
    <?php
}
?>