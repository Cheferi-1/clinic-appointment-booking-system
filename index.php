<?php

$host = "localhost";
$port = 3307;
$dbname = "lab03_clinic";
$username = "root";
$password = "";

$success = "";
$errors = [];

$patient_name = "";
$email = "";
$phone = "";
$department = "";
$preferred_doctor = "";
$appointment_date = "";
$time_slot = "";
$reason = "";
$consent = "";

try {
    // Connect to database
    $conn = new PDO(
        "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );

    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    die("Database connection failed. Please check your database settings.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Collect form values
    $patient_name = trim($_POST["patient_name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $department = trim($_POST["department"] ?? "");
    $preferred_doctor = trim($_POST["preferred_doctor"] ?? "");
    $appointment_date = trim($_POST["appointment_date"] ?? "");
    $time_slot = trim($_POST["time_slot"] ?? "");
    $reason = trim($_POST["reason"] ?? "");
    $consent = isset($_POST["consent"]) ? "1" : "";

    // Validate patient name
    if ($patient_name == "") {
        $errors[] = "Patient name is required.";
    }

    // Validate email if supplied
    if ($email != "" && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }

    // Validate phone
    if ($phone == "") {
        $errors[] = "Phone number is required.";
    }

    // Validate department
    $departments = ["General Medicine", "Dental", "Pediatrics", "Cardiology", "Maternity"];

    if (!in_array($department, $departments, true)) {
        $errors[] = "Please select a valid department.";
    }

    // Validate doctor
    $doctors = ["Dr. John", "Dr. Mary", "Dr. Peter", "Dr. Grace"];

    if (!in_array($preferred_doctor, $doctors, true)) {
        $errors[] = "Please select a valid doctor.";
    }

    // Validate appointment date
    $dateObject = DateTime::createFromFormat('!Y-m-d', $appointment_date);

    if (
        !$dateObject ||
        $dateObject->format('Y-m-d') !== $appointment_date ||
        $appointment_date <= date("Y-m-d")
    ) {
        $errors[] = "Appointment date must be a valid future date.";
    }

    // Validate time slot
    $time_slots = ["09:00 AM", "10:00 AM", "11:00 AM", "12:00 PM", "02:00 PM", "03:00 PM"];

    if (!in_array($time_slot, $time_slots, true)) {
        $errors[] = "Please select a valid time slot.";
    }

    // Validate reason
    if (strlen($reason) < 10 || strlen($reason) > 500) {
        $errors[] = "Reason must contain between 10 and 500 characters.";
    }

    // Validate consent
    if ($consent != "1") {
        $errors[] = "You must agree to the use of your information.";
    }

    // Insert appointment if everything is valid
    if (empty($errors)) {

        try {

            // Check whether doctor is already booked
            $check = $conn->prepare(
                "SELECT id FROM appointments
                 WHERE preferred_doctor = ?
                 AND appointment_date = ?
                 AND time_slot = ?"
            );

            $check->execute([
                $preferred_doctor,
                $appointment_date,
                $time_slot
            ]);

            if ($check->rowCount() > 0) {

                $errors[] = "This doctor already has an appointment at that date and time. Please choose another slot.";

            } else {

                // Insert appointment
                $sql = "INSERT INTO appointments
                (patient_name, email, phone, department,
                preferred_doctor, appointment_date, time_slot,
                reason, consent)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

                $stmt = $conn->prepare($sql);

                $stmt->execute([
                    $patient_name,
                    $email ?: null,
                    $phone,
                    $department,
                    $preferred_doctor,
                    $appointment_date,
                    $time_slot,
                    $reason,
                    1
                ]);

                $appointment_id = $conn->lastInsertId();

                $success = "Appointment booked successfully! Your appointment reference is #" . $appointment_id;

                // Clear form after success
                $patient_name = "";
                $email = "";
                $phone = "";
                $department = "";
                $preferred_doctor = "";
                $appointment_date = "";
                $time_slot = "";
                $reason = "";
                $consent = "";
            }

        } catch (PDOException $e) {

            if ($e->getCode() == "23000") {
                $errors[] = "This doctor is already booked for that date and time.";
            } else {
                $errors[] = "Could not save the appointment. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clinic Appointment Booking</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<div class="container">

    <h1>Clinic Appointment Booking</h1>
    <p class="subtitle">Complete the form to request an appointment.</p>

    <?php if (!empty($errors)): ?>
        <div class="error">
            <strong>Please correct the following errors:</strong>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($success != ""): ?>
        <div class="success">
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">

        <label>Patient Name *</label>
        <input type="text" name="patient_name"
               value="<?= htmlspecialchars($patient_name) ?>"
               required>

        <label>Email Address (Optional)</label>
        <input type="email" name="email"
               value="<?= htmlspecialchars($email) ?>">

        <label>Phone Number *</label>
        <input type="tel" name="phone"
               value="<?= htmlspecialchars($phone) ?>"
               required>

        <label>Department *</label>
        <select name="department" required>
            <option value="">Select Department</option>

            <?php
            $departments = ["General Medicine", "Dental", "Pediatrics", "Cardiology", "Maternity"];

            foreach ($departments as $item):
            ?>
                <option value="<?= htmlspecialchars($item) ?>"
                    <?= $department == $item ? "selected" : "" ?>>
                    <?= htmlspecialchars($item) ?>
                </option>
            <?php endforeach; ?>

        </select>

        <label>Preferred Doctor *</label>
        <select name="preferred_doctor" required>
            <option value="">Select Doctor</option>

            <?php
            $doctors = ["Dr. John", "Dr. Mary", "Dr. Peter", "Dr. Grace"];

            foreach ($doctors as $doctor):
            ?>
                <option value="<?= htmlspecialchars($doctor) ?>"
                    <?= $preferred_doctor == $doctor ? "selected" : "" ?>>
                    <?= htmlspecialchars($doctor) ?>
                </option>
            <?php endforeach; ?>

        </select>

        <label>Appointment Date *</label>
        <input type="date" name="appointment_date"
               min="<?= date('Y-m-d', strtotime('+1 day')) ?>"
               value="<?= htmlspecialchars($appointment_date) ?>"
               required>

        <label>Time Slot *</label>
        <select name="time_slot" required>
            <option value="">Select Time</option>

            <?php
            $time_slots = ["09:00 AM", "10:00 AM", "11:00 AM", "12:00 PM", "02:00 PM", "03:00 PM"];

            foreach ($time_slots as $slot):
            ?>
                <option value="<?= htmlspecialchars($slot) ?>"
                    <?= $time_slot == $slot ? "selected" : "" ?>>
                    <?= htmlspecialchars($slot) ?>
                </option>
            <?php endforeach; ?>

        </select>

        <label>Symptoms or Reason for Appointment *</label>
        <textarea name="reason" rows="5" required><?= htmlspecialchars($reason) ?></textarea>

        <div class="checkbox">
            <input type="checkbox" name="consent" value="1"
                <?= $consent == "1" ? "checked" : "" ?>>
            <label>I consent to the use of my information for appointment booking. *</label>
        </div>

        <button type="submit">Book Appointment</button>

    </form>

</div>

</body>
</html>