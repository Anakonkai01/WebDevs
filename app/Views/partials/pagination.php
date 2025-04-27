<?php
// Get current page and total pages
$currentPage = (int)($currentPage ?? 1);
$totalPages = (int)($totalPages ?? 1);

// Check if pagination is needed
if ($totalPages <= 1) {
    return;
}

// Page links to show around current page
$pageLinksToShow = 2;

?>
<nav aria-label="Product navigation">
    <ul class="pagination justify-content-center">
        <?php // Previous page link ?>
        <li class="page-item <?= ($currentPage <= 1) ? 'disabled' : '' ?>">
            <a class="page-link ajax-page-link"
               href="#page-<?= $currentPage - 1 ?>"
               data-page="<?= $currentPage - 1 ?>"
               aria-label="Previous">
                <span aria-hidden="true">&laquo;</span>
            </a>
        </li>

        <?php
        // Calculate start and end page for display
        $startPage = max(1, $currentPage - $pageLinksToShow);
        $endPage = min($totalPages, $currentPage + $pageLinksToShow);
        if ($currentPage - $startPage < $pageLinksToShow) {
            $endPage = min($totalPages, $endPage + ($pageLinksToShow - ($currentPage - $startPage)));
        }
        if ($endPage - $currentPage < $pageLinksToShow) {
            $startPage = max(1, $startPage - ($pageLinksToShow - ($endPage - $currentPage)));
        }
        // Display page number
        if ($startPage > 1) {
            echo '<li class="page-item"><a class="page-link ajax-page-link" href="#page-1" data-page="1">1</a></li>';
            if ($startPage > 2) {
                echo '<li class="page-item disabled"><span class="page-link px-2">...</span></li>';
            }
        }

        // Loop through the calculated page range and display page links
        for ($i = $startPage; $i <= $endPage; $i++) {
            // Determine if the current page is active
            $activeClass = ($i == $currentPage) ? 'active' : '';            
            echo '<li class="page-item ' . $activeClass . '">';
            if ($i == $currentPage) {
                echo '<span class="page-link">' . $i . '</span>';
            } else {
                echo '<a class="page-link ajax-page-link" href="#page-' . $i . '" data-page="' . $i . '">' . $i . '</a>';
            }
            echo '</li>';
        }

        // Display ... or last page link
        if ($endPage < $totalPages) {
            if ($endPage < $totalPages - 1) {
                echo '<li class="page-item disabled"><span class="page-link px-2">...</span></li>';
            }
            // Always show the last page link
            echo '<li class="page-item"><a class="page-link ajax-page-link" href="#page-' . $totalPages . '" data-page="' . $totalPages . '">' . $totalPages . '</a></li>';
        }
        ?>

        <?php // Next page link ?>
        <li class="page-item <?= ($currentPage >= $totalPages) ? 'disabled' : '' ?>">
            <a class="page-link ajax-page-link"
               href="#page-<?= $currentPage + 1 ?>"
               data-page="<?= $currentPage + 1 ?>"
               aria-label="Next">
                <span aria-hidden="true">&raquo;</span>
            </a>
        </li>
    </ul>
</nav>