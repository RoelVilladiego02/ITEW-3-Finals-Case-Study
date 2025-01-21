<?php
session_start();
require_once '../config/db.php'; // Include the database configuration file

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

class CartManager
{
    private $pdo;
    private $userId;

    public function __construct($pdo, $userId)
    {
        $this->pdo = $pdo;
        $this->userId = $userId;
    }

    public function updateCart($productId, $action)
    {
        try {
            $this->pdo->beginTransaction();
            switch ($action) {
                case 'add':
                    $this->addProductToCart($productId);
                    break;
                case 'subtract':
                    $this->subtractProductFromCart($productId);
                    break;
                case 'remove':
                    $this->removeProductFromCart($productId);
                    break;
                default:
                    throw new Exception("Invalid action: $action");
            }
            $this->pdo->commit();
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Database error: " . $e->getMessage());
        }
    }

    private function addProductToCart($productId)
    {
        $product = $this->getProductById($productId);
        if ($product && $product['quantity'] > 0) {
            // Update cart quantity
            $stmt = $this->pdo->prepare("UPDATE cart SET quantity = quantity + 1 WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$this->userId, $productId]);

            // Decrement product stock
            $stmt = $this->pdo->prepare("UPDATE products SET quantity = quantity - 1 WHERE id = ?");
            $stmt->execute([$productId]);
        }
    }

    private function subtractProductFromCart($productId)
    {
        $cartItem = $this->getCartItem($productId);
        if ($cartItem) {
            if ($cartItem['quantity'] > 1) {
                // Decrement cart quantity
                $stmt = $this->pdo->prepare("UPDATE cart SET quantity = quantity - 1 WHERE user_id = ? AND product_id = ?");
                $stmt->execute([$this->userId, $productId]);

                // Increment product stock
                $stmt = $this->pdo->prepare("UPDATE products SET quantity = quantity + 1 WHERE id = ?");
                $stmt->execute([$productId]);
            } else {
                $this->removeProductFromCart($productId);
            }
        }
    }

    private function removeProductFromCart($productId)
    {
        $cartItem = $this->getCartItem($productId);
        if ($cartItem) {
            // Remove item from cart
            $stmt = $this->pdo->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$this->userId, $productId]);

            // Restore product stock
            $stmt = $this->pdo->prepare("UPDATE products SET quantity = quantity + ? WHERE id = ?");
            $stmt->execute([$cartItem['quantity'], $productId]);
        }
    }

    private function getProductById($productId)
    {
        $stmt = $this->pdo->prepare("SELECT quantity FROM products WHERE id = ?");
        $stmt->execute([$productId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function getCartItem($productId)
    {
        $stmt = $this->pdo->prepare("SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$this->userId, $productId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

if (isset($_GET['id'], $_GET['action'])) {
    $productId = (int) $_GET['id']; // Sanitize product ID
    $action = $_GET['action'];

    // Step 1: Initialize the database and get the PDO connection
    $db = new Database();
    $pdo = $db->getConnection();

    // Step 2: Create CartManager instance with the PDO connection
    $userId = $_SESSION['user_id'];
    $cartManager = new CartManager($pdo, $userId);
    $cartManager->updateCart($productId, $action);
}

header('Location: view_cart.php');
exit();
?>
