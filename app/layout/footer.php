<?php
// Web/app/Views/layout/footer.php
?>

</main> <?php // Đóng thẻ main từ header.php ?>

<footer class="site-footer" style="background-color: #343a40; color: #f8f9fa; padding: 40px 0; margin-top: 40px;">
    <div class="container">
        <div style="display: flex; justify-content: space-between; flex-wrap: wrap; gap: 20px;">
            <div>
                <h4>Về MyShop</h4>
                <p style="max-width: 300px; color: #adb5bd;">MyShop là cửa hàng cung cấp các sản phẩm công nghệ chính hãng với giá tốt nhất.</p>
            </div>
            <div>
                <h4>Liên kết nhanh</h4>
                <ul style="list-style: none; padding: 0;">
                    <li style="margin-bottom: 8px;"><a href="?page=home" style="color: #adb5bd;">Trang chủ</a></li>
                    <li style="margin-bottom: 8px;"><a href="?page=shop_grid" style="color: #adb5bd;">Cửa hàng</a></li>
                    <li style="margin-bottom: 8px;"><a href="#" style="color: #adb5bd;">Giới thiệu</a></li>
                    <li style="margin-bottom: 8px;"><a href="?page=contact" style="color: #adb5bd;">Liên hệ</a></li>
                </ul>
            </div>
            <div>
                <h4>Hỗ trợ khách hàng</h4>
                <ul style="list-style: none; padding: 0;">
                    <li style="margin-bottom: 8px;"><a href="#" style="color: #adb5bd;">Chính sách đổi trả</a></li>
                    <li style="margin-bottom: 8px;"><a href="#" style="color: #adb5bd;">Chính sách bảo mật</a></li>
                    <li style="margin-bottom: 8px;"><a href="#" style="color: #adb5bd;">Điều khoản dịch vụ</a></li>
                </ul>
            </div>
            <div>
                <h4>Liên hệ</h4>
                <p style="color: #adb5bd;">Địa chỉ: 123 Đường ABC, Quận XYZ, TP.HCM</p>
                <p style="color: #adb5bd;">Điện thoại: 0987 654 321</p>
                <p style="color: #adb5bd;">Email: contact@myshop.com</p>
            </div>
        </div>
        <hr style="border-color: #495057; margin: 30px 0;">
        <div style="text-align: center; color: #adb5bd; font-size: 0.9em;">
            &copy; <?= date('Y') ?> MyShop. All Rights Reserved.
        </div>
    </div>
</footer>

<?php // Thêm các link JS cần thiết ở đây (nếu có) ?>
<?php /* <script src="/public/js/main.js"></script> */ ?>

</body>
</html>