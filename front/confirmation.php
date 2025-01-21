<?php
$page_title = 'Order Confirmation';
include 'header.php';

if (!isset($_SESSION['order_details'])) {
    header('Location: view_cart.php');
    exit();
}

$order_details = $_SESSION['order_details'];
unset($_SESSION['order_details']); // Clear the order details after displaying them
?>

<div class="content-container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h2 class="mb-0">
                        <i class="bi bi-check-circle me-2"></i>Order Confirmation
                    </h2>
                </div>
                <div class="card-body">
                    <div class="alert alert-success" role="alert">
                        <i class="bi bi-heart me-2"></i>Thank you for your order!
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <i class="bi bi-truck me-2"></i>Shipping Information
                                </div>
                                <div class="card-body">
                                    <p class="card-text">
                                        <strong>Address:</strong> 
                                        <?php echo htmlspecialchars($order_details['shipping_address']); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <i class="bi bi-credit-card me-2"></i>Payment Details
                                </div>
                                <div class="card-body">
                                    <p class="card-text">
                                        <strong>Method:</strong> 
                                        <?php echo htmlspecialchars($order_details['payment_method']); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <i class="bi bi-basket me-2"></i>Order Summary
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Product</th>
                                        <th>Quantity</th>
                                        <th class="text-end">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $total = 0;
                                    foreach ($order_details['items'] as $item): 
                                        $subtotal = $item['price'] * $item['quantity'];
                                        $total += $subtotal;
                                    ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                            <td>x<?php echo htmlspecialchars($item['quantity']); ?></td>
                                            <td class="text-end">₱<?php echo number_format($subtotal, 2); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body text-end">
                            <h4 class="card-title">
                                Total: <span class="text-primary">₱<?php echo number_format($total, 2); ?></span>
                            </h4>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <a href="front_store.php" class="btn btn-secondary">
                            <i class="bi bi-shop me-2"></i>Continue Shopping
                        </a>
                        <a href="view_cart.php" class="btn btn-custom">
                            <i class="bi bi-cart me-2"></i>Back to Cart
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>