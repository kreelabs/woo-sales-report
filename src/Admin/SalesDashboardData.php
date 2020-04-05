<?php

namespace KreeLabs\WSR\Admin;

use WP_Query;
use KreeLabs\WSR\Reports;
use KreeLabs\WSR\Database\Database;

use function KreeLabs\WSR\get_date_with_timezone;

class SalesDashboardData
{
    /** @var Database */
    protected $db;

    /** @var array */
    protected $orderStatuses;

    /**
     * SalesDashboardData constructor.
     */
    public function __construct()
    {
        $this->db = Reports::getDatabase();

        $this->orderStatuses = apply_filters(
            'wsr_sales_dashboard_order_status',
            ['wc-completed', 'wc-processing', 'wc-on-hold']
        );
    }

    /**
     * Get total earnings.
     *
     * @param string $for
     *
     * @return array|null|object
     */
    public function getTotalEarningsAndOrders($for = '')
    {
        $today         = get_date_with_timezone('now')->format('Y-m-d');
        $postsTable    = $this->db->wpdb->posts;
        $postMetaTable = $this->db->wpdb->postmeta;

        $query = "SELECT SUM(meta.meta_value) AS total_earnings, COUNT(posts.ID) AS total_orders FROM ${postsTable} AS posts
                  LEFT JOIN ${postMetaTable} AS meta ON posts.ID = meta.post_id
                  WHERE meta.meta_key = '_order_total'
                  AND posts.post_type = 'shop_order'
                  AND posts.post_status IN ('" . implode("','", $this->orderStatuses) . "')";

        switch ($for) {
            case 'today':
                $query .= ' AND DATE(posts.post_date) = "' . $today . '"';

                break;

            case 'this-month':
                $query .= ' AND DATE(posts.post_date) >= "' . get_date_with_timezone('first day of this month')->format('Y-m-d') . '"';
                $query .= ' AND DATE(posts.post_date) <= "' . $today . '"';

                break;

            case 'last-month':
                $query .= ' AND DATE(posts.post_date) >= "' . get_date_with_timezone('first day of previous month')->format('Y-m-d') . '"';
                $query .= ' AND DATE(posts.post_date) <= "' . get_date_with_timezone('last day of previous month')->format('Y-m-d') . '"';

                break;

            case 'this-year':
                $query .= ' AND DATE(posts.post_date) >= "' . get_date_with_timezone('first day of january this year')->format('Y-m-d') . '"';
                $query .= ' AND DATE(posts.post_date) <= "' . $today . '"';

                break;

            case 'last-year':
                $query .= ' AND DATE(posts.post_date) >= "' . get_date_with_timezone('first day of january previous year')->format('Y-m-d') . '"';
                $query .= ' AND DATE(posts.post_date) <= "' . get_date_with_timezone('last day of december previous year')->format('Y-m-d') . '"';

                break;
        }

        return $this->db->rawRow($query);
    }

    /**
     * Get total earnings.
     *
     * @param string $for
     *
     * @return array|null|object
     */
    public function getAverageEarningsAndOrders($for = '')
    {
        $totalEarnings = $this->getTotalEarningsAndOrders($for);

        $average = [];
        switch ($for) {
            case 'this-month':
                $days = date('t', mktime(0, 0, 0, date('m'), 1, date('Y')));
                $days = intval($days);

                $average['avg_sales']    = intval($totalEarnings['total_orders']) / $days;
                $average['avg_earnings'] = floatval($totalEarnings['total_earnings']) / $days;

                break;

            case 'last-month':
                $days = date('t', mktime(0, 0, 0, date('m', strtotime('first day of previous month')), 1,
                    date('Y', strtotime('first day of previous month'))));
                $days = intval($days);

                $average['avg_sales']    = intval($totalEarnings['total_orders']) / $days;
                $average['avg_earnings'] = floatval($totalEarnings['total_earnings']) / $days;

                break;

            case 'this-year':
            case 'last-year':
                $average['avg_sales']    = intval($totalEarnings['total_orders']) / 12;
                $average['avg_earnings'] = floatval($totalEarnings['total_earnings']) / 12;

                break;
        }

        return $average;
    }

