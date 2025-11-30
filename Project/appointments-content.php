<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['book_appointment'])) {
    if (!is_logged_in()) {
        $appointment_error = "Please login to book an appointment";
    } else {
        $service_id = sanitize_input($_POST['service']);
        $staff_id = !empty($_POST['stylist']) ? sanitize_input($_POST['stylist']) : NULL;
        $date = sanitize_input($_POST['date']);
        $time = sanitize_input($_POST['time']);
        $notes = sanitize_input($_POST['notes']);
        $user_id = $_SESSION['user_id'];
        
        $sql = "INSERT INTO appointments (user_id, service_id, staff_id, appointment_date, appointment_time, notes, status) 
                VALUES ('$user_id', '$service_id', " . ($staff_id ? "'$staff_id'" : "NULL") . ", '$date', '$time', '$notes', 'pending')";
        
        if ($conn->query($sql)) {
            $appointment_success = "Appointment booked successfully! We'll confirm it shortly.";
        } else {
            $appointment_error = "Failed to book appointment. Please try again.";
        }
    }
}

$services_sql = "SELECT * FROM services ORDER BY name";
$services_result = $conn->query($services_sql);

$staff_sql = "SELECT s.id, u.first_name, u.last_name, s.position 
              FROM staff s 
              JOIN users u ON s.user_id = u.id 
              ORDER BY u.first_name";
$staff_result = $conn->query($staff_sql);
?>

<section class="page">
    <h2 class="text-center mb-20">Book an Appointment</h2>
    
    <?php if (isset($appointment_error)): ?>
        <div class="alert alert-danger"><?php echo $appointment_error; ?></div>
    <?php endif; ?>
    
    <?php if (isset($appointment_success)): ?>
        <div class="alert alert-success"><?php echo $appointment_success; ?></div>
    <?php endif; ?>
    
    <form method="POST" action="">
        <div class="form-row">
            <div class="form-group">
                <label for="service">Select Service</label>
                <select id="service" name="service" class="form-control" required>
                    <option value="">Choose a service</option>
                    <?php while ($service = $services_result->fetch_assoc()): ?>
                        <option value="<?php echo $service['id']; ?>">
                            <?php echo htmlspecialchars($service['name']); ?> 
                            ($<?php echo number_format($service['price_min'], 2); ?> - $<?php echo number_format($service['price_max'], 2); ?>)
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="stylist">Preferred Stylist</label>
                <select id="stylist" name="stylist" class="form-control">
                    <option value="">Any available stylist</option>
                    <?php while ($staff = $staff_result->fetch_assoc()): ?>
                        <option value="<?php echo $staff['id']; ?>">
                            <?php echo htmlspecialchars($staff['first_name'] . ' ' . $staff['last_name']); ?>
                            (<?php echo htmlspecialchars($staff['position']); ?>)
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="date">Preferred Date</label>
                <input type="date" id="date" name="date" class="form-control" 
                       min="<?php echo date('Y-m-d'); ?>" required>
            </div>
            <div class="form-group">
                <label for="time">Preferred Time</label>
                <select id="time" name="time" class="form-control" required>
                    <option value="">Select a time</option>
                    <option value="09:00:00">9:00 AM</option>
                    <option value="10:00:00">10:00 AM</option>
                    <option value="11:00:00">11:00 AM</option>
                    <option value="12:00:00">12:00 PM</option>
                    <option value="13:00:00">1:00 PM</option>
                    <option value="14:00:00">2:00 PM</option>
                    <option value="15:00:00">3:00 PM</option>
                    <option value="16:00:00">4:00 PM</option>
                    <option value="17:00:00">5:00 PM</option>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label for="notes">Additional Notes</label>
            <textarea id="notes" name="notes" class="form-control" rows="4" 
                      placeholder="Any special requests or requirements..."></textarea>
        </div>
        <button type="submit" name="book_appointment" class="btn btn-primary">Book Appointment</button>
    </form>
    
    <div class="mt-20">
        <h3>Appointment Calendar</h3>
        <table class="calendar">
            <thead>
                <tr>
                    <th>Sun</th>
                    <th>Mon</th>
                    <th>Tue</th>
                    <th>Wed</th>
                    <th>Thu</th>
                    <th>Fri</th>
                    <th>Sat</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $month = date('m');
                $year = date('Y');
                $first_day = mktime(0, 0, 0, $month, 1, $year);
                $days_in_month = date('t', $first_day);
                $day_of_week = date('w', $first_day);
                $today = date('j');
                
                $day = 1;
                for ($i = 0; $i < 6; $i++):
                    echo "<tr>";
                    for ($j = 0; $j < 7; $j++):
                        if (($i == 0 && $j < $day_of_week) || $day > $days_in_month):
                            echo "<td></td>";
                        else:
                            $class = ($day == $today) ? 'class="today"' : '';
                            echo "<td $class>$day</td>";
                            $day++;
                        endif;
                    endfor;
                    echo "</tr>";
                    if ($day > $days_in_month) break;
                endfor;
                ?>
            </tbody>
        </table>
    </div>
</section>