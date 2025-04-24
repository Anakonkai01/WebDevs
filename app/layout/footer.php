<?php // Web/app/Views/layout/footer.php ?>

</main> <?php // Đóng thẻ main từ header.php ?>

<?php // --- Footer Section --- ?>
<footer class="site-footer mt-auto bg-dark text-white pt-5 pb-4"> <?php // Use bg-dark, text-white, more padding ?>
    <div class="container">
        <div class="row gy-4"> <?php // Bootstrap grid row with vertical gutter ?>

            <?php // Column 1: About ?>
            <div class="col-lg-3 col-md-6">
                <h5 class="mb-3 fw-semibold">Về MyShop</h5> <?php // Increased heading size slightly, added bottom margin ?>
                <p class="small text-white-50"> <?php // Use text-white-50 for better contrast than text-muted ?>
                    MyShop là cửa hàng cung cấp các sản phẩm công nghệ chính hãng với giá tốt nhất, đảm bảo chất lượng và dịch vụ hậu mãi chu đáo.
                </p>
            </div>

            <?php // Column 2: Quick Links ?>
            <div class="col-lg-3 col-md-6">
                <h5 class="mb-3 fw-semibold">Liên kết nhanh</h5>
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <a href="?page=home" class="link-light text-decoration-none">Trang chủ</a> <?php // Use link-light for white links, remove underline ?>
                    </li>
                    <li class="mb-2">
                        <a href="?page=shop_grid" class="link-light text-decoration-none">Cửa hàng</a>
                    </li>
                    <li class="mb-2">
                        <a href="#" class="link-light text-decoration-none">Giới thiệu</a>
                    </li>
                    <li class="mb-2">
                        <a href="?page=contact" class="link-light text-decoration-none">Liên hệ</a>
                    </li>
                </ul>
            </div>

            <?php // Column 3: Customer Support ?>
            <div class="col-lg-3 col-md-6">
                <h5 class="mb-3 fw-semibold">Hỗ trợ khách hàng</h5>
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <a href="#" class="link-light text-decoration-none">Chính sách đổi trả</a>
                    </li>
                    <li class="mb-2">
                        <a href="#" class="link-light text-decoration-none">Chính sách bảo mật</a>
                    </li>
                    <li class="mb-2">
                        <a href="#" class="link-light text-decoration-none">Điều khoản dịch vụ</a>
                    </li>
                </ul>
            </div>

            <?php // Column 4: Contact Info ?>
            <div class="col-lg-3 col-md-6">
                <h5 class="mb-3 fw-semibold">Liên hệ</h5>
                <ul class="list-unstyled">
                    <?php // Improved contact item structure and styling ?>
                    <li class="mb-2 d-flex">
                        <i class="fas fa-map-marker-alt mt-1 me-2 text-secondary" style="width: 15px;"></i> <?php // Added fixed width for alignment ?>
                        <span class="small text-white-50">123 Đường ABC, Q. XYZ, TP.HCM</span>
                    </li>
                    <li class="mb-2 d-flex">
                        <i class="fas fa-phone-alt mt-1 me-2 text-secondary" style="width: 15px;"></i>
                        <a href="tel:0987654321" class="link-light text-decoration-none small">0987 654 321</a>
                    </li>
                    <li class="mb-2 d-flex">
                        <i class="fas fa-envelope mt-1 me-2 text-secondary" style="width: 15px;"></i>
                        <a href="mailto:contact@myshop.com" class="link-light text-decoration-none small">contact@myshop.com</a>
                    </li>
                </ul>
            </div>
        </div> <?php // End row ?>

        <hr class="my-4" style="border-color: rgba(255, 255, 255, 0.15);"> <?php // Lighter horizontal rule ?>

        <div class="text-center text-white-50 small"> <?php // Use text-white-50 and small ?>
            &copy; <?= date('Y') ?> MyShop. All Rights Reserved.
        </div>
    </div> <?php // End container ?>
</footer>
<?php // --- END Footer Section --- ?>


<?php // --- JavaScript Includes --- ?>
<script src="http://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script>
    // Your custom scripts (if any)
</script>
<?php // --------------------------- ?>

</body>
</html>