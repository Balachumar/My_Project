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
        
        $booking_date = strtotime($date);
        $today = strtotime(date('Y-m-d'));
        
        if ($booking_date < $today) {
            $appointment_error = "Cannot book appointments in the past";
        } else {
            $check_sql = "SELECT COUNT(*) as count FROM appointments 
                         WHERE appointment_date = '$date' 
                         AND appointment_time = '$time' 
                         AND status != 'cancelled'";
            
            if ($staff_id) {
                $check_sql .= " AND staff_id = $staff_id";
            }
            
            $check_result = $conn->query($check_sql);
            $check_row = $check_result->fetch_assoc();
            
            if ($check_row['count'] > 0) {
                $appointment_error = "This time slot is already booked. Please select another time.";
            } else {
                $user_check_sql = "SELECT COUNT(*) as count FROM appointments 
                                  WHERE user_id = $user_id 
                                  AND appointment_date = '$date' 
                                  AND appointment_time = '$time'
                                  AND status != 'cancelled'";
                
                $user_check_result = $conn->query($user_check_sql);
                $user_check_row = $user_check_result->fetch_assoc();
                
                if ($user_check_row['count'] > 0) {
                    $appointment_error = "You already have an appointment at this time";
                } else {
                    $sql = "INSERT INTO appointments (user_id, service_id, staff_id, appointment_date, appointment_time, notes, status) 
                            VALUES ('$user_id', '$service_id', " . ($staff_id ? "'$staff_id'" : "NULL") . ", '$date', '$time', '$notes', 'pending')";
                    
                    if ($conn->query($sql)) {
                        $appointment_id = $conn->insert_id;
                        
                        $service_sql = "SELECT price_min, price_max FROM services WHERE id = $service_id";
                        $service_result = $conn->query($service_sql);
                        $service_data = $service_result->fetch_assoc();
                        $amount = ($service_data['price_min'] + $service_data['price_max']) / 2;
                        
                        $payment_sql = "INSERT INTO payments (appointment_id, amount, payment_method, payment_status) 
                                       VALUES ($appointment_id, $amount, 'Pending', 'pending')";
                        $conn->query($payment_sql);
                        
                        $appointment_success = "Appointment booked successfully! We'll confirm it shortly.";
                    } else {
                        $appointment_error = "Failed to book appointment. Please try again.";
                    }
                }
            }
        }
    }
}

$available_dates = [];
for ($i = 0; $i < 60; $i++) {
    $date = strtotime("+$i days");
    $day_of_week = date('w', $date);
    $available_dates[] = date('Y-m-d', $date);
}

$services_sql = "SELECT * FROM services ORDER BY category, name";
$services_result = $conn->query($services_sql);

$staff_sql = "SELECT s.id, u.first_name, u.last_name, s.position 
              FROM staff s 
              JOIN users u ON s.user_id = u.id 
              ORDER BY u.first_name";
$staff_result = $conn->query($staff_sql);

$upcoming_appointments = [];
if (is_logged_in()) {
    $user_id = $_SESSION['user_id'];
    $today = date('Y-m-d');
    
    $upcoming_sql = "SELECT a.*, s.name as service_name,
                     CONCAT(u.first_name, ' ', u.last_name) as stylist_name
                     FROM appointments a
                     JOIN services s ON a.service_id = s.id
                     LEFT JOIN staff st ON a.staff_id = st.id
                     LEFT JOIN users u ON st.user_id = u.id
                     WHERE a.user_id = $user_id 
                     AND a.appointment_date >= '$today'
                     AND a.status != 'cancelled'
                     ORDER BY a.appointment_date, a.appointment_time
                     LIMIT 3";
    
    $upcoming_result = $conn->query($upcoming_sql);
    while ($row = $upcoming_result->fetch_assoc()) {
        $upcoming_appointments[] = $row;
    }
}
?>

