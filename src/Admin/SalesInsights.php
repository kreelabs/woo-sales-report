<?php

namespace KreeLabs\WSR\Admin;

use KreeLabs\WSR\Admin\Tables\Sales\BuyerList;
use KreeLabs\WSR\Admin\Tables\Sales\GuestList;
use KreeLabs\WSR\Admin\Tables\Sales\OrderList;
use KreeLabs\WSR\Admin\Tables\Sales\CustomerList;

use function KreeLabs\WSR\translate;

/**
 * WooCommerce Sales Stats.
 *
 * @package KreeLabs\Admin
 */
class SalesInsights
{
    /**
     * Stats constructor.
     */
    public function __construct()
    {
        // Add stats sub menu.
        add_action('admin_menu', [$this, 'addMenu']);

        // Enqueue necessary scripts and styles for reports page.
        add_action('admin_enqueue_scripts', [$this, 'addAssets']);

        // Save screen options.
        add_filter('set-screen-option', [$this, 'setScreenOptions'], 10, 3);

        if ( ! class_exists('WP_List_Table')) {
            require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
        }
    }

    /**
     * Add stats sub page.
     *
     * @return void
     */
    public function addMenu()
    {
        $hook = add_submenu_page(
            'woo-sales-report',
            translate('Insights'),
            translate('Insights'),
            'manage_options',
            'woo-sales-report-insights',
            [$this, 'insights']
        );

        add_action("load-$hook", [$this, 'addOptions']);
    }

    /**
     * Add necessary scripts and styles for reports page.
     *
     * @return void
     */
    public function addAssets()
    {
        if (isset($_GET['page']) && $_GET['page'] === 'woo-sales-report-insights') {
            wp_enqueue_style(
                'wsr-sales-insights',
                plugins_url('/../resources/admin/css/stats.css', dirname(__FILE__))
            );

            wp_enqueue_script(
                'wsr-sales-insights',
                plugins_url('/../resources/admin/js/stats.js', dirname(__FILE__))
            );
        }
    }

    /**
     * Add screen options.
     *
     * @return void
     */
    public function addOptions()
    {
        add_screen_option('per_page', [
            'label' => translate('Data per page'),
            'default' => 25,
            'option' => 'wsr_data_per_page',
        ]);
    }

    /**
     * Set screen options.
     *
     * @param string $status
     * @param string $option
     * @param int    $value
     *
     * @return mixed
     */
    public function setScreenOptions($status, $option, $value)
    {
        if ('wsr_data_per_page' === $option) {
            return $value;
        }

        return $status;
    }

    /**
     * Add stats page.
     *
     * @return void
     */
    public function insights()
    {
        $tabs = [
            'customers' => translate('Customers'),
            'orders' => translate('Orders'),
        ];

        $activeTab = 'customers';
        if ( ! empty($_GET['tab']) && in_array($_GET['tab'], array_keys($tabs))) {
            $activeTab = $_GET['tab'];
        }

        $buyerList    = new BuyerList;
        $guestList    = new GuestList;
        $orderList    = new OrderList;
        $customerList = new CustomerList;

        $current = 'buyers';
        if (isset($_GET['show'])) {
            $current = $_GET['show'];
        }

        if ('customers' === $activeTab) {
            switch ($current) {
                case 'registered_users':
                    $list = $customerList;
                    break;

                case 'guest_users':
                    $list = $guestList;
                    break;

                default:
                    $list = $buyerList;
            }
        } else if ('orders' === $activeTab) {
            switch ($current) {
                default:
                    $list = $orderList;
            }
        }

        include WSR_PLUGIN_DIR . 'resources/admin/views/sales-insights.php';
    }
}
