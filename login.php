<?php
include 'includes/db_connect.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT id, password_hash FROM users WHERE email=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $row['id'];
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Invalid credentials.";
        }
    } else {
        $error = "Invalid credentials.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Premium Experience</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #31A4FE;
            --primary-hover: #2689d9;
            --bg-color: #f4f7f6;
            --surface: #ffffff;
            --text-main: #333333;
            --text-muted: #777777;
            --error: #ff4d4f;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
        }

        body {
            background-color: var(--bg-color);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            color: var(--text-main);
        }

        .login-container {
            background: var(--surface);
            width: 100%;
            max-width: 400px;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .login-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.08);
        }

        .login-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 6px;
            background: var(--primary);
        }

        h2 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
            color: var(--text-main);
        }

        .subtitle {
            font-size: 14px;
            color: var(--text-muted);
            margin-bottom: 30px;
        }

        .input-group {
            position: relative;
            margin-bottom: 24px;
        }

        .input-group input {
            width: 100%;
            padding: 14px 16px;
            font-size: 15px;
            border: 2px solid #e1e5e8;
            border-radius: 10px;
            outline: none;
            transition: all 0.3s ease;
            background: transparent;
        }

        .input-group input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(49, 164, 254, 0.1);
        }

        .input-group label {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 15px;
            pointer-events: none;
            transition: all 0.3s ease;
            background: var(--surface);
            padding: 0 4px;
        }

        .input-group input:focus ~ label,
        .input-group input:not(:placeholder-shown) ~ label {
            top: 0;
            font-size: 12px;
            color: var(--primary);
        }

        button {
            width: 100%;
            padding: 14px;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.1s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        button:hover {
            background-color: var(--primary-hover);
        }

        button:active {
            transform: scale(0.98);
        }

        .error-message {
            color: var(--error);
            font-size: 14px;
            margin-top: 15px;
            text-align: center;
            font-weight: 500;
            opacity: 0;
            transform: translateY(-10px);
            animation: slideIn 0.3s forwards ease-out;
        }

        @keyframes slideIn {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .loader {
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
            display: none;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .btn-text {
            transition: opacity 0.2s;
        }

        button.loading .btn-text {
            display: none;
        }

        button.loading .loader {
            display: block;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Welcome Back</h2>
        <p class="subtitle">Please enter your details to sign in.</p>
        <form method="POST" action="" id="loginForm">
            <div class="input-group">
                <input type="email" name="email" id="email" placeholder=" " required>
                <label for="email">Email Address</label>
            </div>
            <div class="input-group">
                <input type="password" name="password" id="password" placeholder=" " required>
                <label for="password">Password</label>
            </div>
            <button type="submit" id="submitBtn">
                <span class="btn-text">Sign In</span>
                <div class="loader"></div>
            </button>
            <?php if(isset($error)): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
        </form>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', function() {
            const btn = document.getElementById('submitBtn');
            btn.classList.add('loading');
        });
    </script>
</body>
</html>
