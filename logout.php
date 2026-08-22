<?php
session_start();

// Unset all session variables
$_SESSION = array();

// Destroy the session cookie if it exists
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finally destroy the session
session_destroy();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logging Out...</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #ffffff;
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .logout-container {
            text-align: center;
            padding: 50px;
            border-radius: 16px;
            box-shadow: 0 15px 35px rgba(49, 164, 254, 0.15);
            background: #ffffff;
            border: 1px solid rgba(49, 164, 254, 0.1);
        }
        .spinner {
            width: 60px;
            height: 60px;
            border: 6px solid rgba(49, 164, 254, 0.15);
            border-top: 6px solid #31A4FE;
            border-radius: 50%;
            margin: 0 auto 25px;
            animation: spin 1s cubic-bezier(0.68, -0.55, 0.265, 1.55) infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        h2 {
            margin: 0 0 10px;
            color: #31A4FE;
            font-size: 24px;
            font-weight: 600;
        }
        p {
            margin: 0;
            color: #777;
            font-size: 15px;
        }
    </style>
</head>
<body>
    <div class="logout-container">
        <div class="spinner"></div>
        <h2>Logging Out</h2>
        <p>Please wait while we securely log you out...</p>
    </div>

    <script>
        // Redirect to login page after a short delay to show the animation
        setTimeout(function() {
            window.location.href = 'login.php?loggedout=1';
        }, 2000);
    </script>
</body>
</html>
