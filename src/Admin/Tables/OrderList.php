<?php

namespace KreeLabs\WSR\Admin\Tables\Sales;

use KreeLabs\WSR\Entities\Order;

use function KreeLabs\WSR\translate;
use function KreeLabs\WSR\format_date;
use function KreeLabs\WSR\get_display_name;

/**
 * Class OrderList.
 *
 * @package KreeLabs\Admin\Tables\Sales
 */
class OrderList extends \WP_List_Table
{
    /** @var Order */
    protected $order;

    /**
     * OrderList constructor.
     */
    public function __construct()
    {
        parent::__construct([
            'singular' => translate('Order'),
            'plural' => translate('Orders'),
            'ajax' => false,
        ]);

        global $wcReport;

        $this->order = $wcReport->getOrder();

        add_filter('woocommerce_order_data_store_cpt_get_orders_query', function ($query, $vars) {
            if ( ! empty($vars['search'])) {
                $keyword = esc_attr($vars['search']);

                $query['meta_query'] = [
                    'relation' => 'OR',
                    [
                        'key' => '_billing_email',
                        'value' => $keyword,
                        'compare' => 'LIKE',
                    ],
                    [
                        'key' => '_billing_first_name',
                        'value' => $keyword,
                        'compare' => 'LIKE',
                    ],
                    [
                        'key' => '_billing_last_name',
                        'value' => $keyword,
                        'compare' => 'LIKE',
                    ],
                    [
                        'key' => '_billing_phone',
                        'value' => $keyword,
                        'compare' => 'LIKE',
                    ],
                ];
            }

            return $query;
        }, 10, 2);
    }

    /**
     * {@inheritdoc}
     */
    public function get_columns()
    {
        $columns = apply_filters('wsr_order_columns', [
            'cb' => '<input type="checkbox" />',
            'id' => translate('Order'),
            'date_created' => translate('Date'),
            'status' => translate('Status'),
            'total' => translate('Total'),
            'items' => translate('Items'),
        ]);

        return $columns;
    }

