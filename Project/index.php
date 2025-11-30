<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $email = sanitize_input($_POST['email']);
    $password = $_POST['password'];
    
    $sql = "SELECT * FROM users WHERE email = '$email' AND role = 'user'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            
            header('Location: user-dashboard.php');
            exit();
        } else {
            $user_error = "Invalid email or password";
        }
    } else {
        $user_error = "Invalid email or password. Use User Login tab for customer accounts.";
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['staff_login'])) {
    $email = sanitize_input($_POST['email']);
    $password = $_POST['password'];
    
    $sql = "SELECT * FROM users WHERE email = '$email' AND role = 'staff'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            
            header('Location: staff-dashboard.php');
            exit();
        } else {
            $staff_error = "Invalid staff email or password";
        }
    } else {
        $staff_error = "Invalid staff credentials. Use Staff Login tab.";
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['admin_login'])) {
    $email = sanitize_input($_POST['email']);
    $password = $_POST['password'];
    
    $sql = "SELECT * FROM users WHERE email = '$email' AND role = 'admin'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            
            header('Location: admin-dashboard.php');
            exit();
        } else {
            $admin_error = "Invalid admin email or password";
        }
    } else {
        $admin_error = "Invalid admin credentials. Use Admin Login tab.";
    }
}

// Handle User Registration
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    $first_name = sanitize_input($_POST['first_name']);
    $last_name = sanitize_input($_POST['last_name']);
    $email = sanitize_input($_POST['email']);
    $phone = sanitize_input($_POST['phone']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    $check_sql = "SELECT id FROM users WHERE email = '$email'";
    $check_result = $conn->query($check_sql);
    
    if ($check_result->num_rows > 0) {
        $reg_error = "Email already registered";
    } else {
        $sql = "INSERT INTO users (first_name, last_name, email, phone, password, role) 
                VALUES ('$first_name', '$last_name', '$email', '$phone', '$password', 'user')";
        
        if ($conn->query($sql)) {
            $reg_success = "Registration successful! You can now login.";
        } else {
            $reg_error = "Registration failed. Please try again.";
        }
    }
}

// Handle Staff Registration (NEW - DIRECT REGISTRATION)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['staff_register'])) {
    $first_name = sanitize_input($_POST['first_name']);
    $last_name = sanitize_input($_POST['last_name']);
    $email = sanitize_input($_POST['email']);
    $phone = sanitize_input($_POST['phone']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $position = sanitize_input($_POST['position']);
    $experience_years = sanitize_input($_POST['experience_years']);
    $specializations = sanitize_input($_POST['specializations']);
    $schedule = sanitize_input($_POST['schedule']);
    $staff_code = sanitize_input($_POST['staff_code']);
    
    // Staff registration secret code (change this to your own secure code)
    $correct_staff_code = "STAFF2025";
    
    if ($staff_code !== $correct_staff_code) {
        $staff_reg_error = "Invalid staff registration code! Please contact HR for the correct code.";
    } else {
        $check_sql = "SELECT id FROM users WHERE email = '$email'";
        $check_result = $conn->query($check_sql);
        
        if ($check_result->num_rows > 0) {
            $staff_reg_error = "Email already registered";
        } else {
            // Create user account
            $user_sql = "INSERT INTO users (first_name, last_name, email, phone, password, role) 
                        VALUES ('$first_name', '$last_name', '$email', '$phone', '$password', 'staff')";
            
            if ($conn->query($user_sql)) {
                $user_id = $conn->insert_id;
                
                // Create staff record
                $staff_sql = "INSERT INTO staff (user_id, position, schedule, experience_years, specializations) 
                             VALUES ($user_id, '$position', '$schedule', $experience_years, '$specializations')";
                
                if ($conn->query($staff_sql)) {
                    // Notify admins
                    $admin_sql = "SELECT id FROM users WHERE role = 'admin'";
                    $admin_result = $conn->query($admin_sql);
                    
                    while ($admin = $admin_result->fetch_assoc()) {
                        $notif_sql = "INSERT INTO notifications (user_id, type, title, message) 
                                     VALUES ({$admin['id']}, 'new_staff', 'New Staff Member', 
                                     'New staff member registered: $first_name $last_name - $position')";
                        $conn->query($notif_sql);
                    }
                    
                    $staff_reg_success = "Staff registration successful! You can now login with your credentials.";
                } else {
                    $staff_reg_error = "Failed to create staff record. Please try again.";
                }
            } else {
                $staff_reg_error = "Registration failed. Please try again.";
            }
        }
    }
}