<section class="page">
    <h2 class="text-center mb-20">Book an Appointment</h2>
    
    <?php if (isset($appointment_error)): ?>
        <div class="alert alert-danger"><?php echo $appointment_error; ?></div>
    <?php endif; ?>
    
    <?php if (isset($appointment_success)): ?>
        <div class="alert alert-success"><?php echo $appointment_success; ?></div>
    <?php endif; ?>
    
    <?php if (!is_logged_in()): ?>
        <div class="alert" style="background-color: #fff3cd; border: 1px solid #ffc107; color: #856404; padding: 15px; border-radius: 4px; margin-bottom: 20px;">
            <i class="fas fa-info-circle"></i> 
            <strong>Please login or register to book an appointment.</strong>
        </div>
    <?php endif; ?>
    
    <?php if (is_logged_in() && count($upcoming_appointments) > 0): ?>
    <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
        <h3 style="margin-bottom: 15px;">Your Upcoming Appointments</h3>
        <div class="form-row">
            <?php foreach ($upcoming_appointments as $app): ?>
            <div style="flex: 1; background: white; padding: 15px; border-radius: 4px; border-left: 4px solid var(--primary);">
                <p style="margin: 5px 0;"><strong><?php echo date('l, F j, Y', strtotime($app['appointment_date'])); ?></strong></p>
                <p style="margin: 5px 0;"><?php echo date('g:i A', strtotime($app['appointment_time'])); ?> - <?php echo htmlspecialchars($app['service_name']); ?></p>
                <?php if ($app['stylist_name']): ?>
                    <p style="margin: 5px 0; color: #666;">with <?php echo htmlspecialchars($app['stylist_name']); ?></p>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
    
    <form method="POST" action="" id="appointmentForm">
        <div class="form-row">
            <div class="form-group">
                <label for="service">Select Service *</label>
                <select id="service" name="service" class="form-control" required onchange="updateServiceInfo()">
                    <option value="">Choose a service</option>
                    <?php 
                    $current_category = '';
                    $services_result->data_seek(0);
                    while ($service = $services_result->fetch_assoc()): 
                        if ($current_category != $service['category']) {
                            if ($current_category != '') echo '</optgroup>';
                            echo '<optgroup label="' . htmlspecialchars($service['category']) . '">';
                            $current_category = $service['category'];
                        }
                    ?>
                        <option value="<?php echo $service['id']; ?>" 
                                data-price-min="<?php echo $service['price_min']; ?>"
                                data-price-max="<?php echo $service['price_max']; ?>"
                                data-duration="<?php echo $service['duration']; ?>">
                            <?php echo htmlspecialchars($service['name']); ?>
                        </option>
                    <?php 
                    endwhile; 
                    if ($current_category != '') echo '</optgroup>';
                    ?>
                </select>
                <div id="serviceInfo" style="margin-top: 10px; color: #666; font-size: 14px;"></div>
            </div>
            <div class="form-group">
                <label for="stylist">Preferred Stylist</label>
                <select id="stylist" name="stylist" class="form-control" onchange="checkAvailability()">
                    <option value="">Any available stylist</option>
                    <?php 
                    $staff_result->data_seek(0);
                    while ($staff = $staff_result->fetch_assoc()): 
                    ?>
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
                <label for="date">Preferred Date *</label>
                <input type="date" id="date" name="date" class="form-control" 
                       min="<?php echo date('Y-m-d'); ?>" 
                       max="<?php echo date('Y-m-d', strtotime('+60 days')); ?>"
                       required onchange="checkAvailability()">
            </div>
            <div class="form-group">
                <label for="time">Preferred Time *</label>
                <select id="time" name="time" class="form-control" required onchange="checkAvailability()">
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
                <div id="availabilityMessage" style="margin-top: 10px; font-size: 14px;"></div>
            </div>
        </div>
        <div class="form-group">
            <label for="notes">Additional Notes</label>
            <textarea id="notes" name="notes" class="form-control" rows="4" 
                      placeholder="Any special requests, allergies, or specific requirements..."></textarea>
        </div>
        
        <?php if (is_logged_in()): ?>
            <button type="submit" name="book_appointment" class="btn btn-primary" id="bookBtn">Book Appointment</button>
        <?php else: ?>
            <button type="button" class="btn btn-primary" onclick="alert('Please login to book an appointment'); showModal('loginModal')">
                Login to Book
            </button>
        <?php endif; ?>
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
                            $date_str = sprintf("%04d-%02d-%02d", $year, $month, $day);
                            echo "<td $class onclick=\"selectDate('$date_str')\" style=\"cursor: pointer;\">$day</td>";
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

<script>
function updateServiceInfo() {
    const select = document.getElementById('service');
    const option = select.options[select.selectedIndex];
    const infoDiv = document.getElementById('serviceInfo');
    
    if (option.value) {
        const priceMin = option.getAttribute('data-price-min');
        const priceMax = option.getAttribute('data-price-max');
        const duration = option.getAttribute('data-duration');
        
        infoDiv.innerHTML = `
            <i class="fas fa-info-circle"></i> 
            <strong>Price:</strong> $${priceMin} - $${priceMax} | 
            <strong>Duration:</strong> ${duration} minutes
        `;
    } else {
        infoDiv.innerHTML = '';
    }
}

function checkAvailability() {
    const date = document.getElementById('date').value;
    const time = document.getElementById('time').value;
    const stylist = document.getElementById('stylist').value;
    const messageDiv = document.getElementById('availabilityMessage');
    const bookBtn = document.getElementById('bookBtn');
    
    if (date && time) {
        messageDiv.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Checking availability...';
        messageDiv.style.color = '#666';
        setTimeout(() => {
            const isAvailable = Math.random() > 0.3;
            if (isAvailable) {
                messageDiv.innerHTML = '<i class="fas fa-check-circle"></i> Time slot is available!';
                messageDiv.style.color = '#28a745';
                if (bookBtn) bookBtn.disabled = false;
            } else {
                messageDiv.innerHTML = '<i class="fas fa-times-circle"></i> This time slot is already booked. Please choose another time.';
                messageDiv.style.color = '#dc3545';
                if (bookBtn) bookBtn.disabled = true;
            }
        }, 500);
    }
}

function selectDate(date) {
    document.getElementById('date').value = date;
    checkAvailability();
}
</script>