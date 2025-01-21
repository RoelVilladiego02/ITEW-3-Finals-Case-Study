<?php
session_start();
include '../config/db.php';
include '../admin/header.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../front/login.php');
    exit();
}

// Initialize the Database connection
$db = new Database();
$pdo = $db->getConnection();

$error = '';
$success = '';

// Process form submission for deleting a product
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $productId = intval($_POST['product_id']); // Sanitize input

    try {
        // Prepare and execute the delete statement
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        if ($stmt->execute([$productId])) {
            $success = "Product deleted successfully!";
        } else {
            $error = "Failed to delete product.";
        }
    } catch (PDOException $e) {
        $error = "An error occurred while deleting the product: " . htmlspecialchars($e->getMessage());
    }
}

// Fetch all products for the list
try {
    $stmt = $pdo->query("SELECT * FROM products");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $products = [];
    $error = "Failed to fetch products: " . htmlspecialchars($e->getMessage());
}
?>


<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <h2 class="card-title text-center mb-0">
                        <i class="bi bi-trash text-danger me-2"></i>Delete Products
                    </h2>
                </div>
            </div>
        </div>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php elseif ($success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($success); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (empty($products)): ?>
        <div class="row">
            <div class="col-12">
                <div class="card text-center border-0 shadow-hover">
                    <div class="card-body py-5">
                        <i class="bi bi-box-seam text-muted fs-1 mb-3"></i>
                        <h4 class="card-title mb-3">No Products Found</h4>
                        <p class="card-text text-muted mb-4">It seems there are no products in your inventory.</p>
                        <a href="add_product.php" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-2"></i>Add Product
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Product ID</th>
                                <th>Product Name</th>
                                <th>Barcode</th>
                                <th>Description</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($product['id']); ?></td>
                                <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                                <td><?php echo htmlspecialchars($product['barcode']); ?></td>
                                <td><?php echo htmlspecialchars($product['description']); ?></td>
                                <td>₱<?php echo number_format(htmlspecialchars($product['price']), 2); ?></td>
                                <td><?php echo htmlspecialchars($product['quantity']); ?></td>
                                <td>
                                    <form action="delete_product.php" method="POST" onsubmit="return confirmDelete();">
                                        <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product['id']); ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash me-1"></i>Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
    .table-hover tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.05);
        transition: background-color 0.3s ease;
    }
    .shadow-hover {
        transition: box-shadow 0.3s ease;
    }
    .shadow-hover:hover {
        box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
    }
    .btn-outline-danger:hover {
        color: white !important;
    }
</style>

<script>
function confirmDelete() {
    return confirm("Are you sure you want to delete this product?");
}
</script>
</body>
</html>