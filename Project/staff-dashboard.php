<?php
require_once 'config.php';

if (!is_logged_in()) {
    header('Location: index.php');
    exit();
}

// Check if user is staff
if ($_SESSION['role'] !== 'staff') {
    if (is_admin()) {
        header('Location: admin-dashboard.php');
    } else {
        header('Location: user-dashboard.php');
    }
    exit();
}

$user_id = $_SESSION['user_id'];

// Get staff details
$staff_sql = "SELECT s.*, u.* 
              FROM staff s 
              JOIN users u ON s.user_id = u.id 
              WHERE u.id = $user_id";
$staff_result = $conn->query($staff_sql);
$staff = $staff_result->fetch_assoc();

// Handle appointment status update
if (isset($_POST['update_appointment'])) {
    $appointment_id = sanitize_input($_POST['appointment_id']);
    $status = sanitize_input($_POST['status']);
    
    $sql = "UPDATE appointments SET status = '$status' WHERE id = $appointment_id AND staff_id = {$staff['id']}";
    if ($conn->query($sql)) {
        $success = "Appointment status updated successfully!";
    }
}

// Get today's date
$today = date('Y-m-d');

// Get staff statistics
$stats_sql = "SELECT 
    COUNT(CASE WHEN appointment_date = '$today' AND status != 'cancelled' THEN 1 END) as today_appointments,
    COUNT(CASE WHEN appointment_date = '$today' AND status = 'completed' THEN 1 END) as completed_today,
    COUNT(CASE WHEN appointment_date >= '$today' AND status = 'pending' THEN 1 END) as pending_appointments,
    COUNT(CASE WHEN status = 'completed' THEN 1 END) as total_completed
FROM appointments 
WHERE staff_id = {$staff['id']}";
$stats_result = $conn->query($stats_sql);
$stats = $stats_result->fetch_assoc();

