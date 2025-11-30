<?php
// Handle Staff Application Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['staff_register'])) {
    $first_name = sanitize_input($_POST['first_name']);
    $last_name = sanitize_input($_POST['last_name']);
    $email = sanitize_input($_POST['email']);
    $phone = sanitize_input($_POST['phone']);
    $position = sanitize_input($_POST['position']);
    $experience_years = sanitize_input($_POST['experience_years']);
    $specializations = sanitize_input($_POST['specializations']);
    $resume_text = sanitize_input($_POST['resume_text']);
    $references = sanitize_input($_POST['references']);
    $schedule = sanitize_input($_POST['schedule']);
    
    // Check if email already exists
    $check_sql = "SELECT id FROM users WHERE email = '$email'";
    $check_result = $conn->query($check_sql);
    
    // Check if application already exists
    $check_app_sql = "SELECT id FROM staff_applications WHERE email = '$email' AND status = 'pending'";
    $check_app_result = $conn->query($check_app_sql);
    
    if ($check_result->num_rows > 0) {
        $staff_reg_error = "Email already registered in the system";
    } elseif ($check_app_result->num_rows > 0) {
        $staff_reg_error = "You already have a pending application. Please wait for admin approval.";
    } else {
        // Insert into staff_applications table
        $sql = "INSERT INTO staff_applications 
                (first_name, last_name, email, phone, position, experience_years, 
                 specializations, resume_text, `references`, schedule, status) 
                VALUES 
                ('$first_name', '$last_name', '$email', '$phone', '$position', 
                 $experience_years, '$specializations', '$resume_text', '$references', 
                 '$schedule', 'pending')";
        
        if ($conn->query($sql)) {
            // Get all admin users
            $admin_sql = "SELECT id FROM users WHERE role = 'admin'";
            $admin_result = $conn->query($admin_sql);
            
            // Create notification for all admins
            while ($admin = $admin_result->fetch_assoc()) {
                $notif_sql = "INSERT INTO notifications (user_id, type, title, message) 
                             VALUES ({$admin['id']}, 'staff_application', 
                             'New Staff Application', 
                             'New staff application from $first_name $last_name for $position position')";
                $conn->query($notif_sql);
            }
            
            $staff_reg_success = "Application submitted successfully! We'll review your application and contact you soon.";
        } else {
            $staff_reg_error = "Application submission failed. Please try again.";
        }
    }
}
?>

