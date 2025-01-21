<?php
session_start();
include '../config/db.php';
include 'header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

class CartViewer
{
    private $pdo;
    private $userId;

    public function __construct($pdo, $userId)
    {
        $this->pdo = $pdo;
        $this->userId = $userId;
    }

    public function getCartItems()
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT c.*, p.product_name, p.price, p.quantity AS stock_quantity 
                FROM cart c 
                JOIN products p ON c.product_id = p.id 
                WHERE c.user_id = ?
            ");
            $stmt->execute([$this->userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return [];
        }
    }
}

// Step 1: Create Database instance and get the PDO connection
$db = new Database();
$pdo = $db->getConnection();

// Step 2: Instantiate CartViewer with the new $pdo object
$userId = $_SESSION['user_id'];
$cartViewer = new CartViewer($pdo, $userId);
$cartItems = $cartViewer->getCartItems();
?>

<div class="content-container">
    <h2 class="mb-4">Shopping Cart</h2>
    
    <div class="cart-wrapper">
        <form action="checkout.php" method="post" id="cart-form">
            <?php if (!empty($cartItems)): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>
                                    <input type="checkbox" id="select-all" class="form-check-input">
                                </th>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cartItems as $item): ?>
                                <tr class="cart-item">
                                    <td>
                                        <input type="checkbox" name="selected_items[]" value="<?php echo $item['product_id']; ?>" 
                                               class="form-check-input item-checkbox" 
                                               data-price="<?php echo $item['price'] * $item['quantity']; ?>"
                                               data-quantity="<?php echo $item['quantity']; ?>">
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="fw-bold"><?php echo htmlspecialchars($item['product_name']); ?></span>
                                        </div>
                                    </td>
                                    <td>₱<?php echo number_format($item['price'], 2); ?></td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <?php if ($item['quantity'] > 1): ?>
                                                <a href="update_cart.php?id=<?php echo $item['product_id']; ?>&action=subtract" 
                                                   class="btn btn-outline-secondary">
                                                    <i class="bi bi-dash"></i>
                                                </a>
                                            <?php else: ?>
                                                <a href="update_cart.php?id=<?php echo $item['product_id']; ?>&action=subtract" 
                                                   class="btn btn-outline-secondary" 
                                                   onclick="return confirm('Are you sure you want to remove this item from the cart?')">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            <?php endif; ?>
                                            
                                            <span class="input-group-text bg-white"><?php echo htmlspecialchars($item['quantity']); ?></span>
                                
                                            <?php if ($item['quantity'] < $item['stock_quantity']): ?>
                                                <a href="update_cart.php?id=<?php echo $item['product_id']; ?>&action=add" 
                                                   class="btn btn-outline-secondary">
                                                    <i class="bi bi-plus"></i>
                                                </a>
                                            <?php else: ?>
                                                <button class="btn btn-outline-secondary" disabled>
                                                    <i class="bi bi-plus"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>₱<?php echo number_format($item['price'] * $item['quantity'], 1); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Cart Summary -->
                <div class="card mt-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <p class="card-text">Total Items Selected: <span id="selected-items-count">0</span></p>
                            </div>
                            <div class="col-md-4 text-end">
                                <h5 class="card-title">Grand Total: <span id="grand-total">₱0.00</span></h5>
                            </div>
                        </div>
                        <div class="text-end mt-3">
                            <button type="submit" class="btn btn-custom" id="checkout-button" disabled>
                                <i class="bi bi-cart-check"></i> Proceed to Checkout
                            </button>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-info text-center">
                    <i class="bi bi-cart-x me-2" style="font-size: 2rem;"></i>
                    Your cart is empty! <a href="front_store.php" class="alert-link">Continue Shopping</a>
                </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const checkboxes = document.querySelectorAll('.item-checkbox');
    const selectAllCheckbox = document.getElementById('select-all');
    const grandTotalElement = document.getElementById('grand-total');
    const selectedItemsCountElement = document.getElementById('selected-items-count');
    const cartForm = document.getElementById('cart-form');
    const checkoutButton = document.getElementById('checkout-button');

    function calculateGrandTotal() {
        let grandTotal = 0;
        let selectedItemsCount = 0;

        checkboxes.forEach(checkbox => {
            if (checkbox.checked) {
                grandTotal += parseFloat(checkbox.dataset.price);
                selectedItemsCount++;
            }
        });

        grandTotalElement.textContent = `₱${grandTotal.toFixed(2)}`;
        selectedItemsCountElement.textContent = selectedItemsCount;
        
        checkoutButton.disabled = selectedItemsCount === 0;
    }

    // Select/Deselect all items
    selectAllCheckbox.addEventListener('change', () => {
        checkboxes.forEach(checkbox => {
            checkbox.checked = selectAllCheckbox.checked;
        });
        calculateGrandTotal();
    });

    // Individual item checkbox
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', calculateGrandTotal);
    });

    // Form submission validation
    cartForm.addEventListener('submit', (event) => {
        const selectedItems = Array.from(checkboxes).filter(checkbox => checkbox.checked);
        
        if (selectedItems.length === 0) {
            event.preventDefault();
            alert('Please select at least one product to proceed to checkout.');
        }
    });

    // Initial calculation
    calculateGrandTotal();
});
</script>

<?php include 'footer.php'; ?>