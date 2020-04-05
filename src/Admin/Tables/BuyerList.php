<?php

namespace KreeLabs\WSR\Admin\Tables\Sales;

use KreeLabs\WSR\Entities\Customer;

use function KreeLabs\WSR\translate;
use function KreeLabs\WSR\format_date;
use function KreeLabs\WSR\get_display_name;

/**
 * Class BuyerList.
 *
 * @package KreeLabs\Admin\Tables\Sales
 */
class BuyerList extends AbstractCustomerList
{
    /** @var Customer */
    protected $buyer;

    /** @var int */
    public $total;

    /** @var string */
    protected static $columnFilter = 'wsr_buyer_columns';

    /**
     * GuestList constructor.
     */
    public function __construct()
    {
        parent::__construct([
            'singular' => translate('Guest'),
            'plural' => translate('Guests'),
            'ajax' => false,
        ]);

        global $wcReport;

        $this->buyer = $wcReport->getCustomer();
        $this->total = $this->buyer->getBuyersTotal();
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
    public function prepare_items()
    {
        parent::prepare_items();

        $total       = $this->total;
        $filter      = [];
        $perPage     = $this->get_items_per_page('wsr_data_per_page', 25);
        $currentPage = $this->get_pagenum();

        if ( ! empty($_REQUEST['s'])) {
            $_REQUEST['s'] = trim($_REQUEST['s']);

            $keyword = '%' . esc_sql($_REQUEST['s']) . '%';
            $filter  = "first_name LIKE '$keyword' OR last_name LIKE '$keyword' OR email LIKE '$keyword'";
            $total   = $this->buyer->getBuyersTotal($filter);
        }

        $this->set_pagination_args([
            'total_items' => $total,
            'per_page' => $perPage,
        ]);

        if (is_null($this->items)) {
            $this->items = $this->buyer->getBuyersList($perPage, $currentPage, $filter);
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
        $this->items = $this->buyer->getFilteredBuyersData($exportType);
        $this->total = $this->buyer->getFilteredBuyersTotal($exportType);
    }

    /**
     * {@inheritdoc}
     */
    public function no_items()
    {
        echo translate('No buyers found.');
    }
}