// Handle Admin Registration
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['admin_register'])) {
    $first_name = sanitize_input($_POST['first_name']);
    $last_name = sanitize_input($_POST['last_name']);
    $email = sanitize_input($_POST['email']);
    $phone = sanitize_input($_POST['phone']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $admin_secret_key = $_POST['admin_secret_key'];
    
    $correct_secret_key = "ADMIN2024";
    
    if ($admin_secret_key !== $correct_secret_key) {
        $admin_reg_error = "Invalid admin secret key!";
    } else {
        $check_sql = "SELECT id FROM users WHERE email = '$email'";
        $check_result = $conn->query($check_sql);
        
        if ($check_result->num_rows > 0) {
            $admin_reg_error = "Email already registered";
        } else {
            $sql = "INSERT INTO users (first_name, last_name, email, phone, password, role) 
                    VALUES ('$first_name', '$last_name', '$email', '$phone', '$password', 'admin')";
            
            if ($conn->query($sql)) {
                $admin_reg_success = "Admin registration successful! You can now login as admin.";
            } else {
                $admin_reg_error = "Registration failed. Please try again.";
            }
        }
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit();
}

$page = isset($_GET['page']) ? $_GET['page'] : 'home';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Elegance Salon</title>
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
                    <li><a href="?page=home" class="nav-link <?php echo $page == 'home' ? 'active' : ''; ?>">Home</a></li>
                    <li><a href="?page=services" class="nav-link <?php echo $page == 'services' ? 'active' : ''; ?>">Services</a></li>
                    <li><a href="?page=appointments" class="nav-link <?php echo $page == 'appointments' ? 'active' : ''; ?>">Appointments</a></li>
                    <li><a href="?page=contact" class="nav-link <?php echo $page == 'contact' ? 'active' : ''; ?>">Contact</a></li>
                </ul>
            </nav>
            <div class="auth-buttons">
                <?php if (is_logged_in()): ?>
                    <span style="color: white; margin-right: 15px;">Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                    <?php if (is_admin()): ?>
                        <a href="admin-dashboard.php" class="btn btn-outline">Dashboard</a>
                    <?php elseif ($_SESSION['role'] == 'staff'): ?>
                        <a href="staff-dashboard.php" class="btn btn-outline">Dashboard</a>
                    <?php else: ?>
                        <a href="user-dashboard.php" class="btn btn-outline">Dashboard</a>
                    <?php endif; ?>
                    <a href="?logout=1" class="btn btn-primary">Logout</a>
                <?php else: ?>
                    <button class="btn btn-outline" onclick="showModal('loginModal')">Login</button>
                    <button class="btn btn-primary" onclick="showModal('registerModal')">Register</button>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <main class="container">
        <?php
        switch($page) {
            case 'services':
                include 'services-content.php';
                break;
            case 'appointments':
                include 'appointments-content.php';
                break;
            case 'contact':
                include 'contact-content.php';
                break;
            default:
                include 'home-content.php';
        }
        ?>
    </main>
        
    <footer>
        <div class="container footer-content">
            <div class="footer-section">
                <h3>Elegance Salon</h3>
                <p>Providing premium beauty and wellness services with a touch of luxury and elegance.</p>
            </div>
            <div class="footer-section">
                <h3>Quick Links</h3>
                <a href="?page=home">Home</a>
                <a href="?page=services">Services</a>
                <a href="?page=appointments">Appointments</a>
                <a href="?page=contact">Contact</a>
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

    <!-- Login Modal -->
    <div id="loginModal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close" onclick="hideModal('loginModal')">&times;</span>
            
            <div class="login-tabs">
                <button class="tab-btn active" onclick="switchLoginTab('user')">User</button>
                <button class="tab-btn" onclick="switchLoginTab('staff')">Staff</button>
                <button class="tab-btn" onclick="switchLoginTab('admin')">Admin</button>
            </div>
            
            <!-- User Login Form -->
            <div id="userLoginForm" class="login-form active">
                <h2>User Login</h2>
                <?php if (isset($user_error)): ?>
                    <div class="alert alert-danger"><?php echo $user_error; ?></div>
                <?php endif; ?>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="user_email">Email</label>
                        <input type="email" id="user_email" name="email" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="user_password">Password</label>
                        <input type="password" id="user_password" name="password" class="form-control" required>
                    </div>
                    <button type="submit" name="login" class="btn btn-primary">Login as User</button>
                    <p style="margin-top: 15px; text-align: center;">
                        Don't have an account? <a href="#" onclick="hideModal('loginModal'); showModal('registerModal');" style="color: var(--primary); font-weight: bold;">Register here</a>
                    </p>
                </form>
            </div>
            
            <!-- Staff Login Form -->
            <div id="staffLoginForm" class="login-form">
                <h2>Staff Login</h2>
                <?php if (isset($staff_error)): ?>
                    <div class="alert alert-danger"><?php echo $staff_error; ?></div>
                <?php endif; ?>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="staff_email">Staff Email</label>
                        <input type="email" id="staff_email" name="email" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="staff_password">Password</label>
                        <input type="password" id="staff_password" name="password" class="form-control" required>
                    </div>
                    <button type="submit" name="staff_login" class="btn btn-primary">Login as Staff</button>
                    <p style="margin-top: 15px; text-align: center;">
                        New staff member? <a href="#" onclick="hideModal('loginModal'); showModal('registerModal'); switchRegisterTab('staff');" style="color: var(--primary); font-weight: bold;">Register here</a>
                    </p>
                </form>
            </div>
            
            <!-- Admin Login Form -->
            <div id="adminLoginForm" class="login-form">
                <h2>Admin Login</h2>
                <?php if (isset($admin_error)): ?>
                    <div class="alert alert-danger"><?php echo $admin_error; ?></div>
                <?php endif; ?>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="admin_email">Admin Email</label>
                        <input type="email" id="admin_email" name="email" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="admin_password">Admin Password</label>
                        <input type="password" id="admin_password" name="password" class="form-control" required>
                    </div>
                    <div class="alert" style="background-color: #e7f3ff; border: 1px solid #2196F3; color: #0c5460; padding: 10px; border-radius: 4px; margin-bottom: 15px; font-size: 13px;">
                        <i class="fas fa-info-circle"></i> <strong>Default:</strong> admin@elegancesalon.com / admin123
                    </div>
                    <button type="submit" name="admin_login" class="btn btn-primary">Login as Admin</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Register Modal -->
    <div id="registerModal" class="modal" style="display: none;">
        <div class="modal-content" style="max-width: 600px;">
            <span class="close" onclick="hideModal('registerModal')">&times;</span>
            
            <div class="login-tabs">
                <button class="tab-btn active" onclick="switchRegisterTab('user')">User</button>
                <button class="tab-btn" onclick="switchRegisterTab('staff')">Staff</button>
                <button class="tab-btn" onclick="switchRegisterTab('admin')">Admin</button>
            </div>
            
            <!-- User Registration Form -->
            <div id="userRegisterForm" class="login-form active">
                <h2>User Registration</h2>
                <?php if (isset($reg_error)): ?>
                    <div class="alert alert-danger"><?php echo $reg_error; ?></div>
                <?php endif; ?>
                <?php if (isset($reg_success)): ?>
                    <div class="alert alert-success"><?php echo $reg_success; ?></div>
                <?php endif; ?>
                <form method="POST" action="">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="first_name">First Name</label>
                            <input type="text" id="first_name" name="first_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="last_name">Last Name</label>
                            <input type="text" id="last_name" name="last_name" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="reg_email">Email</label>
                        <input type="email" id="reg_email" name="email" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="reg_phone">Phone</label>
                        <input type="text" id="reg_phone" name="phone" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="reg_password">Password</label>
                        <input type="password" id="reg_password" name="password" class="form-control" minlength="6" required>
                        <small style="color: #666;">Minimum 6 characters</small>
                    </div>
                    <button type="submit" name="register" class="btn btn-primary">Register as User</button>
                    <p style="margin-top: 15px; text-align: center;">
                        Already have an account? <a href="#" onclick="hideModal('registerModal'); showModal('loginModal');" style="color: var(--primary); font-weight: bold;">Login here</a>
                    </p>
                </form>
            </div>
            
            <!-- Staff Registration Form (NEW!) -->
            <div id="staffRegisterForm" class="login-form">
                <h2>Staff Registration</h2>
                <?php if (isset($staff_reg_error)): ?>
                    <div class="alert alert-danger"><?php echo $staff_reg_error; ?></div>
                <?php endif; ?>
                <?php if (isset($staff_reg_success)): ?>
                    <div class="alert alert-success"><?php echo $staff_reg_success; ?></div>
                <?php endif; ?>
                <form method="POST" action="">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="staff_first_name">First Name *</label>
                            <input type="text" id="staff_first_name" name="first_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="staff_last_name">Last Name *</label>
                            <input type="text" id="staff_last_name" name="last_name" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="staff_reg_email">Email *</label>
                            <input type="email" id="staff_reg_email" name="email" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="staff_reg_phone">Phone *</label>
                            <input type="text" id="staff_reg_phone" name="phone" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="staff_reg_password">Password *</label>
                        <input type="password" id="staff_reg_password" name="password" class="form-control" minlength="6" required>
                        <small style="color: #666;">Minimum 6 characters</small>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="staff_position">Position *</label>
                            <select id="staff_position" name="position" class="form-control" required>
                                <option value="">Select Position</option>
                                <option value="Senior Stylist">Senior Stylist</option>
                                <option value="Stylist">Stylist</option>
                                <option value="Color Specialist">Color Specialist</option>
                                <option value="Nail Technician">Nail Technician</option>
                                <option value="Esthetician">Esthetician</option>
                                <option value="Makeup Artist">Makeup Artist</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="staff_experience">Years of Experience *</label>
                            <input type="number" id="staff_experience" name="experience_years" class="form-control" min="0" max="50" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="staff_specializations">Specializations/Skills</label>
                        <input type="text" id="staff_specializations" name="specializations" class="form-control" 
                               placeholder="e.g., Balayage, Hair Extensions, Bridal Makeup">
                    </div>
                    <div class="form-group">
                        <label for="staff_schedule">Preferred Schedule *</label>
                        <input type="text" id="staff_schedule" name="schedule" class="form-control" 
                               placeholder="e.g., Monday-Friday, 9am-5pm" required>
                    </div>
                    <div class="form-group">
                        <label for="staff_code">Staff Registration Code *</label>
                        <input type="text" id="staff_code" name="staff_code" class="form-control" required>
                        <small style="color: #666;">Contact HR/Management for the registration code</small>
                    </div>
                    <div class="alert" style="background-color: #fff3cd; border: 1px solid #ffc107; color: #856404; padding: 10px; border-radius: 4px; margin-bottom: 15px; font-size: 13px;">
                        <i class="fas fa-exclamation-triangle"></i> <strong>Note:</strong> Default registration code: STAFF2025 (Change this in production!)
                    </div>
                    <button type="submit" name="staff_register" class="btn btn-primary">Register as Staff</button>
                    <p style="margin-top: 15px; text-align: center;">
                        Already registered? <a href="#" onclick="hideModal('registerModal'); showModal('loginModal'); switchLoginTab('staff');" style="color: var(--primary); font-weight: bold;">Login here</a>
                    </p>
                </form>
            </div>
            
            <!-- Admin Registration Form -->
            <div id="adminRegisterForm" class="login-form">
                <h2>Admin Registration</h2>
                <?php if (isset($admin_reg_error)): ?>
                    <div class="alert alert-danger"><?php echo $admin_reg_error; ?></div>
                <?php endif; ?>
                <?php if (isset($admin_reg_success)): ?>
                    <div class="alert alert-success"><?php echo $admin_reg_success; ?></div>
                <?php endif; ?>
                <form method="POST" action="">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="admin_first_name">First Name</label>
                            <input type="text" id="admin_first_name" name="first_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="admin_last_name">Last Name</label>
                            <input type="text" id="admin_last_name" name="last_name" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="admin_reg_email">Email</label>
                        <input type="email" id="admin_reg_email" name="email" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="admin_reg_phone">Phone</label>
                        <input type="text" id="admin_reg_phone" name="phone" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="admin_reg_password">Password</label>
                        <input type="password" id="admin_reg_password" name="password" class="form-control" minlength="6" required>
                        <small style="color: #666;">Minimum 6 characters</small>
                    </div>
                    <div class="form-group">
                        <label for="admin_secret_key">Admin Secret Key</label>
                        <input type="text" id="admin_secret_key" name="admin_secret_key" class="form-control" required>
                        <small style="color: #666;">Contact system administrator for key</small>
                    </div>
                    <div class="alert" style="background-color: #fff3cd; border: 1px solid #ffc107; color: #856404; padding: 10px; border-radius: 4px; margin-bottom: 15px; font-size: 13px;">
                        <i class="fas fa-exclamation-triangle"></i> <strong>Note:</strong> Default key: ADMIN2024
                    </div>
                    <button type="submit" name="admin_register" class="btn btn-primary">Register as Admin</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function showModal(modalId) {
            document.getElementById(modalId).style.display = 'block';
        }
        
        function hideModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }
        
        function switchLoginTab(type) {
            const userForm = document.getElementById('userLoginForm');
            const staffForm = document.getElementById('staffLoginForm');
            const adminForm = document.getElementById('adminLoginForm');
            const tabs = document.querySelectorAll('#loginModal .tab-btn');
            
            userForm.classList.remove('active');
            staffForm.classList.remove('active');
            adminForm.classList.remove('active');
            
            tabs.forEach(tab => tab.classList.remove('active'));
            
            if (type === 'user') {
                userForm.classList.add('active');
                tabs[0].classList.add('active');
            } else if (type === 'staff') {
                staffForm.classList.add('active');
                tabs[1].classList.add('active');
            } else {
                adminForm.classList.add('active');
                tabs[2].classList.add('active');
            }
        }
        
        function switchRegisterTab(type) {
            const userForm = document.getElementById('userRegisterForm');
            const staffForm = document.getElementById('staffRegisterForm');
            const adminForm = document.getElementById('adminRegisterForm');
            const tabs = document.querySelectorAll('#registerModal .tab-btn');
            
            userForm.classList.remove('active');
            staffForm.classList.remove('active');
            adminForm.classList.remove('active');
            
            tabs.forEach(tab => tab.classList.remove('active'));
            
            if (type === 'user') {
                userForm.classList.add('active');
                tabs[0].classList.add('active');
            } else if (type === 'staff') {
                staffForm.classList.add('active');
                tabs[1].classList.add('active');
            }else {
                adminForm.classList.add('active');
                tabs[2].classList.add('active');
            }
        }
        window.onclick = function(event) {
        if (event.target.classList.contains('modal')) {
            event.target.style.display = 'none';
        }
    }
    
    // Auto-show modals if there are errors
    <?php if (isset($user_error)): ?>
        showModal('loginModal');
        switchLoginTab('user');
    <?php endif; ?>
    
    <?php if (isset($staff_error)): ?>
        showModal('loginModal');
        switchLoginTab('staff');
    <?php endif; ?>
    
    <?php if (isset($admin_error)): ?>
        showModal('loginModal');
        switchLoginTab('admin');
    <?php endif; ?>
    
    <?php if (isset($reg_error) || isset($reg_success)): ?>
        showModal('registerModal');
        switchRegisterTab('user');
    <?php endif; ?>
    
    <?php if (isset($staff_reg_error) || isset($staff_reg_success)): ?>
        showModal('registerModal');
        switchRegisterTab('staff');
    <?php endif; ?>
    
    <?php if (isset($admin_reg_error) || isset($admin_reg_success)): ?>
        showModal('registerModal');
        switchRegisterTab('admin');
    <?php endif; ?>
</script>

<style>
    .modal {
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5);
        overflow-y: auto;
    }
    .modal-content {
        background-color: white;
        margin: 3% auto;
        padding: 0;
        border-radius: 8px;
        width: 90%;
        max-width: 550px;
        position: relative;
        box-shadow: 0 5px 15px rgba(0,0,0,0.3);
    }
    .close {
        position: absolute;
        right: 20px;
        top: 15px;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
        color: #999;
        z-index: 1;
    }
    .close:hover {
        color: #000;
    }
    .login-tabs {
        display: flex;
        border-bottom: 2px solid #e0e0e0;
        background-color: #f5f5f5;
        border-radius: 8px 8px 0 0;
    }
    .tab-btn {
        flex: 1;
        padding: 15px;
        border: none;
        background: transparent;
        cursor: pointer;
        font-size: 16px;
        font-weight: 500;
        color: #666;
        transition: all 0.3s;
    }
    .tab-btn.active {
        background-color: white;
        color: var(--primary);
        border-bottom: 3px solid var(--primary);
    }
    .tab-btn:hover {
        background-color: rgba(138, 109, 59, 0.1);
    }
    .login-form {
        display: none;
        padding: 30px;
    }
    .login-form.active {
        display: block;
    }
    .login-form h2 {
        margin-top: 0;
        margin-bottom: 20px;
        color: var(--primary-dark);
    }
    .alert {
        padding: 12px;
        margin-bottom: 15px;
        border-radius: 4px;
    }
    .alert-danger {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    .alert-success {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

</style>
</body>
</html>
</parameter>