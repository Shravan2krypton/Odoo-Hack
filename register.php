<?php
include 'includes/db_connect.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = trim($_POST['first_name']);
    $last_name  = trim($_POST['last_name']);
    $email      = trim($_POST['email']);
    $phone      = trim($_POST['phone']);
    $city       = trim($_POST['city']);
    $country    = trim($_POST['country']);
    $extra_info = trim($_POST['extra_info']);
    $password   = $_POST['password'];

    // Validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters.";
    } else {
        // Check if email exists
        $sql = "SELECT id FROM users WHERE email=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Email already registered.";
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            $sql = "INSERT INTO users 
                (first_name, last_name, email, phone, city, country, extra_info, password_hash) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssssss", $first_name, $last_name, $email, $phone, $city, $country, $extra_info, $password_hash);

            if ($stmt->execute()) {
                header("Location: login.php?registered=1");
                exit();
            } else {
                $error = "Registration failed. Try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <form method="POST" action="">
        <h2>Register Users</h2>
        <input type="text" name="first_name" placeholder="First Name" required><br>
        <input type="text" name="last_name" placeholder="Last Name" required><br>
        <input type="email" name="email" placeholder="Email Address" required><br>
        <input type="text" name="phone" placeholder="Phone Number"><br>
        <input type="text" name="city" placeholder="City"><br>
        <input type="text" name="country" placeholder="Country"><br>
        <textarea name="extra_info" placeholder="Additional Information..."></textarea><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <button type="submit">Register</button>
        <p><?php if(isset($error)) echo $error; ?></p>
    </form>
</body>
</html>
