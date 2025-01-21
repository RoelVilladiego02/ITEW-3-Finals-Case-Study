<?php
// Start the session
session_start();

// Redirect to login page if the user is not logged in or not an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../front/login.php');
    exit();
}


include 'header.php';
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <h2 class="card-title text-center mb-0">
                        <i class="bi bi-person-circle me-2 text-primary"></i>
                        Welcome, Admin
                    </h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-3 col-sm-6">
            <div class="card dashboard-card h-100 border-0 shadow-hover">
                <a href="add_product.php" class="text-decoration-none">
                    <div class="card-body text-center">
                        <div class="dashboard-icon bg-primary-soft mb-3">
                            <i class="bi bi-plus-circle text-primary fs-2"></i>
                        </div>
                        <h5 class="card-title text-dark">Add Product</h5>
                        <p class="card-text text-muted">Create new product entries</p>
                    </div>
                </a>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="card dashboard-card h-100 border-0 shadow-hover">
                <a href="view_product.php" class="text-decoration-none">
                    <div class="card-body text-center">
                        <div class="dashboard-icon bg-info-soft mb-3">
                            <i class="bi bi-card-list text-info fs-2"></i>
                        </div>
                        <h5 class="card-title text-dark">View Products</h5>
                        <p class="card-text text-muted">Browse product catalog</p>
                    </div>
                </a>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="card dashboard-card h-100 border-0 shadow-hover">
                <a href="edit_product.php" class="text-decoration-none">
                    <div class="card-body text-center">
                        <div class="dashboard-icon bg-success-soft mb-3">
                            <i class="bi bi-pencil-square text-success fs-2"></i>
                        </div>
                        <h5 class="card-title text-dark">Edit Product</h5>
                        <p class="card-text text-muted">Modify product details</p>
                    </div>
                </a>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="card dashboard-card h-100 border-0 shadow-hover">
                <a href="delete_product.php" class="text-decoration-none">
                    <div class="card-body text-center">
                        <div class="dashboard-icon bg-danger-soft mb-3">
                            <i class="bi bi-trash-fill text-danger fs-2"></i>
                        </div>
                        <h5 class="card-title text-dark">Delete Product</h5>
                        <p class="card-text text-muted">Remove product entries</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>

<style>
    body {
        background-color: #f4f6f9;
    }
    .dashboard-card {
        transition: all 0.3s ease;
        transform: translateY(0);
    }
    .dashboard-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
    }
    .dashboard-icon {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
    }
    .bg-primary-soft {
        background-color: rgba(13, 110, 253, 0.1);
    }
    .bg-info-soft {
        background-color: rgba(13, 202, 240, 0.1);
    }
    .bg-success-soft {
        background-color: rgba(25, 135, 84, 0.1);
    }
    .bg-danger-soft {
        background-color: rgba(220, 53, 69, 0.1);
    }
    .shadow-hover {
        transition: box-shadow 0.3s ease;
    }
    .shadow-hover:hover {
        box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>