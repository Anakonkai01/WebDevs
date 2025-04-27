<?php
// webfinal/app/Views/partials/pagination.php
// Variables $currentPage, $totalPages are passed from the Controller

// Ensure variables are integers and have defaults
$currentPage = (int)($currentPage ?? 1);
$totalPages = (int)($totalPages ?? 1);

// Don't render pagination if there's only one page
if ($totalPages <= 1) {
    return;
}

// Number of pages links to show around the current page
$pageLinksToShow = 2; // Example: 2 links before and 2 after current page
$maxPagesToShow = ($pageLinksToShow * 2) + 1; // Total links including current

?>
<nav aria-label="Product navigation">
    <ul class="pagination justify-content-center">
        <?php // Previous Page Link ?>
        <li class="page-item <?= ($currentPage <= 1) ? 'disabled' : '' ?>">
            <a class="page-link ajax-page-link"
               href="#page-<?= $currentPage - 1 ?>"
               data-page="<?= $currentPage - 1 ?>"
               aria-label="Previous">
                <span aria-hidden="true">&laquo;</span>
            </a>
        </li>

        <?php
        // Determine the range of pages to display
        $startPage = max(1, $currentPage - $pageLinksToShow);
        $endPage = min($totalPages, $currentPage + $pageLinksToShow);

        // Adjust start/end if near the beginning or end
        if ($currentPage - $startPage < $pageLinksToShow) {
            $endPage = min($totalPages, $endPage + ($pageLinksToShow - ($currentPage - $startPage)));
        }
        if ($endPage - $currentPage < $pageLinksToShow) {
            $startPage = max(1, $startPage - ($pageLinksToShow - ($endPage - $currentPage)));
        }

        // --- Display Page Links ---

        // Show first page and ellipsis if needed
        if ($startPage > 1) {
            echo '<li class="page-item"><a class="page-link ajax-page-link" href="#page-1" data-page="1">1</a></li>';
            if ($startPage > 2) {
                echo '<li class="page-item disabled"><span class="page-link px-2">...</span></li>';
            }
        }

        // Loop through the calculated range
        for ($i = $startPage; $i <= $endPage; $i++) {
            $activeClass = ($i == $currentPage) ? 'active' : '';
            echo '<li class="page-item ' . $activeClass . '">';
            // Use span for active page to prevent clicking
            if ($i == $currentPage) {
                 echo '<span class="page-link">' . $i . '</span>';
            } else {
                 echo '<a class="page-link ajax-page-link" href="#page-' . $i . '" data-page="' . $i . '">' . $i . '</a>';
            }
            echo '</li>';
        }

        // Show last page and ellipsis if needed
        if ($endPage < $totalPages) {
            if ($endPage < $totalPages - 1) {
                 echo '<li class="page-item disabled"><span class="page-link px-2">...</span></li>';
            }
            echo '<li class="page-item"><a class="page-link ajax-page-link" href="#page-'.$totalPages.'" data-page="'.$totalPages.'">'.$totalPages.'</a></li>';
        }
        ?>

        <?php // Next Page Link ?>
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
