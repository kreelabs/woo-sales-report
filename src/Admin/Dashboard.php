<?php

namespace KreeLabs\WSR\Admin;

use function KreeLabs\WSR\translate;

/**
 * Woo Sales Report Dashboard.
 *
 * @package KreeLabs\Admin
 */
class Dashboard
{
    /** @const string */
    const VIEW_PATH = WSR_PLUGIN_DIR . 'resources/admin/views';

    /** @var SalesDashboardData */
    protected $salesDashboardData;

    /**
     * Dashboard constructor.
     */
    public function __construct()
    {
        // Add dashboard menu.
        add_action('admin_menu', [$this, 'addMenu']);

        // Enqueue necessary scripts and styles for reports page.
        add_action('admin_enqueue_scripts', [$this, 'addAssets']);

        $this->salesDashboardData = new SalesDashboardData();
    }

    /**
     * Add main menu page.
     *
     * @return void
     */
    public function addMenu()
    {
        add_menu_page(
            translate('Woo Sales Report'),
            translate('Woo Sales Report'),
            'manage_options',
            'woo-sales-report',
            [$this, 'salesDashboard'],
            'dashicons-analytics'
        );

        add_submenu_page(
            'woo-sales-report',
            translate('Dashboard'),
            translate('Dashboard'),
            'manage_options',
            'woo-sales-report',
            [$this, 'salesDashboard']
        );
    }

    /**
     * Add necessary scripts and styles for reports page.
     *
     * @return void
     */
    public function addAssets()
    {
        if (isset($_GET['page']) && $_GET['page'] === 'woo-sales-report') {
            wp_enqueue_style(
                'wsr-materialize',
                plugins_url('/../resources/admin/css/materialize.min.css', dirname(__FILE__))
            );
            wp_enqueue_style(
                'wsr-dashboard',
                plugins_url('/../resources/admin/css/dashboard.css', dirname(__FILE__))
            );

            wp_enqueue_script(
                'wsr-materialize',
                plugins_url('/../resources/admin/js/materialize.min.js', dirname(__FILE__))
            );
            wp_enqueue_script(
                'wsr-chart',
                plugins_url('/../resources/admin/js/chart.min.js', dirname(__FILE__))
            );
            wp_enqueue_script(
                'wsr-dashboard',
                plugins_url('/../resources/admin/js/dashboard.js', dirname(__FILE__))
            );
        }
    }

    /**
     * Reports template.
     *
     * @return void
     */
    public function salesDashboard()
    {
        $totalEarningsAndOrders          = $this->salesDashboardData->getTotalEarningsAndOrders();
        $totalEarningsAndOrdersToday     = $this->salesDashboardData->getTotalEarningsAndOrders('today');
        $totalEarningsAndOrdersThisMonth = $this->salesDashboardData->getTotalEarningsAndOrders('this-month');
        $totalEarningsAndOrdersLastMonth = $this->salesDashboardData->getTotalEarningsAndOrders('last-month');

        $avgEarningsAndOrdersThisMonth = $this->salesDashboardData->getAverageEarningsAndOrders('this-month');
        $avgEarningsAndOrdersLastMonth = $this->salesDashboardData->getAverageEarningsAndOrders('last-month');
        $avgEarningsAndOrdersThisYear  = $this->salesDashboardData->getAverageEarningsAndOrders('this-year');
        $avgEarningsAndOrdersLastYear  = $this->salesDashboardData->getAverageEarningsAndOrders('last-year');

        $topProducts = $this->salesDashboardData->getTopProductsByEarning();

        $topProductsChartData = [
            'labels' => [],
            'data' => [],
            'backgroundColors' => [
                "rgb(97, 189, 249)",
                "rgb(97, 165, 69)",
                "rgb(135, 32, 214)",
                "rgb(180, 147, 137)",
                "rgb(78, 196, 180)",
            ],
        ];

        foreach ($topProducts as $topProduct) {
            $product = wc_get_product($topProduct['product_id']);

            if ($product === false) {
                continue;
            }

            $topProductsChartData['data'][]   = $topProduct['subtotal'];
            $topProductsChartData['labels'][] = $product->get_title();
        }

        $topCategories = $this->salesDashboardData->getTopCategoriesByEarning();

        $topCategoriesChartData = ['labels' => [], 'data' => []];
        foreach ($topCategories as $label => $data) {
            $topCategoriesChartData['labels'][]        = $label;
            $topCategoriesChartData['data']['qty'][]   = $data['qty'];
            $topCategoriesChartData['data']['total'][] = $data['total'];
        }

        include self::VIEW_PATH . '/sales-dashboard.php';
    }

    /**
     * Call view files statically.
     *
     * @method colorStatBox(array $options)
     * @method statBox(array $options)
     * @method tableHorizontal(array $options)
     * @method tableVertical(array $options)
     * @method chart(array $options)
     * @method infoCard(array $options)
     * @method salesCard(array $options)
     *
     * @param string $name
     * @param array  $options
     *
     * @return void
     */
    public static function __callStatic($name, $options)
    {
        $dashify = function ($word) {
            preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $word, $matches);

            $ret = $matches[0];
            foreach ($ret as &$match) {
                $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
            }

            return implode('-', $ret);
        };

        $options = array_pop($options);

        include self::VIEW_PATH . '/partials/dashboard/' . $dashify($name) . '.php';
    }
}