<section class="page">
    <h2 class="text-center mb-20">Join Our Team</h2>
    
    <?php if (isset($staff_reg_error)): ?>
        <div class="alert alert-danger"><?php echo $staff_reg_error; ?></div>
    <?php endif; ?>
    
    <?php if (isset($staff_reg_success)): ?>
        <div class="alert alert-success"><?php echo $staff_reg_success; ?></div>
    <?php endif; ?>
    
    <div style="background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('https://images.unsplash.com/photo-1522337360788-8b13dee7a37e?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80'); background-size: cover; background-position: center; color: white; padding: 60px 30px; text-align: center; border-radius: 8px; margin-bottom: 30px;">
        <h2 style="font-size: 36px; margin-bottom: 15px;">Build Your Career with Elegance Salon</h2>
        <p style="font-size: 18px; max-width: 800px; margin: 0 auto;">Join a team of passionate beauty professionals dedicated to excellence and client satisfaction.</p>
    </div>
    
    <div class="form-row" style="align-items: flex-start; margin-bottom: 40px;">
        <div class="form-group">
            <h3>Why Work With Us?</h3>
            <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-top: 15px;">
                <div style="margin-bottom: 20px;">
                    <i class="fas fa-check-circle" style="color: var(--primary); margin-right: 10px;"></i>
                    <strong>Competitive Compensation</strong>
                    <p style="margin-left: 30px; color: #666;">Attractive salary packages and commission structure</p>
                </div>
                <div style="margin-bottom: 20px;">
                    <i class="fas fa-check-circle" style="color: var(--primary); margin-right: 10px;"></i>
                    <strong>Professional Development</strong>
                    <p style="margin-left: 30px; color: #666;">Ongoing training and certification opportunities</p>
                </div>
                <div style="margin-bottom: 20px;">
                    <i class="fas fa-check-circle" style="color: var(--primary); margin-right: 10px;"></i>
                    <strong>Modern Facilities</strong>
                    <p style="margin-left: 30px; color: #666;">State-of-the-art equipment and luxurious environment</p>
                </div>
                <div>
                    <i class="fas fa-check-circle" style="color: var(--primary); margin-right: 10px;"></i>
                    <strong>Flexible Schedule</strong>
                    <p style="margin-left: 30px; color: #666;">Work-life balance with flexible shift options</p>
                </div>
            </div>
        </div>
        
        <div class="form-group">
            <h3>Open Positions</h3>
            <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-top: 15px;">
                <ul style="list-style: none; padding: 0;">
                    <li style="padding: 10px 0; border-bottom: 1px solid #e0e0e0;">
                        <i class="fas fa-cut" style="color: var(--primary); margin-right: 10px;"></i>
                        <strong>Senior Stylist</strong>
                    </li>
                    <li style="padding: 10px 0; border-bottom: 1px solid #e0e0e0;">
                        <i class="fas fa-tint" style="color: var(--primary); margin-right: 10px;"></i>
                        <strong>Color Specialist</strong>
                    </li>
                    <li style="padding: 10px 0; border-bottom: 1px solid #e0e0e0;">
                        <i class="fas fa-hand-sparkles" style="color: var(--primary); margin-right: 10px;"></i>
                        <strong>Nail Technician</strong>
                    </li>
                    <li style="padding: 10px 0; border-bottom: 1px solid #e0e0e0;">
                        <i class="fas fa-spa" style="color: var(--primary); margin-right: 10px;"></i>
                        <strong>Esthetician</strong>
                    </li>
                    <li style="padding: 10px 0; border-bottom: 1px solid #e0e0e0;">
                        <i class="fas fa-palette" style="color: var(--primary); margin-right: 10px;"></i>
                        <strong>Makeup Artist</strong>
                    </li>
                    <li style="padding: 10px 0;">
                        <i class="fas fa-user-tie" style="color: var(--primary); margin-right: 10px;"></i>
                        <strong>Receptionist</strong>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    
    <h3 class="text-center mb-20">Apply Now</h3>
    <form method="POST" action="?page=careers" style="background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <div class="form-row">
            <div class="form-group">
                <label for="first_name">First Name *</label>
                <input type="text" id="first_name" name="first_name" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="last_name">Last Name *</label>
                <input type="text" id="last_name" name="last_name" class="form-control" required>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="email">Email Address *</label>
                <input type="email" id="email" name="email" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="phone">Phone Number *</label>
                <input type="tel" id="phone" name="phone" class="form-control" required>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="position">Position Applying For *</label>
                <select id="position" name="position" class="form-control" required>
                    <option value="">Select a position</option>
                    <option value="Senior Stylist">Senior Stylist</option>
                    <option value="Stylist">Stylist</option>
                    <option value="Color Specialist">Color Specialist</option>
                    <option value="Nail Technician">Nail Technician</option>
                    <option value="Esthetician">Esthetician</option>
                    <option value="Makeup Artist">Makeup Artist</option>
                    <option value="Receptionist">Receptionist</option>
                </select>
            </div>
            <div class="form-group">
                <label for="experience_years">Years of Experience *</label>
                <input type="number" id="experience_years" name="experience_years" class="form-control" min="0" max="50" required>
            </div>
        </div>
        
        <div class="form-group">
            <label for="specializations">Specializations / Skills</label>
            <input type="text" id="specializations" name="specializations" class="form-control" 
                   placeholder="e.g., Balayage, Hair Extensions, Bridal Makeup">
            <small style="color: #666;">Separate multiple skills with commas</small>
        </div>
        
        <div class="form-group">
            <label for="schedule">Preferred Schedule *</label>
            <input type="text" id="schedule" name="schedule" class="form-control" 
                   placeholder="e.g., Monday-Friday, 9am-5pm or Weekends only" required>
        </div>
        
        <div class="form-group">
            <label for="resume_text">Professional Summary / Resume *</label>
            <textarea id="resume_text" name="resume_text" class="form-control" rows="6" 
                      placeholder="Tell us about your experience, education, certifications, and why you want to join our team..." required></textarea>
        </div>
        
        <div class="form-group">
            <label for="references">Professional References</label>
            <textarea id="references" name="references" class="form-control" rows="4" 
                      placeholder="Please provide name, position, relationship, and contact information for 2-3 references"></textarea>
        </div>
        
        <div style="background: #fff3cd; border: 1px solid #ffc107; padding: 15px; border-radius: 4px; margin-bottom: 20px;">
            <i class="fas fa-info-circle"></i>
            <strong>What Happens Next?</strong>
            <p style="margin: 10px 0 0 0; color: #856404;">
                After submitting your application, our hiring team will review your qualifications. 
                If selected, we'll contact you within 3-5 business days to schedule an interview. 
                You'll receive an email with your login credentials once your application is approved.
            </p>
        </div>
        
        <button type="submit" name="staff_register" class="btn btn-primary" style="width: 100%; padding: 12px; font-size: 16px;">
            <i class="fas fa-paper-plane"></i> Submit Application
        </button>
    </form>
    
    <div style="margin-top: 40px; text-align: center; padding: 30px; background: #f8f9fa; border-radius: 8px;">
        <h3 style="margin-bottom: 15px;">Questions About Employment?</h3>
        <p style="color: #666; margin-bottom: 20px;">
            Contact our HR department for more information about career opportunities at Elegance Salon.
        </p>
        <div>
            <p><i class="fas fa-envelope" style="color: var(--primary);"></i> <strong>careers@elegancesalon.com</strong></p>
            <p><i class="fas fa-phone" style="color: var(--primary);"></i> <strong>(555) 123-4567 ext. 2</strong></p>
        </div>
    </div>
</section>