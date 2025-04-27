// shop_grid_ajax.js - Handles AJAX for shop grid
document.addEventListener("DOMContentLoaded", function () {
  // Cache DOM elements
  const sidebar = document.getElementById("shop-sidebar");
  const productGridContainer = document.getElementById("product-grid-container");
  const paginationContainer = document.getElementById("pagination-container");
  const productCountDisplay = document.getElementById("product-count-display");
  const loadingIndicator = document.getElementById("loading-indicator");
  const sortSelect = document.getElementById("sort_select");
  const searchInput = document.getElementById("search_input");
  const filterSortForm = document.getElementById("filter-sort-form");

  // Track current AJAX request
  let currentRequestController = null;

  /** 
   * getCurrentFiltersAndSort()
   * Gets current filters and sort settings
   * 
   * @returns {Object} filter and sort parameters
   */
  function getCurrentFiltersAndSort() {
    const params = {};
    // Get active filter links
    const activeFilters = sidebar?.querySelectorAll(".filter-options .filter-link.active");
    // Get sort value
    const currentSortValue = sortSelect?.value || 'created_at_desc';
    // Get search value
    const currentSearchValue = searchInput?.value.trim() || '';

    // Add sort
    params['sort'] = currentSortValue;

    // Add search
    if (currentSearchValue) {
      params['search'] = currentSearchValue;
    }

    // Gather active filter links (brand, price_range, specs)
    activeFilters?.forEach(link => {
      const filterGroup = link.closest('.filter-options');
      if (filterGroup) {
        //Get filter info
        const key = filterGroup.dataset.filterKey;
        const value = link.dataset.value;
        if (key && value && value.toLowerCase() !== 'all') {
          params[key] = value;
        }
      }
    });

    return params;
  }

  /** 
   * fetchAndUpdateProducts()
   * Fetch product data and update the UI
   * @param {number} [page=1] - page number to fetch
   * @param {boolean} [pushState=true] - update browser history or not
   */
  async function fetchAndUpdateProducts(page = 1, pushState = true) {
    if (!productGridContainer || !paginationContainer || !productCountDisplay || !loadingIndicator) {
      console.error("Essential elements for AJAX update are missing.");
      return;
    }

    // Abort old request
    if (currentRequestController) { currentRequestController.abort(); }
    currentRequestController = new AbortController();
    // Get the signal from the AbortController to pass to the fetch request.
    const signal = currentRequestController.signal;

    // Show loading indicator and dim the product grid to indicate loading.
    loadingIndicator.style.display = "inline-block";
    productGridContainer.style.opacity = "0.5";
    // Get filters
    const filters = getCurrentFiltersAndSort();
    const urlParams = new URLSearchParams(filters);
    urlParams.set('pg', page);
    // urlParams.set('ajax', '1'); // Backend checks HTTP_X_REQUESTED_WITH instead

    const url = `?page=shop_grid&${urlParams.toString()}`;

    // Fetch data and update UI
    try {
      const response = await fetch(url, {
          signal, // Pass the abort signal to the fetch request.
            headers: {
              'X-Requested-With': 'XMLHttpRequest',
              'Accept': 'application/json' // Expect JSON response
          }
      });

      // Check if the request was aborted after fetch started but before completion
      if (signal.aborted) {
          console.log("Fetch aborted");
          return; // Stop processing if aborted
      }

      if (!response.ok) {
          throw new Error(`HTTP error! Status: ${response.status}`);
      }

      const data = await response.json();
      // Update the UI
      if (data.success) {
        productGridContainer.innerHTML = data.productHtml || '<div class="alert alert-warning">Không có sản phẩm nào.</div>';
        paginationContainer.innerHTML = data.paginationHtml || '';
        productCountDisplay.textContent = data.countText || 'Không có sản phẩm nào.';
        // Update browser history
        if (pushState) {
          const historyParams = new URLSearchParams(urlParams);
          historyParams.set('page', 'shop_grid');
          window.history.pushState({ page: page, filters: filters }, '', `?${historyParams.toString()}`);
        }
      } else {
        // Handle error message from server.
        console.error("AJAX request failed:", data.message || "Unknown server error");
        productGridContainer.innerHTML = `<div class="alert alert-danger">Lỗi: ${data.message || 'Không thể tải sản phẩm. Vui lòng thử lại.'}</div>`;
        paginationContainer.innerHTML = ''; // Clear pagination on error
        productCountDisplay.textContent = 'Đã xảy ra lỗi.';
      }
    } catch (error) {
      if (error.name === 'AbortError') { // Don't show error message if aborted by new request
        console.log('Fetch request was aborted.');
      } else {
        console.error("Fetch error:", error);
        productGridContainer.innerHTML = '<div class="alert alert-danger">Lỗi kết nối hoặc xử lý. Vui lòng thử lại.</div>';
        paginationContainer.innerHTML = '';
        productCountDisplay.textContent = 'Đã xảy ra lỗi.';
      }
    } finally {
      // Hide loading state regardless of success or failure (unless aborted).
      if (!signal.aborted) { // Only reset UI if request was not aborted.
        loadingIndicator.style.display = "none";
        productGridContainer.style.opacity = "1";
        currentRequestController = null;
      }
    }
  }

  // Set up event listeners
  // Filter links
  if (sidebar) {
    sidebar.addEventListener('click', function(event) {
      const targetLink = event.target.closest('.filter-link');
      if (targetLink && !targetLink.classList.contains('active')) {
        event.preventDefault();
        const filterGroup = targetLink.closest('.filter-options');
        if (filterGroup) {
          filterGroup.querySelectorAll('.filter-link.active').forEach(activeLink => {
            activeLink.classList.remove('active');
          });
          targetLink.classList.add('active');
          fetchAndUpdateProducts(1, true);
        }
      }
    });
  }
  // Sort dropdown
  if (sortSelect) {
    sortSelect.addEventListener('change', function() {
      fetchAndUpdateProducts(1, true); // Fetch page 1 with new sort.
    });
  }

  // Search form
  if (filterSortForm) {
      filterSortForm.addEventListener('submit', function(event) {
          event.preventDefault();
          fetchAndUpdateProducts(1, true);
      });
      searchInput?.addEventListener('keypress', function(event) {
        if (event.key === 'Enter') {
              event.preventDefault();
              fetchAndUpdateProducts(1, true);
        }
      });
  }
  // Pagination links
  if (paginationContainer) {
    paginationContainer.addEventListener('click', function(event) {
      const targetLink = event.target.closest('.ajax-page-link');
      if (targetLink && !targetLink.closest('.disabled') && !targetLink.closest('.active')) {
        event.preventDefault();
        const page = targetLink.dataset.page;
        if (page) { fetchAndUpdateProducts(parseInt(page, 10), true); }
      }
    });
  }
  // Browser history
  window.addEventListener('popstate', function(event) {
    if (event.state && event.state.page) {
      //get page from state
      fetchAndUpdateProducts(event.state.page, false);
    } else {
        // Handle initial state or states without page info
        const params = new URLSearchParams(window.location.search);
        const page = parseInt(params.get('pg') || '1', 10);
         // Also re-apply filters from URL on popstate to initial load state
         // This requires updating filter UI elements based on URL params, which is complex.
         // Simpler approach: Fetch based on current URL params.
         fetchAndUpdateProducts(page, false);
    }
  });
});