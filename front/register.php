<?php
session_start();
include '../config/db.php';

class UserRegistration
{
    private $pdo;
    private $error = '';
    private $success = '';

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function register($name, $email, $password, $contact_info)
    {
        if (empty($name) || empty($email) || empty($password) || empty($contact_info)) {
            $this->error = 'All fields are required.';
            return false;
        }

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $role = 'User';

        $stmt = $this->pdo->prepare("INSERT INTO users (name, email, password, contact_info, role) VALUES (?, ?, ?, ?, ?)");
        if ($stmt->execute([$name, $email, $hashed_password, $contact_info, $role])) {
            $this->success = 'Registration successful! You can now log in.';
            return true;
        } else {
            $this->error = 'Registration failed. Email may already be in use.';
            return false;
        }
    }

    public function getError()
    {
        return $this->error;
    }

    public function getSuccess()
    {
        return $this->success;
    }
}

// Initialize the Database connection
$db = new Database();
$pdo = $db->getConnection();

$registration = new UserRegistration($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $contact_info = trim($_POST['contact_info']);

    if ($registration->register($name, $email, $password, $contact_info)) {
        $_SESSION['message'] = $registration->getSuccess();
        header('Location: login.php');
        exit();
    } else {
        $_SESSION['error'] = $registration->getError();
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration</title>
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
        .register-container {
            background-color: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 450px;
            transform: scale(1.02);
            transition: transform 0.3s ease;
        }
        .register-container:hover {
            transform: scale(1.05);
        }
        .btn-register {
            background: linear-gradient(to right, #f6d365, #fda085);
            color: white;
            border: none;
            transition: opacity 0.3s ease;
        }
        .btn-register:hover {
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
            <div class="register-container">
                <h2 class="text-center mb-4">User Registration</h2>
                <?php if (!empty($registration->getError())): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($registration->getError()); ?></div>
                <?php elseif (!empty($registration->getSuccess())): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($registration->getSuccess()); ?></div>
                <?php endif; ?>
                <form method="POST" action="register.php">
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="contact_info" class="form-label">Contact Info</label>
                        <input type="text" class="form-control" id="contact_info" name="contact_info" required>
                    </div>
                    <button type="submit" class="btn btn-register w-100">Register</button>
                </form>
                <div class="footer-link">
                    <p>Already have an account? <a href="login.php">Login here</a></p>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>