<?php

use function \KreeLabs\WSR\translate;

$url = '?page=woo-sales-report-insights';

$current = '';
if (isset($_GET['show'])) {
    $current = $_GET['show'];
}
?>

<div class="wsr-badge-container customer-badge">
    <a href="<?= $url ?>" class="wsr-badge <?= ($current === '' ? 'current' : '') ?>">
    <span class="wsr-badge-content">
            <h3><?= $buyerList->total ?></h3>
            <small><?= translate('Total checkout') ?></small>
        </span>
    </a>
    <a href="<?= $url ?>&show=registered_users"
       class="wsr-badge <?= ($current === 'registered_users' ? 'current' : '') ?>">
        <span class="wsr-badge-content">
            <h3><?= $customerList->total ?></h3>
            <small><?= translate('Registered checkout') ?></small>
        </span>
    </a>
    <a href="<?= $url ?>&show=guest_users" class="wsr-badge <?= ($current === 'guest_users' ? 'current' : '') ?>">
        <span class="wsr-badge-content">
            <h3><?= $guestList->total ?></h3>
            <small><?= translate('Guest checkout') ?></small>
        </span>
    </a>
    <div class="clear"></div>
</div>

<form method="GET" action="<?= admin_url() ?>admin.php"
      class="wsr-sales-insights-form page-<?= ! empty($_REQUEST['show']) ? $_REQUEST['show'] : 'buyers' ?>">
    <input type="hidden" name="page" value="<?= esc_attr($_REQUEST['page']) ?>"/>

    <?php if ( ! empty($_REQUEST['show'])): ?>
        <input type="hidden" name="show" value="<?= esc_attr($_REQUEST['show']) ?>"/>
    <?php endif; ?>

    <input type="hidden" name="tab"
           value="<?= isset($_REQUEST['tab']) ? esc_attr($_REQUEST['tab']) : 'customers' ?>"/>
    <?php
    $list->prepare_items();
    $list->views();
    $list->search_box('Search', 'display_name');
    $list->display();
    ?>
</form>
