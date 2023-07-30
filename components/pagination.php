<?php
function generatePaginationLinks($currentPage, $totalPages)
{
    $visiblePages = 3;
    $halfVisible = floor($visiblePages / 2);
    $startPage = max(1, min($currentPage - $halfVisible, $totalPages - $visiblePages + 1));
    $endPage = min($startPage + $visiblePages - 1, $totalPages);

    $paginationLinks = '';

    if ($startPage > 1) {
        $paginationLinks .= '<li class="page-item"><a href="?page=1" class="page-link">1</a></li>';
    }
    if ($startPage > 2) {
        $paginationLinks .= '<li class="page-item disabled"><a class="page-link">...</a></li>';
    }

    for ($i = $startPage; $i <= $endPage; $i++) {
        if ($i == $currentPage) {
            $paginationLinks .= '<li class="page-item active"><a href="?page=' . (int)$i . '" class="page-link">' . htmlentities($i, ENT_QUOTES, 'UTF-8') . '</a></li>';
        } else {
            $paginationLinks .= '<li class="page-item"><a href="?page=' . (int)$i . '" class="page-link">' . htmlentities($i, ENT_QUOTES, 'UTF-8') . '</a></li>';
        }
    }

    if ($endPage < $totalPages) {
        if ($endPage < $totalPages - 1) {
            $paginationLinks .= '<li class="page-item disabled"><a class="page-link">...</a></li>';
        }
        $paginationLinks .= '<li class="page-item"><a href="?page=' . (int)$totalPages . '" class="page-link">' . htmlentities($totalPages, ENT_QUOTES, 'UTF-8') . '</a></li>';
    }

    return $paginationLinks;
}