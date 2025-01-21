<?php
session_start();
include '../config/db.php';

class Checkout {
    private $pdo;
    private $userId;

    public function __construct($pdo) {
        $this->pdo = $pdo;

        // Ensure user is logged in
        if (!isset($_SESSION['user_id'])) {
            header('Location: ../login.php');
            exit();
        }

        $this->userId = $_SESSION['user_id'];
    }

    public function processCheckout($shippingAddress, $paymentMethod, $selectedItems) {
        try {
            $this->pdo->beginTransaction();

            // Prepare order details
            $orderDetails = [
                'user_id' => $this->userId,
                'shipping_address' => trim($shippingAddress),
                'payment_method' => trim($paymentMethod),
                'items' => [],
                'total' => 0,
            ];

            // Get selected cart items
            $cartItems = $this->getCartItems($selectedItems);

            foreach ($cartItems as $item) {
                $itemTotal = $item['price'] * $item['quantity'];
                $orderDetails['items'][] = [
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product_name'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ];
                $orderDetails['total'] += $itemTotal;

                // Remove purchased items from the cart
                $this->removeFromCart($item['product_id']);
            }

            $this->pdo->commit();

            // Store order details in session and redirect to confirmation
            $_SESSION['order_details'] = $orderDetails;
            header('Location: confirmation.php');
            exit();

        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Checkout error: " . $e->getMessage());
            header('Location: view_cart.php?error=checkout_failed');
            exit();
        }
    }

    private function getCartItems($selectedItems) {
        $placeholders = implode(',', array_fill(0, count($selectedItems), '?'));
        $stmt = $this->pdo->prepare("
            SELECT c.*, p.product_name, p.price 
            FROM cart c 
            JOIN products p ON c.product_id = p.id 
            WHERE c.user_id = ? AND c.product_id IN ($placeholders)
        ");
        $stmt->execute(array_merge([$this->userId], $selectedItems));
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function removeFromCart($productId) {
        $stmt = $this->pdo->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$this->userId, $productId]);
    }
}

// Ensure the form submission is valid
if ($_SERVER['REQUEST_METHOD'] === 'POST' && 
    isset($_POST['shipping_address'], $_POST['payment_method'], $_POST['selected_items'])) {

    // Initialize the Database connection
    $db = new Database();
    $pdo = $db->getConnection();

    $checkout = new Checkout($pdo);
    $checkout->processCheckout($_POST['shipping_address'], $_POST['payment_method'], $_POST['selected_items']);
} else {
    // Redirect back if form submission is invalid
    header('Location: view_cart.php');
    exit();
}
?>
