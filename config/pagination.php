<?php
// Build base URL preserving existing query params (search, status, etc.)
$queryParams = $_GET;
unset($queryParams['page']);
$baseQuery = !empty($queryParams) ? http_build_query($queryParams) . '&' : '';

// Window pagination: show max 5 page numbers
$window = 2;
$start_window = max(1, $page_active - $window);
$end_window   = min($total_page, $page_active + $window);
?>

<div class="pagination-wrapper mt-4 d-flex justify-content-between align-items-center flex-wrap gap-3">

    <!-- Entry info -->
    <div class="pagination-info">
        <span>Menampilkan</span>
        <strong><?= $start_data ?> – <?= $end_data ?></strong>
        <span>dari</span>
        <strong><?= $total_data ?></strong>
        <span>entri</span>
    </div>

    <!-- Page buttons -->
    <div class="pagination-controls">

        <!-- Prev -->
        <?php if ($page_active > 1): ?>
            <a href="?<?= $baseQuery ?>page=<?= $page_active - 1 ?>" class="page-btn page-nav">
                <i class="bi bi-chevron-left"></i>
            </a>
        <?php else: ?>
            <span class="page-btn page-nav disabled">
                <i class="bi bi-chevron-left"></i>
            </span>
        <?php endif; ?>

        <!-- First page + ellipsis -->
        <?php if ($start_window > 1): ?>
            <a href="?<?= $baseQuery ?>page=1" class="page-btn">1</a>
            <?php if ($start_window > 2): ?>
                <span class="page-ellipsis">…</span>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Page window -->
        <?php for ($i = $start_window; $i <= $end_window; $i++): ?>
            <a href="?<?= $baseQuery ?>page=<?= $i ?>"
               class="page-btn <?= ($i == $page_active) ? 'active' : '' ?>">
                <?= $i ?>
            </a>
        <?php endfor; ?>

        <!-- Last page + ellipsis -->
        <?php if ($end_window < $total_page): ?>
            <?php if ($end_window < $total_page - 1): ?>
                <span class="page-ellipsis">…</span>
            <?php endif; ?>
            <a href="?<?= $baseQuery ?>page=<?= $total_page ?>" class="page-btn"><?= $total_page ?></a>
        <?php endif; ?>

        <!-- Next -->
        <?php if ($page_active < $total_page): ?>
            <a href="?<?= $baseQuery ?>page=<?= $page_active + 1 ?>" class="page-btn page-nav">
                <i class="bi bi-chevron-right"></i>
            </a>
        <?php else: ?>
            <span class="page-btn page-nav disabled">
                <i class="bi bi-chevron-right"></i>
            </span>
        <?php endif; ?>

    </div>
</div>

<style>
.pagination-wrapper {
    border-top: 1px solid #f1f5f9;
    padding-top: 20px;
}
.pagination-info {
    font-size: 13px;
    color: #64748b;
    display: flex;
    align-items: center;
    gap: 4px;
}
.pagination-info strong {
    color: #1e293b;
    font-weight: 700;
}
.pagination-controls {
    display: flex;
    align-items: center;
    gap: 6px;
}
.page-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 36px;
    height: 36px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    color: #475569;
    background: #fff;
    border: 1px solid #e2e8f0;
    text-decoration: none;
    transition: all 0.18s ease;
    cursor: pointer;
    padding: 0 10px;
    font-family: 'Inter', sans-serif;
}
.page-btn:hover {
    background: #f1f5f9;
    border-color: #cbd5e1;
    color: #1e293b;
    transform: translateY(-1px);
    box-shadow: 0 2px 6px rgba(0,0,0,0.06);
}
.page-btn.active {
    background: linear-gradient(135deg, #2563eb, #7c3aed);
    border-color: transparent;
    color: #fff;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
    transform: translateY(-1px);
}
.page-btn.active:hover {
    background: linear-gradient(135deg, #1d4ed8, #6d28d9);
}
.page-btn.page-nav {
    color: #64748b;
    min-width: 36px;
    padding: 0;
}
.page-btn.disabled {
    opacity: 0.35;
    pointer-events: none;
    cursor: default;
}
.page-ellipsis {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 28px;
    height: 36px;
    font-size: 13px;
    color: #94a3b8;
    font-weight: 600;
    letter-spacing: 1px;
}
@media (max-width: 576px) {
    .pagination-wrapper { flex-direction: column; align-items: flex-start; }
    .page-btn { min-width: 32px; height: 32px; font-size: 12px; }
}
</style>