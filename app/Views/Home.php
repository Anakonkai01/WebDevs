<?php
// View: home.php (Ví dụ: app/Views/home.php)

/**
 * FILE NÀY GIẢ ĐỊNH RẰNG:
 * 1. Controller (HomeController) đã truyền các biến sau vào thông qua $this->render('home', $data):
 * - $pageTitle (string, optional): Tiêu đề trang.
 * - $search (string): Từ khóa tìm kiếm hiện tại (có thể rỗng).
 * - $brand (string): Hãng đang được lọc (có thể rỗng).
 * - $brands (array): Mảng chứa tên các hãng ['Apple', 'Samsung', ...].
 * - $products (array): Mảng chứa các sản phẩm chính cần hiển thị (kết quả tìm/lọc/mặc định).
 * - $latestProducts (array): Mảng sản phẩm mới nhất.
 * - $topRated (array): Mảng sản phẩm đánh giá cao nhất.
 * - $mostReviewed (array): Mảng sản phẩm nhiều review nhất (cần có key 'review_count').
 * - $cartItemCount (int): Số lượng sản phẩm trong giỏ hàng.
 * - $cartTotal (float): Tổng giá trị giỏ hàng.
 * - $wishlistCount (int): Số lượng sản phẩm yêu thích.
 * - $isLoggedIn (bool): Trạng thái đăng nhập của người dùng.
 * - $username (string): Tên người dùng nếu đã đăng nhập.
 *
 * 2. File layout header.php và footer.php tồn tại trong thư mục /app/Views/layout/
 * và chúng chứa các thẻ HTML cơ bản, link CSS/JS cần thiết cho template.
 *
 * 3. Các route MVC đã được định nghĩa trong Router (ví dụ: '/', '/shop', '/cart', '/login',
 * '/logout', '/account', '/products/show/{id}', '/cart/add/{id}', '/wishlist/add/{id}', '/contact').
 *
 * 4. Thư mục chứa ảnh public là /img/ (ví dụ: public/img/logo.png).
 *
 * 5. Các thư viện JS (jQuery, OwlCarousel, MixItUp/Isotope cho filter) đã được nhúng
 * và khởi tạo đúng cách (thường trong footer.php hoặc file JS riêng).
 */

// --- Khởi tạo biến với giá trị mặc định để tránh lỗi ---
$pageTitle      = $pageTitle ?? 'Trang Chủ - MBStore';
$search         = $search ?? '';
$brand          = $brand  ?? '';
$brands         = $brands ?? [];

$products       = $products       ?? [];
$latestProducts = $latestProducts ?? [];
$topRated       = $topRated       ?? [];
$mostReviewed   = $mostReviewed   ?? [];

// Dữ liệu mẫu/placeholders (Controller cần truyền dữ liệu thật)
$cartItemCount  = $cartItemCount ?? 0;
$cartTotal      = $cartTotal ?? 0.00;
$wishlistCount  = $wishlistCount ?? 0;
$isLoggedIn     = $isLoggedIn ?? false; // Nên kiểm tra từ Session
$username       = $username ?? 'Tài khoản';

