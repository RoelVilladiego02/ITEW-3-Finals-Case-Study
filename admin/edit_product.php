<?php
session_start();
include '../config/db.php';

// Ensure the user is logged in and has the 'admin' role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../front/login.php');
    exit();
}

// Initialize the Database connection
$db = new Database();
$pdo = $db->getConnection();

// Initialize variables
$error = '';
$success = '';
$product = null;

// Check if a product ID is provided for editing
if (isset($_GET['id'])) {
    $product_id = intval($_GET['id']); // Sanitize input
    try {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            $error = "Product not found.";
        }
    } catch (PDOException $e) {
        $error = "Failed to fetch product: " . htmlspecialchars($e->getMessage());
    }
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_id = intval($_POST['product_id']); // Sanitize input
    $barcode = trim($_POST['barcode']);
    $product_name = trim($_POST['product_name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $quantity = intval($_POST['quantity']);
    $category = trim($_POST['category']);

    // If 'Other' is selected, use the custom category
    if ($category === 'Other') {
        $category = trim($_POST['other_category'] ?? '');
    }

    try {
        // Update product in the database
        $stmt = $pdo->prepare("
            UPDATE products 
            SET barcode = ?, product_name = ?, description = ?, price = ?, quantity = ?, category = ? 
            WHERE id = ?
        ");
        $stmt->execute([$barcode, $product_name, $description, $price, $quantity, $category, $product_id]);

        $success = "Product updated successfully!";
        // Refresh product data after update
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Handle duplicate barcode error
        if ($e->getCode() == 23000 && strpos($e->getMessage(), 'Duplicate entry') !== false) {
            $error = "The barcode '{$barcode}' is already in use. Please choose a unique barcode.";
        } else {
            $error = "An unexpected error occurred: " . htmlspecialchars($e->getMessage());
        }
    }
}

// Fetch all products for the dropdown
try {
    $stmt = $pdo->query("SELECT id, product_name FROM products");
    $all_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $all_products = [];
    $error = "Failed to fetch products for the dropdown: " . htmlspecialchars($e->getMessage());
}
?>


<?php include 'header.php'; ?>

<div class="container-fluid my-5">
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">
            <div class="card shadow-lg border-0 rounded-lg">
                <div class="card-header bg-gradient-primary text-white text-center py-4">
                    <h2 class="mb-0">
                        <i class="bi bi-pencil-square me-2"></i>Edit Product
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

                    <form action="edit_product.php" method="POST" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="product_select" class="form-label">
                                <i class="bi bi-search me-2 text-primary"></i>Select Product to Edit
                            </label>
                            <select class="form-select" id="product_select" onchange="loadProductDetails(this.value)">
                                <option value="" disabled selected>Choose a product</option>
                                <?php foreach ($all_products as $prod): ?>
                                    <option value="<?php echo htmlspecialchars($prod['id']); ?>">
                                        <?php echo htmlspecialchars($prod['product_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div id="product_edit_section" <?php echo $product ? '' : 'style="display:none;"'; ?>>
                            <input type="hidden" name="product_id" id="product_id" value="<?php echo $product ? htmlspecialchars($product['id']) : ''; ?>">
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="barcode" class="form-label">
                                        <i class="bi bi-barcode me-2 text-primary"></i>Product Barcode
                                    </label>
                                    <input type="text" class="form-control" id="barcode" name="barcode" 
                                           value="<?php echo $product ? htmlspecialchars($product['barcode']) : ''; ?>" required>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="product_name" class="form-label">
                                        <i class="bi bi-tag me-2 text-primary"></i>Product Name
                                    </label>
                                    <input type="text" class="form-control" id="product_name" name="product_name" 
                                           value="<?php echo $product ? htmlspecialchars($product['product_name']) : ''; ?>" required>
                                </div>
                                
                                <div class="col-12">
                                    <label for="description" class="form-label">
                                        <i class="bi bi-clipboard-fill me-2 text-primary"></i>Description
                                    </label>
                                    <textarea class="form-control" id="description" name="description" rows="3" required><?php 
                                        echo $product ? htmlspecialchars($product['description']) : ''; 
                                    ?></textarea>
                                </div>
                                
                                <div class="col-md-4">
                                    <label for="price" class="form-label">
                                        <i class="bi bi-cash me-2 text-primary"></i>Price
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">₱</span>
                                        <input type="number" step="0.01" class="form-control" id="price" name="price" 
                                               value="<?php echo $product ? htmlspecialchars($product['price']) : ''; ?>" required>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <label for="quantity" class="form-label">
                                        <i class="bi bi-boxes me-2 text-primary"></i>Quantity
                                    </label>
                                    <input type="number" class="form-control" id="quantity" name="quantity" 
                                           value="<?php echo $product ? htmlspecialchars($product['quantity']) : ''; ?>" required>
                                </div>
                                
                                <div class="col-md-4">
                                    <label for="category" class="form-label">
                                        <i class="bi bi-folder me-2 text-primary"></i>Category
                                    </label>
                                    <select class="form-select" id="category" name="category" required onchange="toggleOtherCategory()">
                                        <option value="" disabled>Select Category</option>
                                        <option value="Electronics and Gadgets" <?php echo ($product && $product['category'] === 'Electronics and Gadgets') ? 'selected' : ''; ?>>Electronics and Gadgets</option>
                                        <option value="Fashion and Apparel" <?php echo ($product && $product['category'] === 'Fashion and Apparel') ? 'selected' : ''; ?>>Fashion and Apparel</option>
                                        <option value="Health and Beauty" <?php echo ($product && $product['category'] === 'Health and Beauty') ? 'selected' : ''; ?>>Health and Beauty</option>
                                        <option value="Home and Kitchen Appliances" <?php echo ($product && $product['category'] === 'Home and Kitchen Appliances') ? 'selected' : ''; ?>>Home and Kitchen Appliances</option>
                                        <option value="Other" <?php echo ($product && !in_array($product['category'], ['Electronics and Gadgets', 'Fashion and Apparel', 'Health and Beauty', 'Home and Kitchen Appliances'])) ? 'selected' : ''; ?>>Other</option>
                                    </select>
                                </div>
                                
                                <div class="col-12 mb-3" id="otherCategoryDiv" style="display: none;">
                                    <label for="otherCategory" class="form-label">
                                        <i class="bi bi-pencil-fill me-2 text-primary"></i>Specify Category
                                    </label>
                                    <input type="text" class="form-control" id="otherCategory" name="other_category" 
                                           value="<?php echo ($product && !in_array($product['category'], ['Electronics and Gadgets', 'Fashion and Apparel', 'Health and Beauty', 'Home and Kitchen Appliances'])) ? htmlspecialchars($product['category']) : ''; ?>" 
                                           placeholder="Enter custom category">
                                </div>
                                
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary w-100 py-2">
                                        <i class="bi bi-save me-2"></i>Update Product
                                    </button>
                                </div>
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
</style>

<script>
    // Dynamic product loading
    function loadProductDetails(productId) {
        window.location.href = 'edit_product.php?id=' + productId;
    }

    // Category toggle
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

    // Initial category check on page load
    document.addEventListener('DOMContentLoaded', function() {
        toggleOtherCategory();
    });
</script>