<?php

namespace KreeLabs\WSR\Admin;

use WP_Query;

use function KreeLabs\WSR\translate;

/**
 * Class ProductColumn.
 *
 * @package KreeLabs\Admin
 */
class ProductColumn
{
    /**
     * UserColumn constructor.
     */
    public function __construct()
    {
        add_filter('manage_edit-product_columns', [$this, 'addProductColumns'], 10, 1);
        add_action('manage_product_posts_custom_column', [$this, 'productColumnData'], 10, 2);

        add_filter('manage_edit-product_cat_columns', [$this, 'addProductCategoryColumns'], 10, 1);
        add_action('manage_product_cat_custom_column', [$this, 'productCategoryColumnData'], 10, 3);

        add_action('admin_enqueue_scripts', function () {
            wp_enqueue_style(
                'wsr-admin',
                plugins_url('/../resources/admin/css/admin.css', dirname(__FILE__))
            );
        });

        add_filter('post_row_actions', [$this, 'newRowActions'], 10, 2);
    }

    /**
     * Add new user columns.
     *
     * @param array $columnHeaders
     *
     * @return mixed
     */
    public function addProductColumns($columnHeaders)
    {
        $columnHeaders['unit_sold'] = translate('Unit Sold');
        $columnHeaders['earning']   = translate('Earning');

        return $columnHeaders;
    }

    /**
     * Add product row actions.
     *
     * @param array $actions
     * @param int   $product
     *
     * @return array
     */
    public function newRowActions(array $actions, $product)
    {
        if ('product' !== $product->post_type) {
            return $actions;
        }

        return $actions;
    }

    /**
     * Add new user columns.
     *
     * @param array $columnHeaders
     *
     * @return mixed
     */
    public function addProductCategoryColumns($columnHeaders)
    {
        $handle = $columnHeaders['handle'];

        unset($columnHeaders['handle']);

        $columnHeaders['earning'] = translate('Earning');
        $columnHeaders['handle']  = $handle;

        return $columnHeaders;
    }

    /**
     * Get data for new user columns.
     *
     * @param string $columnName
     * @param int    $productId
     *
     * @return void
     */
    public function productColumnData($columnName, $productId)
    {
        if ('unit_sold' === $columnName) {
            echo get_post_meta($productId, 'total_sales', true);
        }

        if ('earning' === $columnName) {
            $amount = $this->getProductSaleAmount($productId);

            echo is_numeric($amount) ? wc_price($amount) : wc_price(0);
        }
    }

    /**
     * Get total sale amount for a product.
     *
     * @param $productId
     *
     * @return int
     */
    public function getProductSaleAmount($productId)
    {
        global $wpdb;

        $metaTable = $wpdb->prefix . 'woocommerce_order_itemmeta';

        $query = "SELECT om.meta_value as product_id, oim.meta_value as subtotal FROM ${metaTable} om 
                    INNER JOIN ${metaTable} oim ON oim.order_item_id = om.order_item_id 
                    WHERE om.meta_value=${productId} AND om.meta_key='_product_id' AND oim.meta_key='_line_subtotal'";

        $productAmounts = $wpdb->get_results($query);

        $amount = 0;
        foreach ($productAmounts as $productAmount) {
            $amount += $productAmount->subtotal;
        }

        return $amount;
    }

    /**
     * Product category column data.
     *
     * @param mixed  $value
     * @param string $columnName
     * @param int    $productCategoryId
     *
     * @return void
     */
    public function productCategoryColumnData($value, $columnName, $productCategoryId)
    {
        if ('earning' === $columnName) {
            $amount = $this->getSaleAmountByCategoryID($productCategoryId);

            echo is_numeric($amount) ? wc_price($amount) : wc_price(0);
        }
    }

    /**
     * Get total sale amount by category ID.
     *
     * @param int $productCategoryId
     *
     * @return int
     */
    public function getSaleAmountByCategoryID($productCategoryId)
    {
        $categoryProducts = $this->getCategoryProducts($productCategoryId);

        if (empty($categoryProducts)) {
            return 0;
        }

        // Get all successful orders.
        $query = new WP_Query([
            'post_type' => 'shop_order',
            'posts_per_page' => '-1',
            'post_status' => ['wc-processing', 'wc-completed'],
        ]);

        $total  = 0;
        $orders = $query->posts;
        foreach ($orders as $value) {
            $order = wc_get_order($value->ID);
            $items = $order->get_items();

            foreach ($items as $item) {
                $data = $item->get_data();

                if (in_array($data['product_id'], $categoryProducts)) {
                    $total += $item['subtotal'];
                }
            }
        }

        return $total;
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
}
