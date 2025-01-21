<?php
session_start();
include 'header.php';
include '../config/db.php';

class CheckoutManager
{
    private $pdo;
    private $userId;

    public function __construct($pdo, $userId)
    {
        $this->pdo = $pdo;
        $this->userId = $userId;
    }

    public function processCheckout($selectedItems)
    {
        if (empty($selectedItems)) {
            $_SESSION['message'] = 'No items selected for checkout.';
            header('Location: view_cart.php');
            exit();
        }

        $placeholders = str_repeat('?,', count($selectedItems) - 1) . '?';
        $stmt = $this->pdo->prepare("
            SELECT p.*, c.quantity AS cart_quantity 
            FROM products p 
            JOIN cart c ON p.id = c.product_id 
            WHERE p.id IN ($placeholders) AND c.user_id = ?
        ");
        $queryParams = array_merge($selectedItems, [$this->userId]);
        $stmt->execute($queryParams);

        $productData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $selectedProducts = [];
        $grandTotal = 0;

        foreach ($productData as $product) {
            $totalProductPrice = $product['price'] * $product['cart_quantity'];
            $selectedProducts[] = [
                'id' => $product['id'],
                'product_name' => $product['product_name'],
                'price' => $product['price'],
                'quantity' => $product['cart_quantity']
            ];
            $grandTotal += $totalProductPrice;
        }

        return [
            'products' => $selectedProducts,
            'grand_total' => $grandTotal
        ];
    }
}

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Initialize the Database connection
$db = new Database();
$pdo = $db->getConnection();

$checkoutData = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selected_items'])) {
    $selectedItems = $_POST['selected_items'];
    $userId = $_SESSION['user_id'];

    $checkoutManager = new CheckoutManager($pdo, $userId);
    $checkoutData = $checkoutManager->processCheckout($selectedItems);
} else {
    header('Location: view_cart.php');
    exit();
}

$selectedProducts = $checkoutData['products'];
$grandTotal = $checkoutData['grand_total'];
?>


<!-- HTML Template -->
<div class="content-container">
    <h2 class="mb-4">Checkout</h2>
    
    <div class="checkout-wrapper">
        <form action="process_checkout.php" method="post" id="checkout-form">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($selectedProducts as $product): ?>
                            <tr>
                                <td>
                                    <span class="fw-bold"><?php echo htmlspecialchars($product['product_name']); ?></span>
                                </td>
                                <td>₱<?php echo number_format($product['price'], 2); ?></td>
                                <td>x<?php echo htmlspecialchars($product['quantity']); ?></td>
                                <td>₱<?php echo number_format($product['price'] * $product['quantity'], 2); ?></td>
                                
                                <!-- Hidden input for selected items -->
                                <input type="hidden" name="selected_items[]" value="<?php echo htmlspecialchars($product['id']); ?>">
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Checkout Summary -->
            <div class="card mt-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h5 class="card-title">Order Summary</h5>
                        </div>
                        <div class="col-md-4 text-end">
                            <h5>Grand Total: <span>₱<?php echo number_format($grandTotal, 2); ?></span></h5>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Shipping and Payment Details -->
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <i class="bi bi-truck me-2"></i>Shipping Information
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="shipping_address" class="form-label">Shipping Address</label>
                                <textarea id="shipping_address" name="shipping_address" class="form-control" rows="4" required placeholder="Enter your complete shipping address"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <i class="bi bi-credit-card me-2"></i>Payment Method
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="payment_method" class="form-label">Select Payment Method</label>
                                <select id="payment_method" name="payment_method" class="form-select" required>
                                    <option value="">Choose Payment Method</option>
                                    <option value="Cash on Delivery">Cash on Delivery</option>
                                    <option value="Credit/Debit Card">Credit/Debit Card</option>
                                    <option value="GCash">GCash</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="d-flex justify-content-between mt-4">
                <a href="view_cart.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Back to Cart
                </a>
                <button type="submit" class="btn btn-custom" id="confirm-order-button">
                    <i class="bi bi-check-circle me-2"></i>Confirm Order
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const checkoutForm = document.getElementById('checkout-form');
    const confirmOrderButton = document.getElementById('confirm-order-button');
    const shippingAddressTextarea = document.getElementById('shipping_address');
    const paymentMethodSelect = document.getElementById('payment_method');

    checkoutForm.addEventListener('submit', (event) => {
        const shippingAddress = shippingAddressTextarea.value.trim();
        const paymentMethod = paymentMethodSelect.value;

        if (!shippingAddress) {
            event.preventDefault();
            alert('Please provide a shipping address.');
            shippingAddressTextarea.focus();
            return;
        }

        if (!paymentMethod) {
            event.preventDefault();
            alert('Please select a payment method.');
            paymentMethodSelect.focus();
            return;
        }
    });
});
</script>

<?php include 'footer.php'; ?>
