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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Travel Dashboard</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f7f6;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .register-container {
            background-color: #ffffff;
            width: 100%;
            max-width: 500px;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(49, 164, 254, 0.1);
            box-sizing: border-box;
            margin: 20px;
        }
        .register-container h2 {
            margin-top: 0;
            margin-bottom: 25px;
            color: #31A4FE;
            text-align: center;
            font-size: 28px;
            font-weight: 700;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group.row {
            display: flex;
            gap: 15px;
        }
        .form-group.row .col {
            flex: 1;
        }
        input[type="text"],
        input[type="email"],
        input[type="password"],
        textarea {
            width: 100%;
            padding: 14px 16px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
            color: #333;
            box-sizing: border-box;
            transition: all 0.3s ease;
            font-family: inherit;
        }
        textarea {
            resize: vertical;
            min-height: 80px;
        }
        input:focus,
        textarea:focus {
            outline: none;
            border-color: #31A4FE;
            box-shadow: 0 0 0 4px rgba(49, 164, 254, 0.15);
        }
        button[type="submit"] {
            width: 100%;
            padding: 15px;
            background-color: #31A4FE;
            color: #ffffff;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.1s ease;
            margin-top: 10px;
        }
        button[type="submit"]:hover {
            background-color: #258ae0;
        }
        button[type="submit"]:active {
            transform: scale(0.98);
        }
        .error-message {
            color: #ff4d4f;
            background-color: #ffe6e6;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: center;
            display: <?php echo isset($error) ? 'block' : 'none'; ?>;
        }
        .login-link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: #666;
        }
        .login-link a {
            color: #31A4FE;
            text-decoration: none;
            font-weight: 600;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <h2>Create an Account</h2>
        <?php if(isset($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="" id="registerForm">
            <div class="form-group row">
                <div class="col">
                    <input type="text" name="first_name" placeholder="First Name" required>
                </div>
                <div class="col">
                    <input type="text" name="last_name" placeholder="Last Name" required>
                </div>
            </div>
            <div class="form-group">
                <input type="email" name="email" placeholder="Email Address" required>
            </div>
            <div class="form-group row">
                <div class="col">
                    <input type="text" name="phone" placeholder="Phone Number">
                </div>
                <div class="col">
                    <input type="password" name="password" placeholder="Password (Min 8 chars)" required minlength="8">
                </div>
            </div>
            <div class="form-group row">
                <div class="col">
                    <input type="text" name="city" placeholder="City">
                </div>
                <div class="col">
                    <input type="text" name="country" placeholder="Country">
                </div>
            </div>
            <div class="form-group">
                <textarea name="extra_info" placeholder="Additional Information..."></textarea>
            </div>
            
            <button type="submit" id="submitBtn">Register</button>
        </form>
        
        <div class="login-link">
            Already have an account? <a href="login.php">Log in</a>
        </div>
    </div>

    <script>
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            // Only show loading if form is valid (browser validation passed)
            if (this.checkValidity()) {
                const btn = document.getElementById('submitBtn');
                btn.innerHTML = 'Registering...';
                btn.style.opacity = '0.8';
                btn.style.cursor = 'not-allowed';
            }
        });
    </script>
</body>
</html>
