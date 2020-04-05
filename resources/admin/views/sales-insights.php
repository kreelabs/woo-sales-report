<div id="wsr-sales-insights" class="wrap">
    <h1 class="title"><?= \KreeLabs\WSR\translate('Sales Insights') ?></h1>
    <h2 class="nav-tab-wrapper">
        <?php foreach ($tabs as $key => $tab): ?>
            <a href="?page=woo-sales-report-insights&tab=<?= $key ?>"
               class="nav-tab <?php echo $activeTab === $key ? 'nav-tab-active' : ''; ?>">
                <?= $tab ?>
            </a>
        <?php endforeach; ?>
    </h2>

    <div class="wsr-table">
        <?php
        // Flash message for delete.
        if (isset($_GET['wsr-deleted'])) {
            if ($_GET['wsr-deleted'] == 1) {
                \KreeLabs\WSR\success_notice('Record deleted successfully.');
            } else if ($_GET['wsr-deleted'] == 0) {
                \KreeLabs\WSR\error_notice('There was a problem. Please try again.');
            }
        }

        include WSR_PLUGIN_DIR . 'resources/admin/views/partials/insights/tab-' . $activeTab . '.php';
        ?>
    </div>
</div>