// Include header layout
include_once __DIR__ . '/layout/header.php';
?>

    <div id="preloder">
        <div class="loader"></div>
    </div>

    <div class="humberger__menu__overlay"></div>
    <div class="humberger__menu__wrapper">
        <div class="humberger__menu__logo">
            <a href="/"><img src="/img/logo.png" alt="MBStore Logo"></a>
        </div>
        <div class="humberger__menu__cart">
            <ul>
                <li><a href="/wishlist"><i class="fa fa-heart"></i> <span><?= $wishlistCount ?></span></a></li>
                <li><a href="/cart"><i class="fa fa-shopping-bag"></i> <span><?= $cartItemCount ?></span></a></li>
            </ul>
            <div class="header__cart__price">Tổng: <span><?= number_format($cartTotal) ?> đ</span></div>
        </div>
        <div class="humberger__menu__widget">
            <div class="header__top__right__language">
                <img src="/img/language.png" alt="Language">
                <div>English</div>
                <span class="arrow_carrot-down"></span>
                <ul>
                    <li><a href="#">Vietnamese</a></li>
                    <li><a href="#">English</a></li>
                </ul>
            </div>
            <div class="header__top__right__auth">
                <?php if ($isLoggedIn): ?>
                    <a href="/account"><i class="fa fa-user"></i> <?= htmlspecialchars($username) ?></a> / <a href="/logout">Đăng xuất</a>
                <?php else: ?>
                    <a href="/login"><i class="fa fa-user"></i> Đăng nhập</a>
                <?php endif; ?>
            </div>
        </div>
        <nav class="humberger__menu__nav mobile-menu">
            <ul>
                <li class="active"><a href="/">Trang chủ</a></li>
                <li><a href="/shop">Cửa hàng</a></li>
                <li><a href="#">Trang</a>
                    <ul class="header__menu__dropdown">
                        <li><a href="/shop">Chi tiết cửa hàng</a></li>
                        <li><a href="/cart">Giỏ hàng</a></li>
                        <li><a href="/checkout">Thanh toán</a></li>
                    </ul>
                </li>
                <li><a href="/contact">Liên hệ</a></li>
            </ul>
        </nav>
        <div id="mobile-menu-wrap"></div>
        <div class="header__top__right__social">
            <a href="#"><i class="fa fa-facebook"></i></a>
            <a href="#"><i class="fa fa-twitter"></i></a>
            <a href="#"><i class="fa fa-linkedin"></i></a>
            <a href="#"><i class="fa fa-pinterest-p"></i></a>
        </div>
        <div class="humberger__menu__contact">
            <ul>
                <li><i class="fa fa-envelope"></i> MBstore@gmail.com</li>
                <li>Miễn phí vận chuyển cho đơn hàng trên 399.000 đ</li>
            </ul>
        </div>
    </div>
    <header class="header">
        <div class="header__top">
            <div class="container">
                <div class="row">
                    <div class="col-lg-6 col-md-6">
                        <div class="header__top__left">
                            <ul>
                                <li><i class="fa fa-envelope"></i> MBstore@gmail.com</li>
                                <li>Miễn phí vận chuyển cho đơn hàng trên 399.000 đ</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-6">
                        <div class="header__top__right">
                            <div class="header__top__right__social">
                                <a href="#"><i class="fa fa-facebook"></i></a>
                                <a href="#"><i class="fa fa-twitter"></i></a>
                                <a href="#"><i class="fa fa-linkedin"></i></a>
                                <a href="#"><i class="fa fa-pinterest-p"></i></a>
                            </div>
                            <div class="header__top__right__language">
                                <img src="/img/language.png" alt="">
                                <div>English</div>
                                <span class="arrow_carrot-down"></span>
                                <ul>
                                    <li><a href="#">Vietnamese</a></li>
                                    <li><a href="#">English</a></li>
                                </ul>
                            </div>
                            <div class="header__top__right__auth">
                                <?php if ($isLoggedIn): ?>
                                    <a href="/account"><i class="fa fa-user"></i> <?= htmlspecialchars($username) ?></a> / <a href="/logout">Đăng xuất</a>
                                <?php else: ?>
                                    <a href="/login"><i class="fa fa-user"></i> Đăng nhập</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="container">
            <div class="row">
                <div class="col-lg-3">
                    <div class="header__logo">
                        <a href="/"><img src="/img/logo.png" alt="MBStore Logo"></a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <nav class="header__menu">
                        <ul>
                            <li class="active"><a href="/">Trang chủ</a></li>
                            <li><a href="/shop">Cửa hàng</a></li>
                            <li><a href="#">Trang</a>
                                <ul class="header__menu__dropdown">
                                    <li><a href="/shop">Chi tiết cửa hàng</a></li>
                                    <li><a href="/cart">Giỏ hàng</a></li>
                                    <li><a href="/checkout">Thanh toán</a></li>
                                </ul>
                            </li>
                            <li><a href="/contact">Liên hệ</a></li>
                        </ul>
                    </nav>
                </div>
                <div class="col-lg-3">
                    <div class="header__cart">
                        <ul>
                            <li><a href="/wishlist"><i class="fa fa-heart"></i> <span><?= $wishlistCount ?></span></a></li>
                            <li><a href="/cart"><i class="fa fa-shopping-bag"></i> <span><?= $cartItemCount ?></span></a></li>
                        </ul>
                        <div class="header__cart__price">Tổng: <span><?= number_format($cartTotal) ?> đ</span></div>
                    </div>
                </div>
            </div>
            <div class="humberger__open">
                <i class="fa fa-bars"></i>
            </div>
        </div>
    </header>
    <section class="hero">
        <div class="container">
            <div class="row">
                <div class="col-lg-3">
                    <div class="hero__categories">
                        <div class="hero__categories__all">
                            <i class="fa fa-bars"></i>
                            <span>Hãng Sản Xuất</span>
                        </div>
                        <ul>
                            <?php if (!empty($brands)): ?>
                                <?php foreach ($brands as $brandName): ?>
                                    <li><a href="/?brand=<?= urlencode($brandName) ?><?php echo $search ? '&search='.urlencode($search) : ''; ?>"><?= htmlspecialchars($brandName) ?></a></li>
                                <?php endforeach; ?>
                                <li><a href="/">Tất cả hãng</a></li> <?php else: ?>
                                <li>(Chưa có hãng)</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-9">
                    <div class="hero__search">
                        <div class="hero__search__form">
                            <form action="/" method="GET">
                                <input type="text" name="search" placeholder="Bạn cần tìm gì?" value="<?= htmlspecialchars($search) ?>">
                                <button type="submit" class="site-btn">TÌM KIẾM</button>
                            </form>
                        </div>
                        <div class="hero__search__phone">
                            <div class="hero__search__phone__icon">
                                <i class="fa fa-phone"></i>
                            </div>
                            <div class="hero__search__phone__text">
                                <h5>049 5149 5149</h5>
                                <span>Hỗ trợ 24/7</span>
                            </div>
                        </div>
                    </div>
                    <div class="hero__item set-bg" data-setbg="/img/hero/banner.jpg">
                        <div class="hero__text">
                            <span>MOBILE PHONE STORE</span>
                            <h2>ALL PRODUCTS<br />100% NEW</h2>
                            <p>Đổi trả trong vòng 6 tháng</p>
                            <a href="/shop" class="primary-btn">MUA NGAY</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="categories">
        <div class="container">
            <div class="row">
                <div class="categories__slider owl-carousel">
                    <div class="col-lg-3">
                        <div class="categories__item set-bg" data-setbg="/img/categories/cat-1.jpg">
                            <h5><a href="/?brand=Xiaomi">XIAOMI</a></h5>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="categories__item set-bg" data-setbg="/img/categories/cat-2.jpg">
                            <h5><a href="/?brand=Oppo">OPPO</a></h5>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="categories__item set-bg" data-setbg="/img/categories/cat-3.jpg">
                            <h5><a href="/?brand=Vivo">VIVO</a></h5>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="categories__item set-bg" data-setbg="/img/categories/cat-4.jpg">
                            <h5><a href="/?brand=Samsung">SAMSUNG</a></h5>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="categories__item set-bg" data-setbg="/img/categories/cat-5.jpg">
                            <h5><a href="/?brand=Apple">IPHONE</a></h5> </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="featured spad">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="section-title">
                        <h2>
                            <?php
                            if ($search) {
                                echo 'Kết quả tìm kiếm cho "' . htmlspecialchars($search) . '"';
                            } elseif ($brand) {
                                echo 'Sản phẩm hãng ' . htmlspecialchars($brand);
                            } else {
                                echo 'Sản phẩm nổi bật';
                            }
                            ?>
                        </h2>
                    </div>
                    <div class="featured__controls">
                        <ul>
                            <li class="active" data-filter="*">Tất cả</li>
                            <?php if (!empty($brands)): ?>
                                <?php foreach ($brands as $brandName):
                                    // Create CSS-friendly filter class (e.g., "apple" -> "brand-apple")
                                    $filterClass = 'brand-' . preg_replace('/[^a-z0-9]+/', '-', strtolower($brandName));
                                    ?>
                                    <li data-filter=".<?= htmlspecialchars($filterClass) ?>"><?= htmlspecialchars($brandName) ?></li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="row featured__filter">
                <?php if (!empty($products)): ?>
                    <?php foreach ($products as $product):
                        // Create class for filtering based on brand
                        $productFilterClass = 'brand-' . preg_replace('/[^a-z0-9]+/', '-', strtolower($product['brand'] ?? 'other'));
                        ?>
                        <div class="col-lg-3 col-md-4 col-sm-6 mix <?= htmlspecialchars($productFilterClass) ?>">
                            <div class="featured__item">
                                <div class="featured__item__pic set-bg" data-setbg="/img/<?= htmlspecialchars($product['image'] ?? 'default-product.jpg') ?>">
                                    <ul class="featured__item__pic__hover">
                                        <li><a href="/wishlist/add/<?= $product['id'] ?>" title="Thêm vào yêu thích"><i class="fa fa-heart"></i></a></li>
                                        <li>
                                            <form action="/cart/add/<?= $product['id'] ?>" method="POST" style="display:inline;">
                                                <input type="hidden" name="quantity" value="1">
                                                <button type="submit" class="icon_btn" title="Thêm vào giỏ hàng"><i class="fa fa-shopping-cart"></i></button>
                                            </form>
                                        </li>
                                        <li><a href="/products/show/<?= $product['id'] ?>" title="Xem chi tiết"><i class="fa fa-eye"></i></a></li>
                                    </ul>
                                </div>
                                <div class="featured__item__text">
                                    <h6><a href="/products/show/<?= $product['id'] ?>"><?= htmlspecialchars($product['name']) ?></a></h6>
                                    <h5><?= number_format($product['price']) ?> đ</h5>
                                    <div class="product__item__rating" style="font-size: 0.9em; color: #f5b223; margin-top: 5px;">
                                        <?= str_repeat('★', round($product['rating'] ?? 0)); ?><?= str_repeat('☆', 5 - round($product['rating'] ?? 0)); ?>
                                        <span style="color:#6f6f6f; font-size: 0.9em;">(<?= number_format($product['rating'] ?? 0, 1) ?>)</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12"><p>Không tìm thấy sản phẩm nào phù hợp với tiêu chí của bạn.</p></div>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <div class="banner">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 col-md-6 col-sm-6">
                    <div class="banner__pic">
                        <img src="/img/banner/banner-1.jpg" alt="Banner 1">
                    </div>
                </div>
                <div class="col-lg-6 col-md-6 col-sm-6">
                    <div class="banner__pic">
                        <img src="/img/banner/banner-2.jpg" alt="Banner 2">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <section class="latest-product spad">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 col-md-6">
                    <div class="latest-product__text">
                        <h4>Sản phẩm mới nhất</h4>
                        <div class="latest-product__slider owl-carousel">
                            <?php if (!empty($latestProducts)):
                                // Group products by 3 for slider items
                                $chunks = array_chunk($latestProducts, 3);
                                foreach ($chunks as $chunk): ?>
                                    <div class="latest-prdouct__slider__item">
                                        <?php foreach ($chunk as $product): ?>
                                            <a href="/products/show/<?= $product['id'] ?>" class="latest-product__item">
                                                <div class="latest-product__item__pic">
                                                    <img src="/img/<?= htmlspecialchars($product['image'] ?? 'default-product-small.jpg') ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                                                </div>
                                                <div class="latest-product__item__text">
                                                    <h6><?= htmlspecialchars($product['name']) ?></h6>
                                                    <span><?= number_format($product['price']) ?> đ</span>
                                                </div>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="latest-prdouct__slider__item"><p>Chưa có sản phẩm mới.</p></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="latest-product__text">
                        <h4>Đánh giá cao nhất</h4>
                        <div class="latest-product__slider owl-carousel">
                            <?php if (!empty($topRated)):
                                $chunks = array_chunk($topRated, 3);
                                foreach ($chunks as $chunk): ?>
                                    <div class="latest-prdouct__slider__item">
                                        <?php foreach ($chunk as $product): ?>
                                            <a href="/products/show/<?= $product['id'] ?>" class="latest-product__item">
                                                <div class="latest-product__item__pic">
                                                    <img src="/img/<?= htmlspecialchars($product['image'] ?? 'default-product-small.jpg') ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                                                </div>
                                                <div class="latest-product__item__text">
                                                    <h6><?= htmlspecialchars($product['name']) ?></h6>
                                                    <span><?= number_format($product['price']) ?> đ</span>
                                                    <div class="product__item__rating" style="font-size: 0.8em; color: #f5b223;">
                                                        <?= str_repeat('★', round($product['rating'] ?? 0)); ?><?= str_repeat('☆', 5 - round($product['rating'] ?? 0)); ?>
                                                        <span style="color:#6f6f6f; font-size: 0.9em;">(<?= number_format($product['rating'] ?? 0, 1) ?>)</span>
                                                    </div>
                                                </div>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="latest-prdouct__slider__item"><p>Chưa có sản phẩm được đánh giá.</p></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="latest-product__text">
                        <h4>Review nhiều nhất</h4>
                        <div class="latest-product__slider owl-carousel">
                            <?php if (!empty($mostReviewed)):
                                $chunks = array_chunk($mostReviewed, 3);
                                foreach ($chunks as $chunk): ?>
                                    <div class="latest-prdouct__slider__item">
                                        <?php foreach ($chunk as $product): ?>
                                            <a href="/products/show/<?= $product['id'] ?>" class="latest-product__item">
                                                <div class="latest-product__item__pic">
                                                    <img src="/img/<?= htmlspecialchars($product['image'] ?? 'default-product-small.jpg') ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                                                </div>
                                                <div class="latest-product__item__text">
                                                    <h6><?= htmlspecialchars($product['name']) ?></h6>
                                                    <span><?= number_format($product['price']) ?> đ</span>
                                                    <div style="font-size: 0.8em; color: #6f6f6f;"><?= htmlspecialchars($product['review_count'] ?? 0) ?> review(s)</div>
                                                </div>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="latest-prdouct__slider__item"><p>Chưa có sản phẩm nào được review.</p></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
<?php
// Include footer layout
include_once __DIR__ . '/layout/footer.php';
?>