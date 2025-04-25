document.addEventListener("DOMContentLoaded", function () {
  const sidebar = document.getElementById("shop-sidebar"); // ID cho sidebar
  const productGridContainer = document.getElementById(
    "product-grid-container"
  );
  const paginationContainer = document.getElementById("pagination-container");
  const productCountDisplay = document.getElementById("product-count-display");
  const loadingIndicator = document.getElementById("loading-indicator");
  const searchForm = document.getElementById("search-form"); // ID cho form tìm kiếm
  const sortSelect = document.getElementById("sort_select");

  let currentRequestController = null; // Để hủy request cũ nếu có request mới

  // --- Hàm lấy tất cả filter hiện tại ---
  function getCurrentFilters() {
    const filters = {};
    // Lấy từ search input
    const searchInput = document.getElementById("search_input");
    if (searchInput && searchInput.value.trim() !== "") {
      filters.search = searchInput.value.trim();
    }

    // Lấy từ các link active trong list-group/accordion (Brand, Price, Specs)
    const activeLinks = sidebar?.querySelectorAll(
      ".filter-options .list-group-item.active"
    );
    activeLinks?.forEach((link) => {
      const filterGroup = link.closest(".filter-options");
      if (filterGroup) {
        const key = filterGroup.dataset.filterKey;
        const value = link.dataset.value;
        // Chỉ thêm nếu key tồn tại và value khác 'all' (hoặc giá trị mặc định khác)
        if (key && value && value.toLowerCase() !== "all") {
          filters[key] = value;
        }
      }
    });

    // Lấy từ sort select
    if (sortSelect) {
      // Chỉ thêm sort nếu khác giá trị mặc định (ví dụ: 'created_at_desc')
      if (sortSelect.value !== "created_at_desc") {
        filters.sort = sortSelect.value;
      }
    }

    // Lấy trang hiện tại (từ data-page của link active trong pagination)
    const activePageLink = paginationContainer?.querySelector(
      ".page-item.active .ajax-page-link"
    );
    // Nếu không có trang active (lần đầu), hoặc lấy từ URL nếu cần thiết fallback
    filters.pg = activePageLink ? activePageLink.dataset.page || "1" : "1";

    return filters;
  }

  // --- Hàm thực hiện AJAX request ---
  async function fetchAndUpdateProducts(page = 1, pushState = true) {
    if (
      !productGridContainer ||
      !paginationContainer ||
      !productCountDisplay ||
      !sidebar
    ) {
      console.error("Missing essential elements for AJAX update.");
      return;
    }

    // Hủy request trước đó nếu đang chạy
    if (currentRequestController) {
      currentRequestController.abort();
    }
    // Tạo AbortController mới cho request này
    currentRequestController = new AbortController();
    const signal = currentRequestController.signal;

    if (loadingIndicator) loadingIndicator.style.display = "inline-block";
    productGridContainer.style.opacity = "0.5";

    const filters = getCurrentFilters();
    filters.pg = page; // Đặt trang cần lấy

    const params = new URLSearchParams();
    // Quan trọng: Luôn thêm page=shop_grid để routing đúng
    params.set("page", "shop_grid");
    // Thêm các filter vào params
    for (const key in filters) {
      if (filters[key] && filters[key] !== "all") {
        // Chỉ thêm các filter có giá trị và khác 'all'
        params.set(key, filters[key]);
      }
    }
    // Thêm ajax=1 để controller biết
    // params.set('ajax', '1'); // Không cần nếu dùng header X-Requested-With

    const queryString = params.toString();
    const ajaxUrl = `?${queryString}`; // URL cho AJAX fetch
    // URL để pushState (bỏ page=shop_grid nếu muốn URL gọn hơn)
    const displayUrl = `?${queryString.replace(/^page=shop_grid&?/, "")}`;

    try {
      const response = await fetch(ajaxUrl, {
        method: "GET",
        headers: { "X-Requested-With": "XMLHttpRequest" }, // Header để server nhận biết AJAX
        signal: signal, // Pass the signal to fetch
      });

      // Nếu request bị hủy, không làm gì cả
      if (signal.aborted) {
        console.log("Fetch aborted");
        return;
      }

      if (!response.ok)
        throw new Error(`HTTP error! status: ${response.status}`);

      const data = await response.json();

      if (data.success) {
        // Cập nhật nội dung HTML
        productGridContainer.innerHTML = data.productHtml;
        paginationContainer.innerHTML = data.paginationHtml;
        productCountDisplay.textContent = data.countText;

        // Cập nhật URL trình duyệt mà không tải lại trang (chỉ khi pushState là true)
        if (pushState) {
          history.pushState(
            { filters: filters },
            "",
            displayUrl || "?page=shop_grid"
          ); // Lưu state nếu cần
        }

        // Gắn lại listener cho pagination MỚI
        attachPaginationListener();
      } else {
        console.error("AJAX request failed:", data.message || "Unknown error");
        productGridContainer.innerHTML =
          '<div class="alert alert-danger">Lỗi tải sản phẩm.</div>'; // Thông báo lỗi
      }
    } catch (error) {
      if (error.name === "AbortError") {
        console.log("Fetch request aborted.");
      } else {
        console.error("Error fetching products:", error);
        productGridContainer.innerHTML =
          '<div class="alert alert-danger">Lỗi kết nối. Vui lòng thử lại.</div>';
        paginationContainer.innerHTML = ""; // Xóa pagination cũ
        productCountDisplay.textContent = "Lỗi";
      }
    } finally {
      if (loadingIndicator) loadingIndicator.style.display = "none";
      productGridContainer.style.opacity = "1";
      currentRequestController = null; // Reset controller
    }
  }

  // --- Hàm gắn listener cho pagination ---
  function attachPaginationListener() {
    paginationContainer?.querySelectorAll(".ajax-page-link").forEach((link) => {
      // Xóa listener cũ trước khi thêm mới (quan trọng)
      link.replaceWith(link.cloneNode(true));
    });
    // Gắn listener mới cho các link vừa clone
    paginationContainer?.querySelectorAll(".ajax-page-link").forEach((link) => {
      link.addEventListener("click", function (e) {
        e.preventDefault();
        const pageNum = this.dataset.page;
        const parentLi = this.closest(".page-item");
        // Chỉ fetch khi link không bị disabled và không phải trang hiện tại
        if (
          pageNum &&
          parentLi &&
          !parentLi.classList.contains("disabled") &&
          !parentLi.classList.contains("active")
        ) {
          fetchAndUpdateProducts(pageNum);
        }
      });
    });
  }

  // --- Gắn Event Listeners ban đầu ---
  if (sidebar) {
    // 1. Listener cho CLICK vào các link lọc (dùng event delegation)
    sidebar.addEventListener("click", function (e) {
      // Chỉ xử lý nếu click vào link filter và link đó không đang active
      if (
        e.target.classList.contains("filter-link") &&
        !e.target.classList.contains("active")
      ) {
        e.preventDefault();
        const link = e.target;
        const filterGroup = link.closest(".filter-options");
        if (!filterGroup) return;

        // Cập nhật trạng thái active UI NGAY LẬP TỨC
        filterGroup
          .querySelectorAll(".filter-link")
          .forEach((el) => el.classList.remove("active"));
        link.classList.add("active");

        // Gọi fetch (luôn về trang 1 khi đổi filter)
        fetchAndUpdateProducts(1);
      }
    });

    // 2. Listener cho SUBMIT form tìm kiếm
    if (searchForm) {
      searchForm.addEventListener("submit", function (e) {
        e.preventDefault();
        fetchAndUpdateProducts(1);
      });
    }

    // 3. Listener cho CHANGE select sắp xếp
    if (sortSelect) {
      sortSelect.addEventListener("change", function () {
        fetchAndUpdateProducts(1);
      });
    }
  }

  // 4. Listener cho nút back/forward của trình duyệt
  window.addEventListener("popstate", function (event) {
    // Khi người dùng nhấn back/forward, URL thay đổi
    // Cần đọc lại filter từ URL mới và fetch lại dữ liệu mà KHÔNG pushState
    // Lưu ý: Cần parse URL để lấy lại các filter
    // console.log("Popstate event:", window.location.search);
    // Đây là phần nâng cao, tạm thời chỉ fetch lại trang 1
    const urlParams = new URLSearchParams(window.location.search);
    const page = urlParams.get("pg") || 1;
    // Cập nhật UI của filter dựa trên URL (phần này phức tạp)
    // ... (code cập nhật UI filter) ...
    fetchAndUpdateProducts(page, false); // Fetch không pushState
  });

  // 5. Listener cho pagination (gắn lần đầu)
  attachPaginationListener();

  // --- Code xử lý Wishlist AJAX ---
  // Đảm bảo code này vẫn hoạt động sau khi grid được cập nhật bằng AJAX
  // Cách tốt nhất là dùng event delegation trên một container cố định bao ngoài grid
  const mainContentArea = document.querySelector(".col-lg-9"); // Hoặc body
  if (mainContentArea) {
    mainContentArea.addEventListener("click", async function (event) {
      const wishlistButton = event.target.closest(".btn-wishlist");
      if (wishlistButton) {
        event.preventDefault(); // Chặn hành vi mặc định của button/link nếu có
        console.log("Wishlist button clicked via delegation.");

        const isLoggedIn = document.body.dataset.isLoggedIn === "true"; // Lấy từ body
        const productId = wishlistButton.dataset.productId;

        if (!productId) {
          console.error("Missing data-product-id on wishlist button.");
          return;
        }

        if (!isLoggedIn) {
          console.log("User not logged in. Redirecting...");
          const currentUrl = encodeURIComponent(
            window.location.href || "?page=shop_grid"
          );
          window.location.href = `?page=login&redirect=${currentUrl}`;
        } else {
          // Gọi hàm AJAX toggleWishlist (đảm bảo hàm này đã được định nghĩa)
          if (typeof toggleWishlist === "function") {
            toggleWishlist(wishlistButton, productId);
          } else {
            console.error("toggleWishlist function is not defined.");
            // Sao chép hàm toggleWishlist vào đây hoặc include file chứa nó
            await toggleWishlistAJAX(wishlistButton, productId); // Ví dụ gọi hàm AJAX trực tiếp
          }
        }
      }
    });
  }

  // Hàm toggleWishlist AJAX (có thể đặt ở đây hoặc include từ file khác)
  async function toggleWishlistAJAX(buttonElement, productId) {
    const isWishlisted = buttonElement.dataset.isWishlisted === "1";
    const action = isWishlisted ? "wishlist_remove" : "wishlist_add";
    const icon = buttonElement.querySelector("i");

    buttonElement.disabled = true;
    if (icon) {
      icon.classList.remove("fa-heart", "fa-spin", "fa-spinner");
      icon.classList.add("fa-spinner", "fa-spin");
    }

    try {
      // Thêm ajax=1 vào URL
      const response = await fetch(
        `?page=${action}&id=${productId}&ajax=1&redirect=no`,
        {
          method: "GET",
          headers: { "X-Requested-With": "XMLHttpRequest" },
        }
      );
      if (!response.ok)
        throw new Error(`HTTP error! status: ${response.status}`);

      const contentType = response.headers.get("content-type");
      if (contentType && contentType.indexOf("application/json") !== -1) {
        const data = await response.json();
        console.log("Wishlist Response:", data);
        if (data.success) {
          buttonElement.dataset.isWishlisted = isWishlisted ? "0" : "1";
          buttonElement.classList.toggle("active");
          buttonElement.title = isWishlisted
            ? "Thêm vào Yêu thích"
            : "Xóa khỏi Yêu thích";
          if (typeof data.wishlistItemCount !== "undefined") {
            const wishlistCountElement = document.getElementById(
              "header-wishlist-count"
            );
            if (wishlistCountElement) {
              const newCount = parseInt(data.wishlistItemCount);
              wishlistCountElement.textContent = newCount;
              wishlistCountElement.style.display =
                newCount > 0 ? "inline-block" : "none";
            }
          }
        } else {
          alert(data.message || "Có lỗi xảy ra, vui lòng thử lại.");
        }
      } else {
        const textResponse = await response.text();
        console.error("Non-JSON Wishlist Response:", textResponse);
        throw new Error("Received non-JSON response from server.");
      }
    } catch (error) {
      console.error("Error toggling wishlist:", error);
      alert("Lỗi kết nối hoặc xử lý (Wishlist). Vui lòng thử lại.");
    } finally {
      buttonElement.disabled = false;
      if (icon) {
        icon.classList.remove("fa-spinner", "fa-spin");
        icon.classList.add("fa-heart");
      }
    }
  }
});

// Hàm helper getSpecFilterLabel (nếu cần)
function getSpecFilterLabel(specKey) {
  /* ... */
}
