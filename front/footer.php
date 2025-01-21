<?php
// Footer content
$current_year = date('Y');
$site_name = 'Front Store';
?>
    </div>

    <!-- Footer -->
    <footer class="footer mt-auto py-4 bg-dark text-white">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-3 mb-md-0">
                    <h5 class="mb-3">
                        <i class="bi bi-shop me-2"></i><?php echo htmlspecialchars($site_name); ?>
                    </h5>
                </div>
                <div class="col-md-4 mb-3 mb-md-0">
                    <h5 class="mb-3">Quick Navigation</h5>
                    <div class="d-flex flex-column">
                        <a href="front_store.php" class="text-white-50 text-decoration-none mb-2 hover-underline">
                            <i class="bi bi-house-fill me-2"></i>Home
                        </a>
                        <a href="view_cart.php" class="text-white-50 text-decoration-none mb-2 hover-underline">
                            <i class="bi bi-cart-fill me-2"></i>View Cart
                        </a>
                        <?php if(isset($_SESSION['user_id'])): ?>
                            <a href="logout.php" class="text-white-50 text-decoration-none hover-underline">
                                <i class="bi bi-box-arrow-right me-2"></i>Logout
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-4">
                    <h5 class="mb-3">Contact Information</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="bi bi-envelope-fill me-2 text-primary"></i>
                            <a href="mailto:support@frontstore.com" class="text-white-50 text-decoration-none">support@frontstore.com</a>
                        </li>
                        <li>
                            <i class="bi bi-telephone-fill me-2 text-primary"></i>
                            <span class="text-white-50">(555) 123-4567</span>
                        </li>
                    </ul>
                </div>
            </div>
            <hr class="my-4 border-secondary">
            <div class="text-center">
                <p class="mb-2 text-muted">
                    &copy; <?php echo $current_year; ?> <?php echo htmlspecialchars($site_name); ?>. All Rights Reserved.
                </p>
                <small class="text-muted">
                    <i class="bi bi-award me-1"></i>Crafted with passion by Your Company
                </small>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>