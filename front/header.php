<?php

// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class HeaderManager
{
    private $currentPage;
    private $isLoggedIn;
    private $pageTitle;
    private $additionalStyles;

    public function __construct($pageTitle = 'Front Store', $additionalStyles = '')
    {
        $this->currentPage = basename($_SERVER['PHP_SELF']);
        $this->isLoggedIn = isset($_SESSION['user_id']);
        $this->pageTitle = $pageTitle;
        $this->additionalStyles = $additionalStyles;
        $this->redirectIfNotLoggedIn();
    }

    private function redirectIfNotLoggedIn()
    {
        if (!$this->isLoggedIn && $this->currentPage !== 'login.php') {
            header('Location: login.php');
            exit();
        }
    }

    public function renderHeader()
    {
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?php echo htmlspecialchars($this->pageTitle); ?></title>
            
            <!-- Bootstrap 5 CSS -->
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
            
            <!-- Bootstrap Icons -->
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.0/font/bootstrap-icons.min.css">
            
            <style>
                body {
                    background: linear-gradient(135deg, #f6d365 0%, #fda085 100%);
                    min-height: 100vh;
                    display: flex;
                    flex-direction: column;
                }
                .navbar {
                    background: rgba(0, 0, 0, 0.7) !important;
                }
                .content-container {
                    background-color: white;
                    border-radius: 10px;
                    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
                    padding: 30px;
                    margin-top: 20px;
                    flex-grow: 1;
                }
                .btn-custom {
                    background: linear-gradient(to right, #f6d365, #fda085);
                    color: white;
                    border: none;
                }
                .btn-custom:hover {
                    opacity: 0.9;
                    color: white;
                }
            </style>
            <?php echo $this->additionalStyles; ?>
        </head>
        <body class="d-flex flex-column">
        <?php
        if ($this->isLoggedIn) {
            $this->renderNavbar();
        }
        ?>
        <div class="container flex-grow-1">
        <?php
    }

    private function renderNavbar()
    {
        ?>
        <nav class="navbar navbar-expand-lg navbar-dark">
            <div class="container-fluid">
                <a class="navbar-brand" href="front_store.php">
                    <i class="bi bi-shop me-2"></i>Front Store
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="view_cart.php">
                                <i class="bi bi-cart-fill me-1"></i>View Cart
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">
                                <i class="bi bi-box-arrow-right me-1"></i>Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
        <?php
    }
}

// Usage
$pageTitle = isset($page_title) ? $page_title : 'Front Store';
$additionalStyles = isset($additional_styles) ? $additional_styles : '';
$headerManager = new HeaderManager($pageTitle, $additionalStyles);
$headerManager->renderHeader();
?>