$dashboard_page = isset($_GET['view']) ? $_GET['view'] : 'overview';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard - Elegance Salon</title>
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
                <span style="color: white; margin-right: 15px;">Staff: <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
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
                    <li><a href="?view=my-appointments" class="dashboard-link <?php echo $dashboard_page == 'my-appointments' ? 'active' : ''; ?>">
                        <i class="fas fa-calendar-check"></i> My Appointments</a></li>
                    <li><a href="?view=schedule" class="dashboard-link <?php echo $dashboard_page == 'schedule' ? 'active' : ''; ?>">
                        <i class="fas fa-clock"></i> My Schedule</a></li>
                    <li><a href="?view=profile" class="dashboard-link <?php echo $dashboard_page == 'profile' ? 'active' : ''; ?>">
                        <i class="fas fa-user"></i> Profile</a></li>
                    <li><a href="index.php?logout=1" class="logout-link">
                        <i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </div>
            
            <div class="dashboard-content">
                <div class="dashboard-header">
                    <div>
                        <h2>Staff Dashboard</h2>
                        <p>Welcome, <?php echo htmlspecialchars($staff['first_name']); ?> - <?php echo htmlspecialchars($staff['position']); ?></p>
                    </div>
                </div>

                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <?php if ($dashboard_page == 'overview'): ?>
                <div id="staff-overview" class="dashboard-page active">
                    <h3>Today's Overview</h3>
                    <div class="stats">
                        <div class="stat-card">
                            <i class="fas fa-calendar-day"></i>
                            <h3><?php echo $stats['today_appointments']; ?></h3>
                            <p>Appointments Today</p>
                        </div>
                        <div class="stat-card">
                            <i class="fas fa-check-circle"></i>
                            <h3><?php echo $stats['completed_today']; ?></h3>
                            <p>Completed Today</p>
                        </div>
                        <div class="stat-card">
                            <i class="fas fa-clock"></i>
                            <h3><?php echo $stats['pending_appointments']; ?></h3>
                            <p>Pending Appointments</p>
                        </div>
                        <div class="stat-card">
                            <i class="fas fa-star"></i>
                            <h3><?php echo $stats['total_completed']; ?></h3>
                            <p>Total Completed</p>
                        </div>
                    </div>

                    <h3>Today's Appointments</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>Client</th>
                                <th>Service</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $today_appointments_sql = "SELECT a.*, 
                                CONCAT(u.first_name, ' ', u.last_name) as client_name,
                                u.phone as client_phone,
                                s.name as service_name,
                                s.duration
                                FROM appointments a
                                JOIN users u ON a.user_id = u.id
                                JOIN services s ON a.service_id = s.id
                                WHERE a.staff_id = {$staff['id']} 
                                AND a.appointment_date = '$today'
                                AND a.status != 'cancelled'
                                ORDER BY a.appointment_time";
                            $today_result = $conn->query($today_appointments_sql);
                            
                            if ($today_result->num_rows > 0):
                                while ($app = $today_result->fetch_assoc()):
                                    $status_class = '';
                                    switch($app['status']) {
                                        case 'completed': $status_class = 'btn-success'; break;
                                        case 'confirmed': $status_class = 'btn-warning'; break;
                                        default: $status_class = 'btn-warning';
                                    }
                            ?>
                            <tr>
                                <td><?php echo date('g:i A', strtotime($app['appointment_time'])); ?></td>
                                <td>
                                    <?php echo htmlspecialchars($app['client_name']); ?>
                                    <br><small><?php echo htmlspecialchars($app['client_phone']); ?></small>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($app['service_name']); ?>
                                    <br><small><?php echo $app['duration']; ?> min</small>
                                </td>
                                <td><span class="btn <?php echo $status_class; ?> btn-sm"><?php echo ucfirst($app['status']); ?></span></td>
                                <td>
                                    <?php if ($app['status'] != 'completed'): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="appointment_id" value="<?php echo $app['id']; ?>">
                                        <select name="status" onchange="this.form.submit()" style="padding: 5px;">
                                            <option value="">Change Status</option>
                                            <option value="confirmed">Confirmed</option>
                                            <option value="completed">Completed</option>
                                        </select>
                                        <input type="hidden" name="update_appointment" value="1">
                                    </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php 
                                endwhile;
                            else:
                            ?>
                            <tr>
                                <td colspan="5" style="text-align: center;">No appointments today</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php elseif ($dashboard_page == 'my-appointments'): ?>
                <div id="my-appointments" class="dashboard-page">
                    <h3>All My Appointments</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Client</th>
                                <th>Service</th>
                                <th>Status</th>
                                <th>Notes</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $all_appointments_sql = "SELECT a.*, 
                                CONCAT(u.first_name, ' ', u.last_name) as client_name,
                                u.phone as client_phone,
                                s.name as service_name
                                FROM appointments a
                                JOIN users u ON a.user_id = u.id
                                JOIN services s ON a.service_id = s.id
                                WHERE a.staff_id = {$staff['id']}
                                AND a.appointment_date >= CURDATE()
                                ORDER BY a.appointment_date, a.appointment_time";
                            $all_result = $conn->query($all_appointments_sql);
                            
                            while ($app = $all_result->fetch_assoc()):
                                $status_class = '';
                                switch($app['status']) {
                                    case 'completed': $status_class = 'btn-success'; break;
                                    case 'confirmed': $status_class = 'btn-warning'; break;
                                    case 'pending': $status_class = 'btn-warning'; break;
                                    case 'cancelled': $status_class = 'btn-danger'; break;
                                }
                            ?>
                            <tr>
                                <td><?php echo date('M j, Y', strtotime($app['appointment_date'])); ?></td>
                                <td><?php echo date('g:i A', strtotime($app['appointment_time'])); ?></td>
                                <td>
                                    <?php echo htmlspecialchars($app['client_name']); ?>
                                    <br><small><?php echo htmlspecialchars($app['client_phone']); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($app['service_name']); ?></td>
                                <td><span class="btn <?php echo $status_class; ?> btn-sm"><?php echo ucfirst($app['status']); ?></span></td>
                                <td><?php echo htmlspecialchars($app['notes']); ?></td>
                                <td>
                                    <?php if ($app['status'] != 'completed' && $app['status'] != 'cancelled'): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="appointment_id" value="<?php echo $app['id']; ?>">
                                        <select name="status" onchange="this.form.submit()" style="padding: 5px;">
                                            <option value="">Update</option>
                                            <option value="confirmed">Confirm</option>
                                            <option value="completed">Complete</option>
                                        </select>
                                        <input type="hidden" name="update_appointment" value="1">
                                    </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <?php elseif ($dashboard_page == 'schedule'): ?>
                <div id="schedule" class="dashboard-page">
                    <h3>My Schedule</h3>
                    <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                        <p><strong>Position:</strong> <?php echo htmlspecialchars($staff['position']); ?></p>
                        <p><strong>Schedule:</strong> <?php echo htmlspecialchars($staff['schedule']); ?></p>
                    </div>

                    <h4>Upcoming Week</h4>
                    <?php
                    for ($i = 0; $i < 7; $i++):
                        $date = date('Y-m-d', strtotime("+$i days"));
                        $day_name = date('l, F j', strtotime($date));
                        
                        $day_appointments_sql = "SELECT a.*, 
                            CONCAT(u.first_name, ' ', u.last_name) as client_name,
                            s.name as service_name
                            FROM appointments a
                            JOIN users u ON a.user_id = u.id
                            JOIN services s ON a.service_id = s.id
                            WHERE a.staff_id = {$staff['id']} 
                            AND a.appointment_date = '$date'
                            AND a.status != 'cancelled'
                            ORDER BY a.appointment_time";
                        $day_result = $conn->query($day_appointments_sql);
                    ?>
                    <div style="background: white; padding: 15px; margin-bottom: 15px; border-left: 4px solid var(--primary); border-radius: 4px;">
                        <h5><?php echo $day_name; ?></h5>
                        <?php if ($day_result->num_rows > 0): ?>
                            <ul style="list-style: none; padding: 0;">
                                <?php while ($app = $day_result->fetch_assoc()): ?>
                                <li style="padding: 5px 0;">
                                    <strong><?php echo date('g:i A', strtotime($app['appointment_time'])); ?></strong> - 
                                    <?php echo htmlspecialchars($app['client_name']); ?> - 
                                    <?php echo htmlspecialchars($app['service_name']); ?>
                                    (<?php echo ucfirst($app['status']); ?>)
                                </li>
                                <?php endwhile; ?>
                            </ul>
                        <?php else: ?>
                            <p style="color: #999;">No appointments scheduled</p>
                        <?php endif; ?>
                    </div>
                    <?php endfor; ?>
                </div>

                <?php elseif ($dashboard_page == 'profile'): ?>
                <div id="profile" class="dashboard-page">
                    <h3>My Profile</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Name</label>
                            <p><?php echo htmlspecialchars($staff['first_name'] . ' ' . $staff['last_name']); ?></p>
                        </div>
                        <div class="form-group">
                            <label>Position</label>
                            <p><?php echo htmlspecialchars($staff['position']); ?></p>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <p><?php echo htmlspecialchars($staff['email']); ?></p>
                    </div>
                    <div class="form-group">
                        <label>Phone</label>
                        <p><?php echo htmlspecialchars($staff['phone']); ?></p>
                    </div>
                    <div class="form-group">
                        <label>Schedule</label>
                        <p><?php echo htmlspecialchars($staff['schedule']); ?></p>
                    </div>
                    <p style="margin-top: 20px; color: #666;">
                        <i class="fas fa-info-circle"></i> To update your profile information, please contact the administrator.
                    </p>
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