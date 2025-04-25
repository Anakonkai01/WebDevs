<?php
// webfinal/app/Views/partials/pagination.php
// Các biến $currentPage, $totalPages được truyền từ Controller qua extract()

// Đảm bảo các biến này là số nguyên
$currentPage = (int)($currentPage ?? 1);
$totalPages = (int)($totalPages ?? 1);
?>
<nav aria-label="Product navigation" class="mt-4">
    <ul class="pagination justify-content-center">
        <?php if ($totalPages > 1):
            $prevDisabled = ($currentPage <= 1) ? 'disabled' : '';
            $nextDisabled = ($currentPage >= $totalPages) ? 'disabled' : '';
        ?>
            <li class="page-item <?= $prevDisabled ?>">
                <a class="page-link ajax-page-link" href="#page-<?= $currentPage - 1 ?>" data-page="<?= $currentPage - 1 ?>" aria-label="Previous">
                    <span aria-hidden="true">&laquo;</span>
                </a>
            </li>
            <?php
            // Logic hiển thị số trang và dấu "..." (Ví dụ đơn giản)
            $maxPagesToShow = 5; // Số lượng link trang tối đa hiển thị
            $halfMax = floor($maxPagesToShow / 2);

            $startPage = max(1, $currentPage - $halfMax);
            $endPage = min($totalPages, $startPage + $maxPagesToShow - 1);

            // Điều chỉnh startPage nếu endPage đạt giới hạn sớm
            if ($endPage - $startPage + 1 < $maxPagesToShow) {
                 $startPage = max(1, $endPage - $maxPagesToShow + 1);
            }


            if ($startPage > 1) {
                echo '<li class="page-item"><a class="page-link ajax-page-link" href="#page-1" data-page="1">1</a></li>';
                if ($startPage > 2) {
                    echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                }
            }

            for ($i = $startPage; $i <= $endPage; $i++) {
                $activeClass = ($i == $currentPage) ? 'active' : '';
                echo '<li class="page-item ' . $activeClass . '"><a class="page-link ajax-page-link" href="#page-' . $i . '" data-page="' . $i . '">' . $i . '</a></li>';
            }

            if ($endPage < $totalPages) {
                if ($endPage < $totalPages - 1) {
                     echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                }
                echo '<li class="page-item"><a class="page-link ajax-page-link" href="#page-'.$totalPages.'" data-page="'.$totalPages.'">'.$totalPages.'</a></li>';
            }
            ?>
            <li class="page-item <?= $nextDisabled ?>">
                 <a class="page-link ajax-page-link" href="#page-<?= $currentPage + 1 ?>" data-page="<?= $currentPage + 1 ?>" aria-label="Next">
                    <span aria-hidden="true">&raquo;</span>
                 </a>
            </li>
        <?php endif; ?>
    </ul>
</nav>