    /**
     * Get earnings and sales per month given a year.
     *
     * @param int $year
     *
     * @return array|null|object
     */
    public function getSalesAndEarningsByMonths($year)
    {
        $postsTable    = $this->db->wpdb->posts;
        $postMetaTable = $this->db->wpdb->postmeta;

        $query = "SELECT SUM(meta.meta_value) AS total_earnings, COUNT(posts.ID) AS total_orders, MONTH(posts.post_date) as order_month FROM ${postsTable} AS posts
                  LEFT JOIN ${postMetaTable} AS meta ON posts.ID = meta.post_id
                  WHERE meta.meta_key = '_order_total'
                  AND posts.post_type = 'shop_order'
                  AND posts.post_status IN ('" . implode("','", $this->orderStatuses) . "')
                  AND DATE(posts.post_date) >= '" . date("$year-01-01") . "'
                  AND DATE(posts.post_date) <= '" . date("$year-12-31") . "'
                  GROUP BY order_month ORDER BY order_month ASC";

        return $this->db->raw($query);
    }

    /**
     * Get earnings and sales per month given a year.
     *
     * @param \DateTime $weekStartDate
     *
     * @return array|null|object
     */
    public function getSalesAndEarningsByWeek($weekStartDate)
    {
        $postsTable    = $this->db->wpdb->posts;
        $postMetaTable = $this->db->wpdb->postmeta;
        $weekStartDate = $weekStartDate->format('Y-m-d');
        $weekEndDate   = (new \DateTime($weekStartDate))->add(new \DateInterval('P7D'))->format('Y-m-d');

        $query = "SELECT SUM(meta.meta_value) AS total_earnings, COUNT(posts.ID) AS total_orders, DAYOFWEEK(posts.post_date) as order_day FROM ${postsTable} AS posts
                  LEFT JOIN ${postMetaTable} AS meta ON posts.ID = meta.post_id
                  WHERE meta.meta_key = '_order_total'
                  AND posts.post_type = 'shop_order'
                  AND posts.post_status IN ('" . implode("','", $this->orderStatuses) . "')
                  AND DATE(posts.post_date) >= '" . $weekStartDate . "'
                  AND DATE(posts.post_date) <= '" . $weekEndDate . "'
                  GROUP BY order_day ORDER BY order_day ASC";

        return $this->db->raw($query);
    }

    /**
     * Get top products by earning.
     *
     * @param int $limit
     *
     * @return array
     */
    public function getTopProductsByEarning($limit = 5)
    {
        $metaTable = $this->db->wpdb->prefix . 'woocommerce_order_itemmeta';

        $query = "SELECT om.meta_value as product_id, SUM(oim.meta_value) as subtotal FROM ${metaTable} om 
                    LEFT JOIN ${metaTable} oim ON oim.order_item_id = om.order_item_id 
                    WHERE om.meta_key='_product_id' AND oim.meta_key='_line_subtotal' GROUP BY product_id ORDER BY subtotal DESC LIMIT ${limit}";

        return $this->db->raw($query);
    }

    /**
     * Get top categories by earning.
     *
     * @return array
     */
    public function getTopCategoriesByEarning()
    {
        $productCategories = get_terms('product_cat', [
            'orderby' => 'name',
            'order' => 'asc',
            'hide_empty' => true,
        ]);

        $categoryEarningsCount = [];
        foreach ($productCategories as $productCategory) {
            $categoryEarningsCount[$productCategory->name] = $this->getSaleAmountAndCountByCategoryID($productCategory->term_id);
        }

        arsort($categoryEarningsCount);

        return $categoryEarningsCount;
    }

