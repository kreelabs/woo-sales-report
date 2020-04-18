<?php

namespace KreeLabs\WSR\Admin\Tables\Sales;

use WP_List_Table;

use function KreeLabs\WSR\translate;

/**
 * Class AbstractCustomerList.
 *
 * @package KreeLabs\Admin\Tables\Sales
 */
abstract class AbstractCustomerList extends WP_List_Table
{
    /**
     * GuestVisitorList constructor.
     *
     * @param array $args
     */
    public function __construct(array $args = [])
    {
        parent::__construct($args);
    }

    /**
     * {@inheritdoc}
     */
    public function get_columns()
    {
        return apply_filters(static::$columnFilter, [
            'cb' => '<input type="checkbox" />',
            'display_name' => translate('Name'),
            'email' => translate('Email'),
            'total_orders' => translate('Orders'),
            'total_paid' => translate('Spent'),
            'last_order_date' => translate('Last order on'),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function get_sortable_columns()
    {
        return [
            'display_name' => ['display_name', false],
            'email' => ['email', false],
            'total_paid' => ['total_paid', false],
            'last_order_date' => ['last_order_date', true],
            'total_orders' => ['total_orders', false],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function get_views()
    {
        $url = '?page=woo-sales-report-insights';

        $current = '';
        if (isset($_GET['show'])) {
            $current = sanitize_key($_GET['show']);
        }

        return [
            "buyers" => "<a href='${url}' class='" . ($current === '' ? 'current' : '') . "'>" . translate('All Buyers') . '</a>',
            "registered_users" => "<a href='${url}&show=registered_users' class='" . ($current === 'registered_users' ? 'current' : '') . "'>" . translate('Registered') . "</a>",
            "guest_users" => "<a href='${url}&show=guest_users' class='" . ($current === 'guest_users' ? 'current' : '') . "'>" . translate('Guests') . '</a>',
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function column_cb($item)
    {
        return '<input type="checkbox" name="guests[]" value="" disabled />';
    }

    /**
     * {@inheritdoc}
     */
    public function get_bulk_actions()
    {

    }

    /**
     * {@inheritdoc}
     */
    public function prepare_items()
    {
        $this->_column_headers = [
            $this->get_columns(),
            [],
            $this->get_sortable_columns(),
        ];

        $this->items = null;

        // Process bulk action if any.
        $this->process_bulk_action();
    }

    /**
     * {@inheritdoc}
     */
    protected function display_tablenav($which)
    {
        if ('top' === $which) {
            wp_nonce_field('bulk-' . $this->_args['plural'], '_wpnonce', false);
        }
        ?>
        <div class="tablenav <?php echo esc_attr($which); ?>">
            <?php if ($this->has_items()) : ?>
                <div class="alignleft actions bulkactions">
                    <?php $this->bulk_actions($which); ?>
                </div>
            <?php
            endif;

            $this->extra_tablenav($which);
            $this->pagination($which);
            ?>

            <br class="clear"/>
        </div>
        <?php
    }

    /**
     * {@inheritdoc}
     */
    protected function extra_tablenav($which)
    {
        $position   = 'top';
        $exportType = -1;

        if (isset($_REQUEST['wsr_customer_filter']) && -1 != $_REQUEST['wsr_customer_filter']) {
            $position   = 'top';
            $exportType = sanitize_key($_REQUEST['wsr_customer_filter']);
        }

        if (isset($_REQUEST['wsr_customer_filter2']) && -1 != $_REQUEST['wsr_customer_filter2']) {
            $position   = 'bottom';
            $exportType = sanitize_key($_REQUEST['wsr_customer_filter2']);
        }
        ?>
        <div class="alignleft actions wsr-table-filters">
            <label for="wsr-customer-filter-data" class="screen-reader-text"><?php translate('Filter'); ?></label>
            <select name="<?= 'top' === $which ? 'wsr_customer_filter' : 'wsr_customer_filter2' ?>"
                    id="wsr-customer-filter-data">
                <?php
                $filters = [
                    '-1' => translate('-- All Dates --'),
                    'today' => translate('Today'),
                    'yesterday' => translate('Yesterday'),
                    'this-week' => translate('This Week'),
                    'this-month' => translate('This Month'),
                ];
                foreach ($filters as $key => $filter):
                    ?>
                    <option value="<?= $key ?>" <?= $position === $which && $key === $exportType ? 'selected' : '' ?>><?= $filter ?></option>
                <?php endforeach; ?>
            </select>
            <?php submit_button(translate('Filter'), '', 'filter_action', false, ['id' => 'filter-query-submit']); ?>
        </div>
        <?php
    }

    /**
     * {@inheritdoc}
     */
    public function process_bulk_action()
    {
        $action = $this->current_action();

        if ( ! in_array($action, ['filter', 'export'])) {
            return;
        }

        $nonce = esc_attr($_REQUEST['_wpnonce']);

        if ( ! wp_verify_nonce($nonce, 'bulk-' . $this->_args['plural'])) {
            die('Something went wrong. Please refresh the page and try again.');
        }

        $exportType = 'all';

        if (isset($_REQUEST['wsr_customer_filter']) && -1 != $_REQUEST['wsr_customer_filter']) {
            $exportType = sanitize_key($_REQUEST['wsr_customer_filter']);
        }

        if (isset($_REQUEST['wsr_customer_filter2']) && -1 != $_REQUEST['wsr_customer_filter2']) {
            $exportType = sanitize_key($_REQUEST['wsr_customer_filter2']);
        }

        switch ($action) {
            case 'filter':
                $this->process_filter_action($exportType);
                break;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function current_action()
    {
        $actions = ['filter_action' => 'filter', 'export_action' => 'export'];
        $paths   = ['wsr_customer_filter', 'wsr_customer_filter2'];

        foreach ($actions as $action => $identifier) {
            if ( ! isset($_REQUEST[$action])) {
                continue;
            }

            foreach ($paths as $path) {
                if (isset($_REQUEST[$path])) {
                    return $identifier;
                }
            }
        }

        return parent::current_action();
    }

    /**
     * Process filter action.
     *
     * @param string $exportType
     *
     * @return void
     */
    abstract protected function process_filter_action($exportType);
}
