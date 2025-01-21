<?php
session_start();
include '../config/db.php';

// Ensure the user is logged in and has the 'admin' role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../front/login.php');
    exit();
}

// Initialize variables to avoid undefined variable warnings
$error = '';
$success = '';

// Initialize the Database connection
$db = new Database();
$pdo = $db->getConnection();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $barcode = trim($_POST['barcode']);
    $product_name = trim($_POST['product_name']);
    $description = trim($_POST['description']);
    $price = trim($_POST['price']);
    $quantity = trim($_POST['quantity']);
    $category = trim($_POST['category']);

    try {
        // Check if the barcode already exists to avoid duplicates
        $stmt = $pdo->prepare("SELECT * FROM products WHERE barcode = ?");
        $stmt->execute([$barcode]);

        if ($stmt->rowCount() > 0) {
            $error = "Barcode already exists. Please use a unique barcode.";
        } else {
            // If 'Other' is selected, set the category to the specified value
            if ($category === 'Other') {
                $other_category = trim($_POST['other_category'] ?? '');
                $category = $other_category; // Use the custom category
            }

            // Insert product into the database
            $stmt = $pdo->prepare("INSERT INTO products (barcode, product_name, description, price, quantity, category) VALUES (?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$barcode, $product_name, $description, $price, $quantity, $category])) {
                $success = "Product added successfully!";
            } else {
                $error = "Failed to add product. Please try again.";
            }
        }
    } catch (PDOException $e) {
        error_log("Error adding product: " . $e->getMessage());
        $error = "An unexpected error occurred. Please try again.";
    }
}
?>


<?php include 'header.php'; ?>

<div class="container-fluid my-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-lg border-0 rounded-lg">
                <div class="card-header bg-gradient-primary text-white text-center py-4">
                    <h2 class="mb-0">
                        <i class="bi bi-box-seam me-2"></i>Add New Product
                    </h2>
                </div>
                <div class="card-body p-4">
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <?php echo htmlspecialchars($error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php elseif ($success): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle-fill me-2"></i>
                            <?php echo htmlspecialchars($success); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form action="add_product.php" method="POST" class="needs-validation" novalidate>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="barcode" class="form-label">
                                    <i class="bi bi-barcode me-2 text-primary"></i>Product Barcode
                                </label>
                                <input type="text" class="form-control" id="barcode" name="barcode" required>
                                <div class="invalid-feedback">Please enter a product barcode.</div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="product_name" class="form-label">
                                    <i class="bi bi-tag me-2 text-primary"></i>Product Name
                                </label>
                                <input type="text" class="form-control" id="product_name" name="product_name" required>
                                <div class="invalid-feedback">Please enter a product name.</div>
                            </div>
                            
                            <div class="col-12">
                                <label for="description" class="form-label">
                                    <i class="bi bi-clipboard-fill me-2 text-primary"></i>Description
                                </label>
                                <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                                <div class="invalid-feedback">Please provide a product description.</div>
                            </div>
                            
                            <div class="col-md-4">
                                <label for="price" class="form-label">
                                    <i class="bi bi-cash me-2 text-primary"></i>Price
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="number" step="0.01" class="form-control" id="price" name="price" required>
                                    <div class="invalid-feedback">Please enter a valid price.</div>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <label for="quantity" class="form-label">
                                    <i class="bi bi-boxes me-2 text-primary"></i>Quantity
                                </label>
                                <input type="number" class="form-control" id="quantity" name="quantity" required>
                                <div class="invalid-feedback">Please enter the quantity.</div>
                            </div>
                            
                            <div class="col-md-4">
                                <label for="category" class="form-label">
                                    <i class="bi bi-folder me-2 text-primary"></i>Category
                                </label>
                                <select class="form-select" id="category" name="category" required onchange="toggleOtherCategory()">
                                    <option value="" disabled selected>Select Category</option>
                                    <option value="Electronics and Gadgets">Electronics and Gadgets</option>
                                    <option value="Fashion and Apparel">Fashion and Apparel</option>
                                    <option value="Health and Beauty">Health and Beauty</option>
                                    <option value="Home and Kitchen Appliances">Home and Kitchen Appliances</option>
                                    <option value="Other">Other</option>
                                </select>
                                <div class="invalid-feedback">Please select a category.</div>
                            </div>
                            
                            <div class="col-12 mb-3" id="otherCategoryDiv" style="display: none;">
                                <label for="otherCategory" class="form-label">
                                    <i class="bi bi-pencil-fill me-2 text-primary"></i>Specify Category
                                </label>
                                <input type="text" class="form-control" id="otherCategory" name="other_category" placeholder="Enter custom category">
                            </div>
                            
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary w-100 py-2">
                                    <i class="bi bi-plus-circle me-2"></i>Add Product
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    body {
        background-color: #f4f6f9;
    }
    .bg-gradient-primary {
        background: linear-gradient(to right, #667eea 0%, #764ba2 100%) !important;
    }
    .card {
        border: none;
        border-radius: 15px;
        overflow: hidden;
    }
    .card-header {
        font-weight: bold;
    }
    .form-label {
        font-weight: 500;
    }
</style>

<script>
    // Add additional custom validation
    (function() {
        'use strict';
        window.addEventListener('load', function() {
            var forms = document.getElementsByClassName('needs-validation');
            var validation = Array.prototype.filter.call(forms, function(form) {
                form.addEventListener('submit', function(event) {
                    if (form.checkValidity() === false) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        }, false);
    })();

    function toggleOtherCategory() {
        const categorySelect = document.getElementById('category');
        const otherCategoryDiv = document.getElementById('otherCategoryDiv');
        const otherCategoryInput = document.getElementById('otherCategory');

        if (categorySelect.value === 'Other') {
            otherCategoryDiv.style.display = 'block';
            otherCategoryInput.required = true;
        } else {
            otherCategoryDiv.style.display = 'none';
            otherCategoryInput.required = false;
            otherCategoryInput.value = '';
        }
    }
</script>