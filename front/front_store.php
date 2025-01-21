<?php
$page_title = 'Product Catalog';
include 'header.php';
require_once '../config/db.php'; // Updated to use OOP-based db.php

// Initialize the Database connection
$db = new Database();
$pdo = $db->getConnection();

// User Class
class User {
    private $pdo;
    private $userId;

    public function __construct($pdo, $userId) {
        $this->pdo = $pdo;
        $this->userId = $userId;
    }

    public function getName() {
        try {
            $stmt = $this->pdo->prepare("SELECT name FROM users WHERE id = :user_id");
            $stmt->execute(['user_id' => $this->userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            return $user ? $user['name'] : 'Guest';
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return 'Guest';
        }
    }
}

// ProductCatalog Class
class ProductCatalog {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getCategories() {
        try {
            $stmt = $this->pdo->query("SELECT DISTINCT category FROM products WHERE category IS NOT NULL");
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            error_log("Database error fetching categories: " . $e->getMessage());
            return [];
        }
    }

    public function getProducts($filters) {
        $sql = "SELECT * FROM products WHERE 1=1";
        $params = [];

        if (!empty($filters['search'])) {
            $sql .= " AND (product_name LIKE :search OR description LIKE :search)";
            $params['search'] = "%" . $filters['search'] . "%";
        }

        if (!empty($filters['category'])) {
            $sql .= " AND category = :category";
            $params['category'] = $filters['category'];
        }

        if (!empty($filters['min_price'])) {
            $sql .= " AND price >= :min_price";
            $params['min_price'] = $filters['min_price'];
        }

        if (!empty($filters['max_price'])) {
            $sql .= " AND price <= :max_price";
            $params['max_price'] = $filters['max_price'];
        }

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database error fetching products: " . $e->getMessage());
            return [];
        }
    }
}

// Cart Class
class Cart {
    private $pdo;
    private $userId;

    public function __construct($pdo, $userId) {
        $this->pdo = $pdo;
        $this->userId = $userId;
    }

    public function getCartItems() {
        try {
            $stmt = $this->pdo->prepare("SELECT product_id, quantity FROM cart WHERE user_id = ?");
            $stmt->execute([$this->userId]);
            return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (PDOException $e) {
            error_log("Database error fetching cart items: " . $e->getMessage());
            return [];
        }
    }
}

// Initialize classes
$userId = $_SESSION['user_id'] ?? null;
$user = new User($pdo, $userId);
$productCatalog = new ProductCatalog($pdo);
$cart = new Cart($pdo, $userId);

// Get data
$userName = $user->getName();
$categories = $productCatalog->getCategories();
$filters = [
    'search' => $_GET['search'] ?? '',
    'category' => $_GET['category'] ?? '',
    'min_price' => $_GET['min_price'] ?? '',
    'max_price' => $_GET['max_price'] ?? ''
];
$products = $productCatalog->getProducts($filters);
$cartItems = $cart->getCartItems();
?>

<div class="content-container">
    <!-- Welcome Message -->
    <div class="alert alert-warning d-flex align-items-center" role="alert">
        <i class="bi bi-person-circle me-3" style="font-size: 1.5rem;"></i>
        <div>
            Welcome, <strong><?php echo htmlspecialchars($userName); ?></strong>! Check out our products below.
        </div>
    </div>
    
    <!-- Search and Filter Section -->
    <form method="GET" action="" class="row g-3 align-items-center mb-4">
        <div class="col-md-4">
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="text" name="search" class="form-control" placeholder="Search products..."
                       value="<?php echo htmlspecialchars($filters['search']); ?>">
            </div>
        </div>
        <div class="col-md-2">
            <select name="category" class="form-select">
                <option value="">All Categories</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo htmlspecialchars($category); ?>"
                            <?php echo $filters['category'] === $category ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($category); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <input type="number" name="min_price" class="form-control" placeholder="Min Price"
                   value="<?php echo htmlspecialchars($filters['min_price']); ?>" min="0" step="0.01">
        </div>
        <div class="col-md-2">
            <input type="number" name="max_price" class="form-control" placeholder="Max Price"
                   value="<?php echo htmlspecialchars($filters['max_price']); ?>" min="0" step="0.01">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-custom w-100">
                <i class="bi bi-funnel"></i> Filter
            </button>
        </div>
    </form>
    
    <!-- Product Listing -->
    <h2 class="mb-4">Product Catalog 
        <?php if (!empty($filters['search']) || !empty($filters['category']) || !empty($filters['min_price']) || !empty($filters['max_price'])): ?>
            <small class="text-muted">
                (<?php echo count($products); ?> results found)
            </small>
        <?php endif; ?>
    </h2>

    <?php if (empty($products)): ?>
        <div class="alert alert-info text-center">
            <i class="bi bi-search me-2"></i>No products found matching your search criteria.
        </div>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-md-3 g-4">
            <?php foreach ($products as $product): ?>
                <div class="col">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">
                                <?php echo htmlspecialchars($product['product_name']); ?>
                                <?php if (!empty($product['category'])): ?>
                                    <span class="badge bg-secondary float-end"><?php echo htmlspecialchars($product['category']); ?></span>
                                <?php endif; ?>
                            </h5>
                            <p class="card-text flex-grow-1"><?php echo htmlspecialchars($product['description']); ?></p>
                            <div class="mb-2">
                                <p class="card-text">Price: ₱<?php echo number_format($product['price'], 2); ?></p>
                                <p class="card-text text-muted">Available Quantity: <?php echo htmlspecialchars($product['quantity']); ?></p>
                            </div>
                            <div class="mt-auto">
                                <?php 
                                $currentCartQty = $cartItems[$product['id']] ?? 0;
                                $maxAllowedQty = floor($product['quantity'] / 2);

                                if ($product['quantity'] > 0 && $currentCartQty < $maxAllowedQty): 
                                ?>
                                    <a href="add_to_cart.php?id=<?php echo $product['id']; ?>" class="btn btn-custom w-100">
                                        <i class="bi bi-cart-plus"></i> Add to Cart
                                    </a>
                                <?php else: ?>
                                    <?php if ($currentCartQty >= $maxAllowedQty): ?>
                                        <button class="btn btn-warning w-100" disabled>
                                            <i class="bi bi-exclamation-triangle"></i> Max Cart Limit Reached
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-secondary w-100" disabled>
                                            <i class="bi bi-cart-x"></i> Out of Stock
                                        </button>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('form');
    const clearFiltersBtn = document.createElement('button');
    clearFiltersBtn.innerHTML = '<i class="bi bi-x-circle"></i> Clear Filters';
    clearFiltersBtn.classList.add('btn', 'btn-outline-secondary', 'ms-2');

    clearFiltersBtn.addEventListener('click', (e) => {
        e.preventDefault();
        form.querySelectorAll('input, select').forEach(field => field.value = '');
        form.submit();
    });

    const hasActiveFilter = 
        document.querySelector('input[name="search"]').value.trim() !== '' ||
        document.querySelector('select[name="category"]').value !== '' ||
        document.querySelector('input[name="min_price"]').value !== '' ||
        document.querySelector('input[name="max_price"]').value !== '';

    if (hasActiveFilter) {
        form.querySelector('.col-md-2:last-child').appendChild(clearFiltersBtn);
    }
});
</script>

<?php include 'footer.php'; ?>