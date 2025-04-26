/**
 * shop_grid_ajax.js
 * Handles AJAX filtering, sorting, and pagination for the shop grid page.
 */
document.addEventListener("DOMContentLoaded", function () {
  // --- DOM Elements ---
  const sidebar = document.getElementById("shop-sidebar");
  const productGridContainer = document.getElementById("product-grid-container");
  const paginationContainer = document.getElementById("pagination-container");
  const productCountDisplay = document.getElementById("product-count-display");
  const loadingIndicator = document.getElementById("loading-indicator");
  const sortSelect = document.getElementById("sort_select");
  const searchInput = document.getElementById("search_input");
  const filterSortForm = document.getElementById("filter-sort-form"); // The form wrapping filters/sort

  // --- State ---
  let currentRequestController = null; // To abort pending requests

  // --- Functions ---

  /**
   * Gathers all current filter and sort values from the UI.
   * @returns {object} Object containing filter/sort parameters.
   */
  function getCurrentFiltersAndSort() {
    const params = {};
    const activeFilters = sidebar?.querySelectorAll(".filter-options .filter-link.active");
    const currentSortValue = sortSelect?.value || 'created_at_desc';
    const currentSearchValue = searchInput?.value.trim() || '';

    // Add sort parameter
    params['sort'] = currentSortValue;

    // Add search parameter if not empty
    if (currentSearchValue) {
      params['search'] = currentSearchValue;
    }

    // Gather active filter links (brand, price_range, specs)
    activeFilters?.forEach(link => {
      const filterGroup = link.closest('.filter-options');
      if (filterGroup) {
        const key = filterGroup.dataset.filterKey;
        const value = link.dataset.value;
        // Only add if not 'All' or 'all'
        if (key && value && value.toLowerCase() !== 'all') {
          params[key] = value;
        }
      }
    });

    return params;
  }

  /**
   * Fetches product data via AJAX based on filters/sort/page and updates the UI.
   * @param {number} [page=1] - The page number to fetch.
   * @param {boolean} [pushState=true] - Whether to update browser history.
   */
  async function fetchAndUpdateProducts(page = 1, pushState = true) {
    if (!productGridContainer || !paginationContainer || !productCountDisplay || !loadingIndicator) {
      console.error("Essential elements for AJAX update are missing.");
      return;
    }

    // Abort any previous pending request
    if (currentRequestController) {
      currentRequestController.abort();
    }
    currentRequestController = new AbortController();
    const signal = currentRequestController.signal;

    // Show loading state
    loadingIndicator.style.display = "inline-block";
    productGridContainer.style.opacity = "0.5"; // Dim the grid

    const filters = getCurrentFiltersAndSort();
    const urlParams = new URLSearchParams(filters);
    urlParams.set('pg', page); // Add page number
    // Use XHR header to indicate AJAX on the backend
    // urlParams.set('ajax', '1'); // Backend checks HTTP_X_REQUESTED_WITH instead

    const url = `?page=shop_grid&${urlParams.toString()}`;

    try {
      const response = await fetch(url, {
          signal, // Pass the abort signal
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

      if (data.success) {
        // --- Update UI ---
        productGridContainer.innerHTML = data.productHtml || '<div class="alert alert-warning">Không có sản phẩm nào.</div>'; // Update grid
        paginationContainer.innerHTML = data.paginationHtml || ''; // Update pagination
        productCountDisplay.textContent = data.countText || 'Không có sản phẩm nào.'; // Update count text

        // --- Update Browser History ---
        if (pushState) {
          const stateUrl = `?${urlParams.toString()}`; // Build URL without page=shop_grid for cleaner history
           // Add base page parameter for clean URL
           const historyParams = new URLSearchParams(urlParams);
           historyParams.set('page', 'shop_grid'); // Add page identifier for direct linking
          window.history.pushState({ page: page, filters: filters }, '', `?${historyParams.toString()}`);
        }
      } else {
        // Handle error message from server
        console.error("AJAX request failed:", data.message || "Unknown server error");
        productGridContainer.innerHTML = `<div class="alert alert-danger">Lỗi: ${data.message || 'Không thể tải sản phẩm. Vui lòng thử lại.'}</div>`;
        paginationContainer.innerHTML = ''; // Clear pagination on error
        productCountDisplay.textContent = 'Đã xảy ra lỗi.';
      }
    } catch (error) {
      if (error.name === 'AbortError') {
        console.log('Fetch request was aborted.'); // Don't show error message if aborted by new request
      } else {
        console.error("Fetch error:", error);
        productGridContainer.innerHTML = '<div class="alert alert-danger">Lỗi kết nối hoặc xử lý. Vui lòng thử lại.</div>';
        paginationContainer.innerHTML = '';
        productCountDisplay.textContent = 'Đã xảy ra lỗi.';
      }
    } finally {
      // Hide loading state regardless of success or failure (unless aborted)
      if (!signal.aborted) {
          loadingIndicator.style.display = "none";
          productGridContainer.style.opacity = "1";
          currentRequestController = null; // Reset controller
      }
    }
  }

  // --- Event Listeners ---

  // 1. Filter Links (Brand, Price Range, Specs) using Event Delegation
  if (sidebar) {
    sidebar.addEventListener('click', function(event) {
      const targetLink = event.target.closest('.filter-link');
      if (targetLink && !targetLink.classList.contains('active')) { // Only trigger if not already active
        event.preventDefault();

        // Handle 'active' class toggle within the same group
        const filterGroup = targetLink.closest('.filter-options');
        if (filterGroup) {
          filterGroup.querySelectorAll('.filter-link.active').forEach(activeLink => {
            activeLink.classList.remove('active');
          });
          targetLink.classList.add('active');
          fetchAndUpdateProducts(1, true); // Fetch page 1 with new filter
        }
      }
    });
  }

  // 2. Sort Select Dropdown
  if (sortSelect) {
    sortSelect.addEventListener('change', function() {
      fetchAndUpdateProducts(1, true); // Fetch page 1 with new sort
    });
  }

  // 3. Search Form Submission
  if (filterSortForm) {
      // Listen for submit on the form itself
      filterSortForm.addEventListener('submit', function(event) {
          event.preventDefault(); // Prevent default form submission
          fetchAndUpdateProducts(1, true); // Fetch page 1 with search term
      });
      // Optional: Trigger search on Enter key press within the input
      searchInput?.addEventListener('keypress', function(event) {
         if (event.key === 'Enter') {
              event.preventDefault(); // Prevent default form submission if Enter is pressed
              fetchAndUpdateProducts(1, true);
         }
      });
  }


  // 4. Pagination Links (using Event Delegation on the container)
  if (paginationContainer) {
    paginationContainer.addEventListener('click', function(event) {
      const targetLink = event.target.closest('.ajax-page-link');
      // Ensure it's a link, not disabled, and not the currently active page
      if (targetLink && !targetLink.closest('.disabled') && !targetLink.closest('.active')) {
        event.preventDefault();
        const page = targetLink.dataset.page;
        if (page) {
          fetchAndUpdateProducts(parseInt(page, 10), true);
        }
      }
    });
  }

  // 5. Browser Back/Forward Navigation (Popstate)
  window.addEventListener('popstate', function(event) {
    if (event.state && event.state.page) {
      // When navigating back/forward, we don't want to push a new state
      // We need to restore the UI state based on event.state.filters if available
      // For simplicity here, we just fetch the page from the state
      // A more complex implementation would update the filter UI elements as well
      fetchAndUpdateProducts(event.state.page, false); // Don't push state again
    } else {
        // Handle initial state or states without page info (e.g., go back to non-shop page)
        // Maybe reload or fetch default page 1
        const params = new URLSearchParams(window.location.search);
        const page = parseInt(params.get('pg') || '1', 10);
         // Also re-apply filters from URL on popstate to initial load state
         // This requires updating filter UI elements based on URL params, which is complex.
         // Simpler approach: Fetch based on current URL params.
         fetchAndUpdateProducts(page, false);
    }
  });

}); // End DOMContentLoaded