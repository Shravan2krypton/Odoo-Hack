<?php
include 'includes/db_connect.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name  = trim($_POST['first_name']);
    $last_name   = trim($_POST['last_name']);
    $email       = trim($_POST['email']);
    $phone_code  = trim($_POST['phone_code']);   // from dropdown
    $phone_number= trim($_POST['phone_number']); // 10 digits
    $country_id  = intval($_POST['country_id']);
    $city_id     = intval($_POST['city_id']);
    $extra_info  = trim($_POST['extra_info']);
    $password    = $_POST['password'];

    // Validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters.";
    } elseif (!preg_match('/^\d{10}$/', $phone_number)) {
        $error = "Phone number must be exactly 10 digits.";
    } else {
        $phone = '+' . $phone_code . $phone_number; // final stored value

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
                (first_name, last_name, email, phone, country_id, city_id, extra_info, password_hash) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssiiis", $first_name, $last_name, $email, $phone, $country_id, $city_id, $extra_info, $password_hash);

            if ($stmt->execute()) {
                header("Location: login.php?registered=1");
                exit();
            } else {
                $error = "Registration failed. Try again.";
            }
        }
    }
}

// Fetch countries for dropdown
$sql = "SELECT id, name, phone_code FROM countries ORDER BY name ASC";
$countries = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Register</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="assets/js/api.js" defer></script>
</head>
<body>
    <div class="form-container">
        <form id="registerForm" method="POST" action="">
            <h2>Register Users</h2>
            <input type="text" name="first_name" placeholder="First Name" required>
            <input type="text" name="last_name" placeholder="Last Name" required>
            <input type="email" name="email" placeholder="Email Address" required>
            <select name="country_id" id="countrySelect" required>
                <option value="" disabled selected>Select Country</option>
                <?php foreach ($countries as $c) { ?>
                    <option value="<?= $c['id'] ?>" data-code="<?= $c['phone_code'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                <?php } ?>
            </select>
            <input type="hidden" name="phone_code" id="phoneCode" value="">
            <input type="text" name="phone_number" id="phoneNumber" placeholder="Phone Number" required>
            <select name="city_id" id="citySelect" required>
                <option value="" disabled selected>Select City</option>
            </select>
            <textarea name="extra_info" placeholder="Additional Information..."></textarea>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Register</button>
            <p class="error-msg" id="errorMsg"><?php if(isset($error)) echo $error; ?></p>
        </form>
    </div>
    <div class="toast" id="toast"></div>
</body>
</html>
