<?php if ( ! empty($_REQUEST['wsr_order_date_filter']) || ! empty($_REQUEST['_customer'])): ?>
    <div class="wsr-table-title ">
        <?php if ( ! empty($_REQUEST['s'])): ?>
            <h3 class="alignleft"><?= translate('Showing search results for') ?>
                &ldquo;<em><?= sanitize_text_field($_REQUEST['s']) ?></em>&rdquo;
            </h3>
        <?php elseif ( ! empty($_REQUEST['_customer'])):
            $user = get_user_by('id', esc_attr($_REQUEST['_customer']));
            if ( ! empty($user)):
                ?>
                <h3 class="alignleft"><?= translate('Showing search results for user') ?>
                    &ldquo;<em><?= $user->user_nicename ?></em>&rdquo;
                </h3>
            <?php endif; ?>
        <?php else: ?>
            <h3 class="alignleft"><?= translate('Showing filtered results') ?></h3>
        <?php endif; ?>
        <a class="alignleft" title="<?= translate('clear all filters') ?>"
           href="<?= admin_url() ?>admin.php?page=woo-sales-report-insights&tab=orders">✘ clear</a>
        <span class="clear"></span>
    </div>
<?php endif; ?>

<form method="GET" action="<?= admin_url() ?>admin.php"
      class="wsr-sales-insights-form page-<?= ! empty($_REQUEST['show']) ? esc_attr($_REQUEST['show']) : 'orders' ?>">
    <input type="hidden" name="page" value="<?= esc_attr($_REQUEST['page']) ?>"/>

    <?php if ( ! empty($_REQUEST['show'])): ?>
        <input type="hidden" name="show" value="<?= esc_attr($_REQUEST['show']) ?>"/>
    <?php endif; ?>

    <?php if ( ! empty($_REQUEST['_customer'])): ?>
        <input type="hidden" name="_customer" value="<?= esc_attr($_REQUEST['_customer']) ?>"/>
    <?php endif; ?>

    <input type="hidden" name="tab"
           value="<?= isset($_REQUEST['tab']) ? esc_attr($_REQUEST['tab']) : 'orders' ?>"/>
    <?php
    $list->prepare_items();
    $list->views();
    $list->search_box('Search', 'display_name');
    $list->display();
    ?>
</form>