    /**
     * Get total sale amount by category ID.
     *
     * @param int $productCategoryId
     *
     * @return array
     */
    public function getSaleAmountAndCountByCategoryID($productCategoryId)
    {
        $categoryProducts = $this->getCategoryProducts($productCategoryId);

        if (empty($categoryProducts)) {
            return [
                'total' => 0,
                'qty' => 0,
            ];
        }

        // Get all successful orders.
        $query = new WP_Query([
            'post_type' => 'shop_order',
            'posts_per_page' => '-1',
            'post_status' => ['wc-processing', 'wc-completed', 'wc-on-hold'],
        ]);

        $qty    = 0;
        $total  = 0;
        $orders = $query->posts;
        foreach ($orders as $value) {
            $order = wc_get_order($value->ID);
            $items = $order->get_items();

            foreach ($items as $item) {
                $data = $item->get_data();

                if (in_array($data['product_id'], $categoryProducts)) {
                    $total += $item['subtotal'];
                    $qty   += $item['quantity'];
                }
            }
        }

        return [
            'total' => $total,
            'qty' => $qty,
        ];
    }

    /**
     * Get all product IDs for a given category.
     *
     * @param $productCategoryId
     *
     * @return array
     */
    public function getCategoryProducts($productCategoryId)
    {
        $query = new WP_Query([
            'post_type' => 'product',
            'pages_per_post' => -1,
            'post_status' => 'publish',
            'fields' => 'ids',
            'tax_query' => [
                [
                    'taxonomy' => 'product_cat',
                    'field' => 'term_id',
                    'terms' => $productCategoryId,
                ],
            ],
        ]);

        return $query->posts;
    }

    /**
     * Get top buyers.
     *
     * @param string $for
     *
     * @return array|null|object
     */
    public function getTopBuyers($for = '')
    {
        $postsTable         = $this->db->wpdb->posts;
        $orderItemsTable    = $this->db->wpdb->prefix . 'woocommerce_order_items';
        $orderItemMetaTable = $this->db->wpdb->prefix . 'woocommerce_order_itemmeta';

        $query = "SELECT p.ID, oi.order_id, SUM(oim.meta_value) AS subtotal, count(distinct p.ID) as total_orders FROM ${postsTable} p 
                    LEFT JOIN ${orderItemsTable} oi ON p.ID = oi.order_id 
                    LEFT JOIN ${orderItemMetaTable} oim ON oim.order_item_id = oi.order_item_id
                    WHERE p.post_type='shop_order' AND p.post_status IN ('" . implode("','",
                $this->orderStatuses) . "') AND (oi.order_item_type='line_item' AND oim.meta_key='_line_subtotal') OR (oi.order_item_type='shipping' AND oim.meta_key='cost')";

        $today = get_date_with_timezone('now')->format('Y-m-d');
        switch ($for) {
            case 'this-week':
                $query .= ' AND DATE(p.post_date) >= "' . get_date_with_timezone('monday this week')->format('Y-m-d') . '"';
                $query .= ' AND DATE(p.post_date) <= "' . $today . '"';

                break;

            case 'this-month':
                $query .= ' AND DATE(p.post_date) >= "' . get_date_with_timezone('first day of this month')->format('Y-m-d') . '"';
                $query .= ' AND DATE(p.post_date) <= "' . $today . '"';

                break;

            case 'last-6-months':
                $query .= ' AND DATE(p.post_date) >= "' . get_date_with_timezone('-6 month')->format('Y-m-d') . '"';
                $query .= ' AND DATE(p.post_date) <= "' . $today . '"';

                break;

            case 'this-year':
                $query .= ' AND DATE(p.post_date) >= "' . get_date_with_timezone('first day of january this year')->format('Y-m-d') . '"';
                $query .= ' AND DATE(p.post_date) <= "' . $today . '"';

                break;
        }

        $query .= ' GROUP BY oi.order_id
                    ORDER BY subtotal DESC
                    LIMIT 1';

        return $this->db->rawRow($query);
    }
}
