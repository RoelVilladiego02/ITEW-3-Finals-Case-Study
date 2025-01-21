<?php
session_start();
require_once '../config/db.php'; // Database connection

class User {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function login($email, $password) {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC); // Use fetch(PDO::FETCH_ASSOC) for associative array

            if ($user && password_verify($password, $user['password'])) {
                return $user; // Return user data if authenticated
            }

        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
        }

        return false; // Invalid credentials
    }
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $error = 'Email and Password are required.';
    } else {
        // Step 1: Create Database instance and get PDO connection
        $db = new Database();
        $pdo = $db->getConnection();

        // Step 2: Pass $pdo to User class
        $userClass = new User($pdo);
        $user = $userClass->login($email, $password);

        if ($user) {
            // Set user session with ID and role
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];

            // Redirect based on user role
            switch ($user['role']) {
                case 'admin':
                    header('Location: ../admin/dashboard.php');
                    exit();
                case 'user':
                    header('Location: front_store.php');
                    exit();
                default:
                    $error = 'Invalid user role.';
                    break;
            }
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        html, body {
            height: 100%;
            margin: 0;
        }
        body {
            background: linear-gradient(135deg, #f6d365 0%, #fda085 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            background-color: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            transform: scale(1.02);
            transition: transform 0.3s ease;
        }
        .login-container:hover {
            transform: scale(1.05);
        }
        .btn-login {
            background: linear-gradient(to right, #f6d365, #fda085);
            color: white;
            border: none;
            transition: opacity 0.3s ease;
        }
        .btn-login:hover {
            opacity: 0.9;
            color: white;
        }
        .footer-link {
            text-align: center;
            margin-top: 20px;
        }
        .footer-link a {
            color: #007bff;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        .footer-link a:hover {
            text-decoration: underline;
            color: #0056b3;
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row justify-content-center align-items-center">
        <div class="col-12 col-md-6 col-lg-4">
            <div class="login-container">
                <h2 class="text-center mb-4">Login</h2>
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-login w-100">Login</button>
                </form>
                <div class="footer-link">
                    <p>Don't have an account? <a href="register.php">Register here</a></p>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>