    /**
     * {@inheritdoc}
     */
    public function get_sortable_columns()
    {
        return [
            'id' => ['id', false],
            'date_created' => ['date_created', true],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function column_default($item, $column)
    {
        switch ($column) {
            case 'id':
                return sprintf(
                    '<b><a href="post.php?post=%d&action=edit">#%d %s (%s)</a></b>',
                    $item[$column],
                    $item[$column],
                    get_display_name($item['id']),
                    $item['billing']['email']
                );

            case 'date_created':
                return format_date($item[$column]);

            case 'status':
                return sprintf(
                    '<mark class="order-status status-%s"><span>%s</span></mark>',
                    $item[$column],
                    $item[$column]
                );

            case 'total':
                return sprintf(
                    '<a href="#" class="wsr-total wsr-toggle" title="%s">%s</a>',
                    translate('Switch to premium to view price breakdown'),
                    wc_price($item[$column])
                );

            case 'items':
                $items = [];
                foreach ($item[$column] as $product) {
                    $items[] = sprintf(
                        '<a href="%sedit.php?s=%s&post_status=all&post_type=product" target="_blank" title="%s">%s (%s)</a>',
                        admin_url(),
                        $product->get_product_id(),
                        translate('Click to view details'),
                        $product->get_product()->get_name(),
                        $product->get_quantity());
                }

                return sprintf(
                    '<a href="#" class="wsr-order-view-items wsr-toggle wsr-link-badge" title="%s">%s</a><br/><span class="wsr-details">&raquo; %s</span>',
                    translate('Click to view items'),
                    count($item[$column]),
                    implode('<br/>&raquo; ', $items)
                );
        }

        return $item[$column];
    }

    /**
     * {@inheritdoc}
     */
    public function current_action()
    {
        if ( ! empty($_REQUEST['filter_action'])) {
            return 'filter';
        }

        return false;
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

        // Process filter action if any.
        $args = $this->process_filter_action();

        if ( ! empty($_REQUEST['_customer'])) {
            $args += ['customer' => $_REQUEST['_customer']];
        }

        $searchArgs  = [];
        $perPage     = $this->get_items_per_page('wsr_data_per_page', 25);
        $currentPage = $this->get_pagenum();

        if ( ! empty($_REQUEST['s'])) {
            $keyword = trim($_REQUEST['s']);

            $searchArgs = [
                'search' => $keyword,
            ];
        }

        $args += $searchArgs;

        $total = $this->order->getTotal($args);

        $args += [
            'type' => 'shop_order',
            'limit' => $perPage,
            'offset' => ($currentPage - 1) * $perPage,
            'paged' => isset($_REQUEST['paged']) ? $_REQUEST['paged'] : 1,
            'orderby' => isset($_REQUEST['orderby']) ? $_REQUEST['orderby'] : 'date_created',
            'order' => isset($_REQUEST['order']) ? $_REQUEST['order'] : 'desc',
        ];

        $this->set_pagination_args([
            'total_items' => $total,
            'per_page' => $perPage,
        ]);

        $this->items = $this->order->getOrders($args);
    }

    /**
     * {@inheritdoc}
     */
    public function process_filter_action()
    {
        $dateFilter   = $this->getFilterType('wsr_order_date_filter');
        $statusFilter = $this->getFilterType('wsr_order_status_filter');

        $args = [];

        if ('all' !== $dateFilter) {
            $today = date('Y-m-d');

            switch ($dateFilter) {
                case 'today':
                    $args['date_created'] = $today;
                    break;

                case 'yesterday':
                    $args['date_created'] = date('Y-m-d', strtotime('-1 day'));
                    break;

                case 'this-week':
                    $args['date_created'] = date('Y-m-d', strtotime('monday this week')) . '...' . $today;
                    break;

                case 'this-month':
                    $args['date_created'] = date('Y-m-d', strtotime('first day of this month')) . '...' . $today;
                    break;
            }
        }

        if ('all' !== $statusFilter) {
            $args['status'] = $statusFilter;
        }

        return $args;
    }

    /**
     * Get filter type.
     *
     * @param string $key
     *
     * @return string
     */
    protected function getFilterType($key)
    {
        $type = 'all';

        if (isset($_REQUEST[$key]) && -1 != $_REQUEST[$key]) {
            $type = $_REQUEST[$key];
        }

        if (isset($_REQUEST[$key . '2']) && -1 != $_REQUEST[$key . '2']) {
            $type = $_REQUEST[$key . '2'];
        }

        return $type;
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
        ?>
        <div class="alignleft actions wsr-table-filters">
            <?php
            $this->dateFilter($which);
            $this->statusFilter($which);

            submit_button(translate('Filter'), '', 'filter_action', false,
                ['id' => 'filter-query-submit']);
            ?>
        </div>
        <?php
    }

    /**
     * Date filter.
     *
     * @param string $which
     *
     * @return void
     */
    protected function dateFilter($which)
    {
        $position   = 'top';
        $exportType = -1;

        if (isset($_REQUEST['wsr_order_date_filter']) && -1 != $_REQUEST['wsr_order_date_filter']) {
            $position   = 'top';
            $exportType = $_REQUEST['wsr_order_date_filter'];
        }

        if (isset($_REQUEST['wsr_order_date_filter2']) && -1 != $_REQUEST['wsr_order_date_filter2']) {
            $position   = 'bottom';
            $exportType = $_REQUEST['wsr_order_date_filter2'];
        }
        ?>
        <label for="wsr-order-date-filter" class="screen-reader-text"><?php translate('Filter by date'); ?></label>
        <select name="<?= 'top' === $which ? 'wsr_order_date_filter' : 'wsr_order_date_filter2' ?>"
                id="wsr-order-date-filter">
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
        <?php
    }

    /**
     * Status filter.
     *
     * @param string $which
     *
     * @return void
     */
    protected function statusFilter($which)
    {
        $position   = 'top';
        $exportType = -1;

        if (isset($_REQUEST['wsr_order_status_filter']) && -1 != $_REQUEST['wsr_order_status_filter']) {
            $position   = 'top';
            $exportType = $_REQUEST['wsr_order_status_filter'];
        }

        if (isset($_REQUEST['wsr_order_status_filter2']) && -1 != $_REQUEST['wsr_order_status_filter2']) {
            $position   = 'bottom';
            $exportType = $_REQUEST['wsr_order_status_filter2'];
        }
        ?>
        <label for="wsr-order-status-filter"
               class="screen-reader-text"><?php translate('Filter by status'); ?></label>
        <select name="<?= 'top' === $which ? 'wsr_order_status_filter' : 'wsr_order_status_filter2' ?>"
                id="wsr-order-status-filter">
            <?php
            $filters = ['-1' => translate('-- All Statuses --')] + wc_get_order_statuses();
            foreach ($filters as $key => $filter):
                ?>
                <option value="<?= $key ?>" <?= $position === $which && $key === $exportType ? 'selected' : '' ?>><?= $filter ?></option>
            <?php endforeach; ?>
        </select>
        <?php
    }

    /**
     * {@inheritdoc}
     */
    public function no_items()
    {
        echo translate('No orders found.');
    }
}
