<?php
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

include '../config/db.php';

// Initialize the Database connection
$db = new Database();
$pdo = $db->getConnection();

$user_id = $_SESSION['user_id'];

try {
    // Fetch all products from the database
    $stmt = $pdo->query("SELECT * FROM products");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching products: " . $e->getMessage());
    $products = [];
}
?>


<?php include 'header.php'; ?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <h2 class="card-title text-center mb-0">
                        <i class="bi bi-card-list text-info me-2"></i>Product List
                    </h2>
                </div>
            </div>
        </div>
    </div>

    <?php if (empty($products)): ?>
        <div class="row">
            <div class="col-12">
                <div class="card text-center border-0 shadow-hover">
                    <div class="card-body py-5">
                        <i class="bi bi-box-seam text-muted fs-1 mb-3"></i>
                        <h4 class="card-title mb-3">No Products Found</h4>
                        <p class="card-text text-muted mb-4">Your product catalog is currently empty.</p>
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
                                <th>ID</th>
                                <th>Barcode</th>
                                <th>Product Name</th>
                                <th>Description</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Category</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($product['id']); ?></td>
                                    <td><?php echo htmlspecialchars($product['barcode']); ?></td>
                                    <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                                    <td><?php echo htmlspecialchars($product['description']); ?></td>
                                    <td>₱<?php echo number_format(htmlspecialchars($product['price']), 2); ?></td>
                                    <td><?php echo htmlspecialchars($product['quantity']); ?></td>
                                    <td><?php echo htmlspecialchars($product['category']); ?></td>
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
        background-color: rgba(13, 202, 240, 0.05);
        transition: background-color 0.3s ease;
    }
    .shadow-hover {
        transition: box-shadow 0.3s ease;
    }
    .shadow-hover:hover {
        box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
    }
</style>
</body>
</html>