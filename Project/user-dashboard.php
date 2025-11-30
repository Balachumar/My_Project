<?php
require_once 'config.php';

if (!is_logged_in()) {
    header('Location: index.php');
    exit();
}

if (is_admin()) {
    header('Location: admin-dashboard.php');
    exit();
}

$user_id = $_SESSION['user_id'];

if (isset($_GET['cancel']) && is_numeric($_GET['cancel'])) {
    $appointment_id = $_GET['cancel'];
    $sql = "UPDATE appointments SET status = 'cancelled' 
            WHERE id = $appointment_id AND user_id = $user_id";
    $conn->query($sql);
    header('Location: user-dashboard.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $first_name = sanitize_input($_POST['first_name']);
    $last_name = sanitize_input($_POST['last_name']);
    $email = sanitize_input($_POST['email']);
    $phone = sanitize_input($_POST['phone']);
    $address = sanitize_input($_POST['address']);
    
    $sql = "UPDATE users SET 
            first_name = '$first_name',
            last_name = '$last_name',
            email = '$email',
            phone = '$phone',
            address = '$address'
            WHERE id = $user_id";
    
    if ($conn->query($sql)) {
        $_SESSION['user_name'] = $first_name . ' ' . $last_name;
        $profile_success = "Profile updated successfully!";
    } else {
        $profile_error = "Failed to update profile.";
    }
}

$user_sql = "SELECT * FROM users WHERE id = $user_id";
$user_result = $conn->query($user_sql);
$user = $user_result->fetch_assoc();

$stats_sql = "SELECT 
    COUNT(CASE WHEN status != 'cancelled' AND appointment_date >= CURDATE() THEN 1 END) as upcoming,
    COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed,
    COALESCE(SUM(CASE WHEN p.payment_status = 'paid' THEN p.amount END), 0) as total_spent
FROM appointments a
LEFT JOIN payments p ON a.id = p.appointment_id
WHERE a.user_id = $user_id";
$stats_result = $conn->query($stats_sql);
$stats = $stats_result->fetch_assoc();

$dashboard_page = isset($_GET['view']) ? $_GET['view'] : 'overview';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - Elegance Salon</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="container header-container">
            <div class="logo">
                <i class="fas fa-spa"></i>
                <h1>Elegance Salon</h1>
            </div>
            <nav>
                <ul>
                    <li><a href="index.php?page=home" class="nav-link">Home</a></li>
                    <li><a href="index.php?page=services" class="nav-link">Services</a></li>
                    <li><a href="index.php?page=appointments" class="nav-link">Appointments</a></li>
                    <li><a href="index.php?page=contact" class="nav-link">Contact</a></li>
                </ul>
            </nav>
            <div class="auth-buttons">
                <span style="color: white; margin-right: 15px;">Welcome, <?php echo $_SESSION['user_name']; ?></span>
                <a href="index.php?logout=1" class="btn btn-primary">Logout</a>
            </div>
        </div>
    </header>

    <section class="page">
        <div class="dashboard">
            <div class="sidebar">
                <ul class="sidebar-menu">
                    <li><a href="?view=overview" class="dashboard-link <?php echo $dashboard_page == 'overview' ? 'active' : ''; ?>">
                        <i class="fas fa-tachometer-alt"></i> Overview</a></li>
                    <li><a href="?view=appointments" class="dashboard-link <?php echo $dashboard_page == 'appointments' ? 'active' : ''; ?>">
                        <i class="fas fa-calendar-check"></i> My Appointments</a></li>
                    <li><a href="?view=profile" class="dashboard-link <?php echo $dashboard_page == 'profile' ? 'active' : ''; ?>">
                        <i class="fas fa-user"></i> My Profile</a></li>
                    <li><a href="?view=payments" class="dashboard-link <?php echo $dashboard_page == 'payments' ? 'active' : ''; ?>">
                        <i class="fas fa-credit-card"></i> Payments</a></li>
                    <li><a href="index.php?logout=1" class="logout-link">
                        <i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </div>
            <div class="dashboard-content">
                <div class="dashboard-header">
                    <h2>User Dashboard</h2>
                    <p>Welcome back, <?php echo htmlspecialchars($user['first_name']); ?>!</p>
                </div>
                
                <?php if ($dashboard_page == 'overview'): ?>
                <div id="user-overview" class="dashboard-page active">
                    <h3>Overview</h3>
                    <div class="stats">
                        <div class="stat-card">
                            <i class="fas fa-calendar-check"></i>
                            <h3><?php echo $stats['upcoming']; ?></h3>
                            <p>Upcoming Appointments</p>
                        </div>
                        <div class="stat-card">
                            <i class="fas fa-history"></i>
                            <h3><?php echo $stats['completed']; ?></h3>
                            <p>Completed Appointments</p>
                        </div>
                        <div class="stat-card">
                            <i class="fas fa-star"></i>
                            <h3>4.8</h3>
                            <p>Average Rating</p>
                        </div>
                        <div class="stat-card">
                            <i class="fas fa-dollar-sign"></i>
                            <h3>$<?php echo number_format($stats['total_spent'], 2); ?></h3>
                            <p>Total Spent</p>
                        </div>
                    </div>
                    
                    <h3>Recent Appointments</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Service</th>
                                <th>Stylist</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $appointments_sql = "SELECT a.*, s.name as service_name, 
                                               u.first_name, u.last_name
                                               FROM appointments a
                                               JOIN services s ON a.service_id = s.id
                                               LEFT JOIN staff st ON a.staff_id = st.id
                                               LEFT JOIN users u ON st.user_id = u.id
                                               WHERE a.user_id = $user_id
                                               ORDER BY a.appointment_date DESC, a.appointment_time DESC
                                               LIMIT 5";
                            $appointments_result = $conn->query($appointments_sql);
                            
                            while ($appointment = $appointments_result->fetch_assoc()):
                                $status_class = '';
                                switch($appointment['status']) {
                                    case 'completed': $status_class = 'btn-success'; break;
                                    case 'confirmed': $status_class = 'btn-warning'; break;
                                    case 'pending': $status_class = 'btn-warning'; break;
                                    case 'cancelled': $status_class = 'btn-danger'; break;
                                }
                            ?>
                            <tr>
                                <td><?php echo date('F j, Y', strtotime($appointment['appointment_date'])); ?></td>
                                <td><?php echo htmlspecialchars($appointment['service_name']); ?></td>
                                <td><?php echo $appointment['first_name'] ? htmlspecialchars($appointment['first_name'] . ' ' . $appointment['last_name']) : 'Not Assigned'; ?></td>
                                <td><span class="btn <?php echo $status_class; ?> btn-sm"><?php echo ucfirst($appointment['status']); ?></span></td>
                                <td>
                                    <div class="action-buttons">
                                        <?php if ($appointment['status'] != 'cancelled' && $appointment['status'] != 'completed'): ?>
                                            <a href="?cancel=<?php echo $appointment['id']; ?>" 
                                               class="btn btn-danger btn-sm"
                                               onclick="return confirm('Are you sure you want to cancel this appointment?')">Cancel</a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php elseif ($dashboard_page == 'appointments'): ?>
                <div id="user-appointments" class="dashboard-page">
                    <h3>My Appointments</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Service</th>
                                <th>Stylist</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $all_appointments_sql = "SELECT a.*, s.name as service_name, 
                                                    u.first_name, u.last_name
                                                    FROM appointments a
                                                    JOIN services s ON a.service_id = s.id
                                                    LEFT JOIN staff st ON a.staff_id = st.id
                                                    LEFT JOIN users u ON st.user_id = u.id
                                                    WHERE a.user_id = $user_id
                                                    ORDER BY a.appointment_date DESC, a.appointment_time DESC";
                            $all_appointments_result = $conn->query($all_appointments_sql);
                            
                            while ($appointment = $all_appointments_result->fetch_assoc()):
                                $status_class = '';
                                switch($appointment['status']) {
                                    case 'completed': $status_class = 'btn-success'; break;
                                    case 'confirmed': $status_class = 'btn-warning'; break;
                                    case 'pending': $status_class = 'btn-warning'; break;
                                    case 'cancelled': $status_class = 'btn-danger'; break;
                                }
                            ?>
                            <tr>
                                <td><?php echo date('F j, Y', strtotime($appointment['appointment_date'])); ?></td>
                                <td><?php echo date('g:i A', strtotime($appointment['appointment_time'])); ?></td>
                                <td><?php echo htmlspecialchars($appointment['service_name']); ?></td>
                                <td><?php echo $appointment['first_name'] ? htmlspecialchars($appointment['first_name'] . ' ' . $appointment['last_name']) : 'Not Assigned'; ?></td>
                                <td><span class="btn <?php echo $status_class; ?> btn-sm"><?php echo ucfirst($appointment['status']); ?></span></td>
                                <td>
                                    <div class="action-buttons">
                                        <?php if ($appointment['status'] != 'cancelled' && $appointment['status'] != 'completed'): ?>
                                            <a href="?cancel=<?php echo $appointment['id']; ?>" 
                                               class="btn btn-danger btn-sm"
                                               onclick="return confirm('Are you sure you want to cancel this appointment?')">Cancel</a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php elseif ($dashboard_page == 'profile'): ?>
                <div id="user-profile" class="dashboard-page">
                    <h3>My Profile</h3>
                    <?php if (isset($profile_success)): ?>
                        <div class="alert alert-success"><?php echo $profile_success; ?></div>
                    <?php endif; ?>
                    <?php if (isset($profile_error)): ?>
                        <div class="alert alert-danger"><?php echo $profile_error; ?></div>
                    <?php endif; ?>
                    <form method="POST" action="">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="first_name">First Name</label>
                                <input type="text" id="first_name" name="first_name" class="form-control" 
                                       value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="last_name">Last Name</label>
                                <input type="text" id="last_name" name="last_name" class="form-control" 
                                       value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" class="form-control" 
                                   value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone</label>
                            <input type="text" id="phone" name="phone" class="form-control" 
                                   value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="address">Address</label>
                            <textarea id="address" name="address" class="form-control" rows="3"><?php echo htmlspecialchars($user['address']); ?></textarea>
                        </div>
                        <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
                    </form>
                </div>
                
                <?php elseif ($dashboard_page == 'payments'): ?>
                <div id="user-payments" class="dashboard-page">
                    <h3>Payment History</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Service</th>
                                <th>Amount</th>
                                <th>Payment Method</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $payments_sql = "SELECT p.*, s.name as service_name, a.appointment_date
                                           FROM payments p
                                           JOIN appointments a ON p.appointment_id = a.id
                                           JOIN services s ON a.service_id = s.id
                                           WHERE a.user_id = $user_id
                                           ORDER BY p.transaction_date DESC";
                            $payments_result = $conn->query($payments_sql);
                            
                            if ($payments_result->num_rows > 0):
                                while ($payment = $payments_result->fetch_assoc()):
                                    $status_class = $payment['payment_status'] == 'paid' ? 'btn-success' : 'btn-warning';
                            ?>
                            <tr>
                                <td><?php echo date('F j, Y', strtotime($payment['transaction_date'])); ?></td>
                                <td><?php echo htmlspecialchars($payment['service_name']); ?></td>
                                <td>$<?php echo number_format($payment['amount'], 2); ?></td>
                                <td><?php echo htmlspecialchars($payment['payment_method']); ?></td>
                                <td><span class="btn <?php echo $status_class; ?> btn-sm"><?php echo ucfirst($payment['payment_status']); ?></span></td>
                            </tr>
                            <?php 
                                endwhile;
                            else:
                            ?>
                            <tr>
                                <td colspan="5" style="text-align: center;">No payment history found</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <footer>
        <div class="container footer-content">
            <div class="footer-section">
                <h3>Elegance Salon</h3>
                <p>Providing premium beauty and wellness services with a touch of luxury and elegance.</p>
            </div>
            <div class="footer-section">
                <h3>Quick Links</h3>
                <a href="index.php?page=home">Home</a>
                <a href="index.php?page=services">Services</a>
                <a href="index.php?page=appointments">Appointments</a>
                <a href="index.php?page=contact">Contact</a>
            </div>
            <div class="footer-section">
                <h3>Contact Info</h3>
                <p><i class="fas fa-map-marker-alt"></i> 123 Beauty Street, Glamour City, GC 12345</p>
                <p><i class="fas fa-phone"></i> (555) 123-4567</p>
                <p><i class="fas fa-envelope"></i> info@elegancesalon.com</p>
            </div>
            <div class="footer-section">
                <h3>Business Hours</h3>
                <p>Monday - Friday: 9:00 AM - 7:00 PM</p>
                <p>Saturday: 9:00 AM - 5:00 PM</p>
                <p>Sunday: 10:00 AM - 4:00 PM</p>
            </div>
        </div>
        <div class="container footer-bottom">
            <p>&copy; 2025 Elegance Salon. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>