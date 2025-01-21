<?php
session_start();
require_once '../config/db.php'; // Include the database configuration file

class CartManager
{
    private $pdo;
    private $userId;

    public function __construct($pdo, $userId)
    {
        $this->pdo = $pdo;
        $this->userId = $userId;
    }

    public function addProductToCart($productId)
    {
        try {
            $product = $this->fetchProduct($productId);

            if ($product && $product['quantity'] > 0) {
                $existingCartItem = $this->fetchCartItem($productId);

                if ($existingCartItem) {
                    $this->updateCartQuantity($productId);
                } else {
                    $this->insertCartItem($productId);
                }

                $this->deductProductStock($productId);
                header('Location: view_cart.php');
                exit();
            } else {
                header('Location: front_store.php?error=out_of_stock');
                exit();
            }
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            header('Location: front_store.php?error=cart_add_failed');
            exit();
        }
    }

    private function fetchProduct($productId)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$productId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function fetchCartItem($productId)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$this->userId, $productId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function updateCartQuantity($productId)
    {
        $stmt = $this->pdo->prepare("UPDATE cart SET quantity = quantity + 1 WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$this->userId, $productId]);
    }

    private function insertCartItem($productId)
    {
        $stmt = $this->pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)");
        $stmt->execute([$this->userId, $productId]);
    }

    private function deductProductStock($productId)
    {
        $stmt = $this->pdo->prepare("UPDATE products SET quantity = quantity - 1 WHERE id = ?");
        $stmt->execute([$productId]);
    }
}

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Process product addition
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $productId = (int) $_GET['id']; // Sanitize the product ID
    $userId = $_SESSION['user_id'];

    // Step 1: Initialize the database and get PDO connection
    $db = new Database();
    $pdo = $db->getConnection();

    // Step 2: Create CartManager instance with the PDO connection
    $cartManager = new CartManager($pdo, $userId);
    $cartManager->addProductToCart($productId);
} else {
    header('Location: front_store.php?error=invalid_product');
    exit();
}
?>
