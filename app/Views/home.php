<?php
// Web/app/Views/Home.php

// Đặt tiêu đề trang cụ thể cho trang home
$pageTitle = 'Trang chủ';

// Include header layout
include_once __DIR__ . '/../layout/header.php';

// Lấy dữ liệu đã được truyền từ HomeController
$search = $search ?? '';
$brand = $brand ?? ''; // Brand đang được lọc (nếu có)
$brands = $brands ?? []; // Danh sách các brand để lọc
$products = $products ?? []; // Danh sách sản phẩm chính (kết quả lọc/search hoặc mới nhất)
$latestProducts = $latestProducts ?? [];
$topRated = $topRated ?? [];
$mostReviewed = $mostReviewed ?? [];
$isLoggedIn = $isLoggedIn ?? isset($_SESSION['user_id']);
$wishlistedIds = $wishlistedIds ?? [];

?>

    <style>
        /* CSS riêng cho trang home */
        .hero-section { background-color: #e9ecef; padding: 40px 0; text-align: center; margin-bottom: 30px; border-radius: 5px;}
        .hero-section h1 { margin: 0; font-size: 2.5em; color: #343a40; }
        .hero-section p { font-size: 1.1em; color: #6c757d; margin-top: 10px; }

        .home-content { display: flex; gap: 30px; }
        .home-sidebar { width: 250px; flex-shrink: 0; }
        .home-main { flex: 1; }

        .filter-widget ul { list-style: none; padding: 0; }
        .filter-widget li { margin-bottom: 8px; }
        .filter-widget a { display: block; padding: 5px 10px; border-radius: 4px; background-color: #f8f9fa; }
        .filter-widget a:hover { background-color: #e2e6ea; }
        .filter-widget a.active { background-color: #007bff; color: white; font-weight: bold; }

        .search-form { display: flex; margin-bottom: 20px; }
        .search-form input[type="text"] { flex-grow: 1; padding: 10px; border: 1px solid #ced4da; border-radius: 4px 0 0 4px; }
        .search-form button { padding: 10px 15px; border: none; background-color: #007bff; color: white; border-radius: 0 4px 4px 0; cursor: pointer; }
        .search-form button:hover { background-color: #0056b3; }

        .products-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 20px; }
        .product-item { border: 1px solid #dee2e6; border-radius: 5px; overflow: hidden; background-color: #fff; transition: box-shadow 0.3s ease; text-align: center; padding-bottom: 15px;}
        .product-item:hover { box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .product-item img { width: 100%; height: 200px; object-fit: contain; background-color: #f8f9fa; margin-bottom: 10px;} /* Adjusted height */
        .product-item .product-info { padding: 0 15px; }
        .product-item h5 { font-size: 1em; margin: 10px 0 5px 0; min-height: 2.8em; } /* Fixed height */
        .product-item h5 a { color: #343a40; text-decoration: none; }
        .product-item .price { font-weight: bold; color: #dc3545; margin-bottom: 10px; }
        .product-item .actions { margin-top: 10px; display: flex; justify-content: center; gap: 15px; align-items: center;}
        .product-item .actions a { font-size: 1.3em; text-decoration: none; }
        .product-item .actions a.wishlist-btn { color: #adb5bd; }
        .product-item .actions a.wishlist-btn.active { color: red; }
        .product-item .actions a.cart-btn { color: #28a745; }
        .product-item .actions span.cart-btn { color: #6c757d; font-size: 1.3em; cursor: not-allowed;}


        .product-list-widget ul { list-style: none; padding: 0; }
        .product-list-widget li { display: flex; align-items: center; gap: 10px; border-bottom: 1px dashed #eee; padding: 10px 0; }
        .product-list-widget img { width: 50px; height: 50px; object-fit: contain; border: 1px solid #eee; }
        .product-list-widget .info span { display: block; font-size: 0.9em; }
        .product-list-widget .info .name { font-weight: 500; }
        .product-list-widget .info .price { color: #dc3545; }
        .product-list-widget .info .reviews { color: #6c757d; font-size: 0.8em; }

    </style>

<?php // Phần Hero hoặc Banner nếu có ?>
    <div class="hero-section">
        <h1>Chào mừng đến với MyShop!</h1>
        <p>Tìm kiếm sản phẩm công nghệ yêu thích của bạn.</p>
    </div>

    <div class="home-content">
        <?php // ----- Sidebar ----- ?>
        <aside class="home-sidebar">
            <div class="filter-widget">
                <h2><i class="fas fa-filter" style="margin-right: 5px;"></i> Lọc theo Hãng</h2>
                <ul>
                    <li>
                        <a href="?page=shop_grid" class="<?= (empty($brand)) ? 'active' : '' ?>">
                            Tất cả Hãng
                        </a>
                    </li>
                    <?php foreach ($brands as $b): ?>
                        <li>
                            <?php // Link nên trỏ đến trang shop_grid với filter brand ?>
                            <a href="?page=shop_grid&brand=<?= urlencode($b) ?>" class="<?= ($brand == $b) ? 'active' : '' ?>">
                                <?= htmlspecialchars($b) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <?php // Có thể thêm các bộ lọc khác ở đây (giá,...) cũng link đến shop_grid ?>
            </div>

            <?php // ----- Widget Sản phẩm mới ----- ?>
            <div class="product-list-widget" style="margin-top: 30px;">
                <h2><i class="fas fa-star" style="margin-right: 5px;"></i> Sản phẩm mới</h2>
                <ul>
                    <?php foreach ($latestProducts as $p): ?>
                        <li>
                            <a href="?page=product_detail&id=<?= $p['id'] ?>">
                                <img src="/public/img/<?= htmlspecialchars($p['image'] ?? 'default.jpg') ?>" alt="<?= htmlspecialchars($p['name']) ?>" loading="lazy">
                            </a>
                            <div class="info">
                                <a href="?page=product_detail&id=<?= $p['id'] ?>" class="name"><?= htmlspecialchars($p['name']) ?></a>
                                <span class="price"><?= number_format($p['price'],0,',','.') ?>₫</span>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

        </aside>

        <?php // ----- Main Content ----- ?>
        <section class="home-main">
            <?php // ----- Search Form ----- ?>
            <form method="GET" action="?page=shop_grid" class="search-form"> <?php // Submit đến trang shop_grid ?>
                <input type="hidden" name="page" value="shop_grid">
                <input type="text" name="search" placeholder="Tìm kiếm sản phẩm..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit"><i class="fas fa-search"></i></button>
            </form>

            <?php // ----- Main Product List ----- ?>
            <h2><?= (!empty($brand) ? "Sản phẩm ".htmlspecialchars($brand) : (!empty($search) ? "Kết quả tìm kiếm" : "Sản phẩm nổi bật")) ?></h2>
            <?php if (!empty($products)): ?>
                <div class="products-grid">
                    <?php foreach ($products as $p): ?>
                        <?php
                        $pId = $p['id'];
                        $isProductWishlisted = $isLoggedIn && in_array($pId, $wishlistedIds);
                        ?>
                        <div class="product-item">
                            <a href="?page=product_detail&id=<?= $pId ?>">
                                <img src="/public/img/<?= htmlspecialchars($p['image'] ?? 'default.jpg') ?>" alt="<?= htmlspecialchars($p['name']) ?>" loading="lazy">
                            </a>
                            <div class="product-info">
                                <h5><a href="?page=product_detail&id=<?= $pId ?>"><?= htmlspecialchars($p['name']) ?></a></h5>
                                <div class="price"><?= number_format($p['price'],0,',','.') ?>₫</div>
                                <div class="actions">
                                    <?php // Nút Wishlist ?>
                                    <div>
                                        <?php if ($isLoggedIn): ?>
                                            <?php if ($isProductWishlisted): ?>
                                                <a href="?page=wishlist_remove&id=<?= $pId ?>" title="Xóa khỏi Yêu thích" class="wishlist-btn active">❤️</a>
                                            <?php else: ?>
                                                <a href="?page=wishlist_add&id=<?= $pId ?>" title="Thêm vào Yêu thích" class="wishlist-btn">♡</a>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <?php $redirectUrlGrid = urlencode('?page=home' . (isset($_SERVER['QUERY_STRING']) ? '&'.$_SERVER['QUERY_STRING'] : '' )); ?>
                                            <a href="?page=login&redirect=<?= $redirectUrlGrid ?>" title="Đăng nhập để yêu thích" class="wishlist-btn">♡</a>
                                        <?php endif; ?>
                                    </div>
                                    <?php // Nút Add to Cart ?>
                                    <div>
                                        <?php if ($p['stock'] > 0): ?>
                                            <a href="?page=cart_add&id=<?= $pId ?>&quantity=1" title="Thêm vào giỏ" class="cart-btn">🛒</a>
                                        <?php else: ?>
                                            <span title="Hết hàng" class="cart-btn" style="cursor: not-allowed; opacity: 0.5;">🛒</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php // Có thể thêm nút "Xem thêm" trỏ đến trang shop_grid ?>
                <div style="text-align: center; margin-top: 20px;">
                    <a href="?page=shop_grid" style="padding: 10px 20px; background-color: #6c757d; color: white; border-radius: 4px;">Xem tất cả sản phẩm</a>
                </div>
            <?php else: ?>
                <p>Không tìm thấy sản phẩm nào phù hợp.</p>
            <?php endif; ?>


            <?php // ----- Top Rated Products ----- ?>
            <div class="product-list-widget" style="margin-top: 30px;">
                <h2><i class="fas fa-thumbs-up" style="margin-right: 5px;"></i> Đánh giá cao</h2>
                <ul>
                    <?php foreach ($topRated as $p): ?>
                        <li>
                            <a href="?page=product_detail&id=<?= $p['id'] ?>">
                                <img src="/public/img/<?= htmlspecialchars($p['image'] ?? 'default.jpg') ?>" alt="<?= htmlspecialchars($p['name']) ?>" loading="lazy">
                            </a>
                            <div class="info">
                                <a href="?page=product_detail&id=<?= $p['id'] ?>" class="name"><?= htmlspecialchars($p['name']) ?></a>
                                <span class="price"><?= number_format($p['price'],0,',','.') ?>₫</span>
                                <span class="reviews" style="color: #ffc107;">★ <?= number_format($p['rating'], 1) ?></span>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <?php // ----- Most Reviewed Products ----- ?>
            <div class="product-list-widget" style="margin-top: 30px;">
                <h2><i class="fas fa-comments" style="margin-right: 5px;"></i> Nhiều đánh giá nhất</h2>
                <ul>
                    <?php foreach ($mostReviewed as $p): ?>
                        <li>
                            <a href="?page=product_detail&id=<?= $p['id'] ?>">
                                <img src="/public/img/<?= htmlspecialchars($p['image'] ?? 'default.jpg') ?>" alt="<?= htmlspecialchars($p['name']) ?>" loading="lazy">
                            </a>
                            <div class="info">
                                <a href="?page=product_detail&id=<?= $p['id'] ?>" class="name"><?= htmlspecialchars($p['name']) ?></a>
                                <span class="price"><?= number_format($p['price'],0,',','.') ?>₫</span>
                                <span class="reviews"><?= htmlspecialchars($p['review_count']) ?> đánh giá</span>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

        </section>

    </div> <?php // End home-content ?>


<?php
// Include footer layout
include_once __DIR__ . '/../layout/footer.php';
?>