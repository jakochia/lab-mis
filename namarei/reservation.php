<?php
include '../includes/config.php';
include '../includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $date = $_POST['date'];
    $time = $_POST['time'];
    $guests = (int)$_POST['guests'];
    $requests = trim($_POST['requests']);

    $stmt = $pdo->prepare("INSERT INTO reservations (customer_name, email, phone, date, time, guests, special_requests) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$name, $email, $phone, $date, $time, $guests, $requests]);
    $success = "Reservation submitted! We'll contact you soon.";
}
?>
<h2>Make a Reservation</h2>
<?php if (isset($success)) echo "<p style='color:green'>$success</p>"; ?>
<form method="post">
    <label>Name: <input type="text" name="name" required></label><br>
    <label>Email: <input type="email" name="email"></label><br>
    <label>Phone: <input type="text" name="phone" required></label><br>
    <label>Date: <input type="date" name="date" required></label><br>
    <label>Time: <input type="time" name="time" required></label><br>
    <label>Guests: <input type="number" name="guests" min="1" required></label><br>
    <label>Special Requests: <textarea name="requests"></textarea></label><br>
    <button type="submit">Book Now</button>
</form>
<?php include '../includes/footer.php'; ?>