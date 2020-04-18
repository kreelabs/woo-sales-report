<?php

namespace KreeLabs\WSR\Admin\Tables\Sales;

use KreeLabs\WSR\Entities\Customer;

use function KreeLabs\WSR\translate;
use function KreeLabs\WSR\format_date;
use function KreeLabs\WSR\get_display_name;

/**
 * Class CustomerList.
 *
 * @package KreeLabs\Admin\Tables\Sales
 */
class CustomerList extends AbstractCustomerList
{
    /** @var Customer */
    protected $customer;

    /** @var int */
    public $total;

    /** @var string */
    protected static $columnFilter = 'wsr_customer_columns';

    /**
     * CustomerList constructor.
     */
    public function __construct()
    {
        parent::__construct([
            'singular' => translate('Customer'),
            'plural' => translate('Customers'),
            'ajax' => false,
        ]);

        global $wcReport;

        $this->customer = $wcReport->getCustomer();
        $this->total    = $this->customer->getTotal();
    }

    /**
     * {@inheritdoc}
     */
    public function get_columns()
    {
        $columns = parent::get_columns();

        $columns += [
            'registered_date' => translate('Registered on'),
        ];

        return $columns;
    }

    /**
     * {@inheritdoc}
     */
    public function get_sortable_columns()
    {
        $sortableColumns = parent::get_sortable_columns();

        return $sortableColumns + ['registered_date' => ['registered_date', false]];
    }

    /**
     * {@inheritdoc}
     */
    protected function column_default($item, $column)
    {
        switch ($column) {
            case 'display_name':
                return get_display_name($item['ID']);

            case 'total_paid':
                return wc_price($item[$column]);

            case 'registered_date':
                $user = get_userdata($item['uid']);

                if (isset($user->user_registered)) {
                    return format_date($user->user_registered);
                }

                return '-';

            case 'last_order_date':
                return format_date($item[$column]);

            case 'total_orders':
                $email   = $item['email'];
                $wpNonce = wp_create_nonce('bulk-' . $this->_args['plural']);

                return sprintf(
                    '<a href="%s" title="%s" class="wsr-link-badge">%s</a>',
                    admin_url() . "admin.php?page=woo-sales-report-insights&tab=orders&_wpnonce=${wpNonce}&s=${email}&filter_action=Filter",
                    translate('View all orders from') . ' ' . $email,
                    $item[$column]
                );
        }

        return $item[$column];
    }

    /**
     * {@inheritdoc}
     */
    protected function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="customers[]" value="%s" disabled />', $item['ID']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function prepare_items()
    {
        parent::prepare_items();

        $total       = $this->total;
        $filter      = [];
        $perPage     = $this->get_items_per_page('wsr_data_per_page', 25);
        $currentPage = $this->get_pagenum();

        if ( ! empty($_REQUEST['s'])) {
            $keyword = '%' . esc_sql(sanitize_text_field(trim($_REQUEST['s']))) . '%';
            $filter  = "first_name LIKE '$keyword' OR last_name LIKE '$keyword' OR email LIKE '$keyword'";
            $total   = $this->customer->getTotal($filter);
        }

        $this->set_pagination_args([
            'total_items' => $total,
            'per_page' => $perPage,
        ]);

        if (is_null($this->items)) {
            $this->items = $this->customer->getList($perPage, $currentPage, $filter);
        }
    }

    /**
     * Process filter action.
     *
     * @param string $exportType
     *
     * @return void
     */
    protected function process_filter_action($exportType)
    {
        $this->items = $this->customer->getFilteredCustomerData($exportType);
        $this->total = $this->customer->getFilteredCustomerTotal($exportType);
    }

    /**
     * {@inheritdoc}
     */
    public function no_items()
    {
        echo translate('No customers found.');
    }
}
