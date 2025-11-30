<?php
// Handle contact form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['send_message'])) {
    $name = sanitize_input($_POST['name']);
    $email = sanitize_input($_POST['email']);
    $subject = sanitize_input($_POST['subject']);
    $message = sanitize_input($_POST['message']);
    
    $sql = "INSERT INTO contact_messages (name, email, subject, message) 
            VALUES ('$name', '$email', '$subject', '$message')";
    
    if ($conn->query($sql)) {
        $contact_success = "Thank you for contacting us! We'll get back to you soon.";
    } else {
        $contact_error = "Failed to send message. Please try again.";
    }
}
?>

<section class="page">
    <h2 class="text-center mb-20">Contact Us</h2>
    
    <?php if (isset($contact_error)): ?>
        <div class="alert alert-danger"><?php echo $contact_error; ?></div>
    <?php endif; ?>
    
    <?php if (isset($contact_success)): ?>
        <div class="alert alert-success"><?php echo $contact_success; ?></div>
    <?php endif; ?>
    
    <div class="form-row">
        <div class="form-group">
            <h3>Get In Touch</h3>
            <p>We'd love to hear from you. Send us a message and we'll respond as soon as possible.</p>
            
            <div class="mt-20">
                <p><i class="fas fa-map-marker-alt"></i> 123 Beauty Street, Glamour City, GC 12345</p>
                <p><i class="fas fa-phone"></i> (555) 123-4567</p>
                <p><i class="fas fa-envelope"></i> info@elegancesalon.com</p>
            </div>
            
            <div class="mt-20">
                <h4>Business Hours</h4>
                <p>Monday - Friday: 9:00 AM - 7:00 PM</p>
                <p>Saturday: 9:00 AM - 5:00 PM</p>
                <p>Sunday: 10:00 AM - 4:00 PM</p>
            </div>
        </div>
        <div class="form-group">
            <form method="POST" action="">
                <div class="form-group">
                    <label for="name">Your Name</label>
                    <input type="text" id="name" name="name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="subject">Subject</label>
                    <input type="text" id="subject" name="subject" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="message">Message</label>
                    <textarea id="message" name="message" class="form-control" rows="5" required></textarea>
                </div>
                <button type="submit" name="send_message" class="btn btn-primary">Send Message</button>
            </form>
        </div>
    </div>
</section>