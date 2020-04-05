<?php

namespace KreeLabs\WSR\Entities;

use KreeLabs\WSR\Database\Database;

class Customer extends Entity
{
    /** @var string */
    protected $table;

    /**
     * {@inheritdoc}
     */
    public function __construct(Database $db)
    {
        parent::__construct($db);

        $this->table = $this->db->wpdb->users;
    }

    /**
     * Get query for total customer count.
     *
     * @param array|string $filter
     *
     * @return string
     */
    public function getCustomerTotalQuery($filter = [])
    {
        $postsTable    = $this->db->wpdb->posts;
        $postMetaTable = $this->db->wpdb->postmeta;

        $query = "SELECT pm.email, max(p.post_date) AS last_order_date
                    FROM (
                        SELECT post_id, meta_value AS email FROM ${postMetaTable} WHERE meta_key='_billing_email'
                    ) pm
                    INNER JOIN ${postMetaTable} wpm ON pm.post_id=wpm.post_id
                    INNER JOIN ${postsTable} p ON wpm.post_id=p.ID
                    WHERE wpm.meta_key = '_customer_user' AND wpm.meta_value!=0 AND p.post_type='shop_order'";

        $query .= ' GROUP BY pm.email';

        if ( ! empty($filter)) {
            $query .= " HAVING $filter";
        }

        return "SELECT count(*) AS total FROM ($query) t";
    }

    /**
     * {@inheritdoc}
     */
    public function getTotal(array $filter = [])
    {
        return $this->db->rawCount($this->getCustomerTotalQuery($filter));
    }

    /**
     * Get query for customer listing.
     *
     * @param string|array $filter
     * @param int          $offset
     * @param int          $limit
     *
     * @return string
     */
    public function getCustomerQuery($filter, $offset = 0, $limit = WSR_EXPORT_LIMIT)
    {
        $postsTable    = $this->db->wpdb->posts;
        $postMetaTable = $this->db->wpdb->postmeta;

        $query = "SELECT p.ID, pm.email, count(p.ID) AS total_orders, sum(ot.total_paid) AS total_paid, max(p.post_date) AS last_order_date, wpm.meta_value as uid
                    FROM (
                        SELECT post_id, meta_value AS email FROM ${postMetaTable} WHERE meta_key='_billing_email'
                    ) pm
                    INNER JOIN ${postMetaTable} wpm ON pm.post_id=wpm.post_id
                    INNER JOIN ${postsTable} p ON wpm.post_id=p.ID
                    INNER JOIN (
                      SELECT post_id, meta_value AS total_paid FROM ${postMetaTable} WHERE meta_key='_order_total'
                    ) ot ON p.ID=ot.post_id
                    WHERE wpm.meta_key = '_customer_user' AND wpm.meta_value!=0 AND p.post_type='shop_order'";

        $query .= ' GROUP BY pm.email';

        if ( ! empty($filter)) {
            $query .= " HAVING $filter";
        }

        if ( ! empty($_REQUEST['orderby'])) {
            $order = ! empty($_REQUEST['order']) ? esc_sql($_REQUEST['order']) : 'desc';

            if ( ! in_array(strtolower($order), ['asc', 'desc'])) {
                $order = 'desc';
            }

            $query .= " ORDER BY ${_REQUEST['orderby']} ${order}";
        } else {
            $query .= " ORDER BY last_order_date desc";
        }

        $query .= " LIMIT ${limit}";

        if ( ! empty($offset)) {
            $query .= " OFFSET $offset";
        }

        return $query;
    }

    /**
     * {@inheritdoc}
     */
    public function getList($perPage, $currentPage, array $filter = [])
    {
        $query = $this->getCustomerQuery($filter, ($currentPage - 1) * $perPage, $perPage);

        return $this->db->raw($query);
    }

    /**
     * Get query for total buyers count.
     *
     * @param string $filter
     *
     * @return string
     */
    public function getBuyersTotalQuery($filter = '')
    {
        $postsTable    = $this->db->wpdb->posts;
        $postMetaTable = $this->db->wpdb->postmeta;

        $query = "SELECT pm.email
                    FROM (
                        SELECT post_id, meta_value AS email FROM ${postMetaTable} WHERE meta_key='_billing_email'
                    ) pm
                    INNER JOIN ${postMetaTable} wpm ON pm.post_id=wpm.post_id
                    INNER JOIN ${postsTable} p ON wpm.post_id=p.ID
                    WHERE wpm.meta_key = '_customer_user' AND p.post_type='shop_order'";

        $query .= ' GROUP BY pm.email';

        if ( ! empty($filter)) {
            $query .= " HAVING $filter";
        }

        return "SELECT count(*) AS total FROM ($query) t";
    }

    /**
     * Get query for total guest count.
     *
     * @param string $filter
     *
     * @return string
     */
    public function getGuestTotalQuery($filter = '')
    {
        $postsTable    = $this->db->wpdb->posts;
        $postMetaTable = $this->db->wpdb->postmeta;

        $query = "SELECT pm.email, max(p.post_date) AS last_order_date
                    FROM (
                        SELECT post_id, meta_value AS email FROM ${postMetaTable} WHERE meta_key='_billing_email'
                    ) pm
                    INNER JOIN ${postMetaTable} wpm ON pm.post_id=wpm.post_id
                    INNER JOIN ${postsTable} p ON wpm.post_id=p.ID
                    WHERE wpm.meta_key = '_customer_user' AND wpm.meta_value=0 AND p.post_type='shop_order'";

        $query .= ' GROUP BY pm.email';

        if ( ! empty($filter)) {
            $query .= " HAVING $filter";
        }

        return "SELECT count(*) AS total FROM ($query) t";
    }

    /**
     * Get query for all buyers listing. `_billing_first_name` and `_billing_last_name`
     * may not be always present, so we need to get it when we display the list instead
     * of joining post meta table.
     *
     * @param string|array $filter
     * @param int          $offset
     * @param int          $limit
     *
     * @return string
     */
    public function getBuyersQuery($filter, $offset = 0, $limit = WSR_EXPORT_LIMIT)
    {
        $postsTable    = $this->db->wpdb->posts;
        $postMetaTable = $this->db->wpdb->postmeta;

        $query = "SELECT p.ID, pm.email, count(p.ID) AS total_orders, sum(ot.total_paid) AS total_paid, max(p.post_date) AS last_order_date  
                    FROM (
                        SELECT post_id, meta_value AS email FROM ${postMetaTable} WHERE meta_key='_billing_email'
                    ) pm
                    INNER JOIN ${postMetaTable} wpm ON pm.post_id=wpm.post_id
                    INNER JOIN ${postsTable} p ON wpm.post_id=p.ID
                    INNER JOIN (
                      SELECT post_id, meta_value AS total_paid FROM ${postMetaTable} WHERE meta_key='_order_total'
                    ) ot ON p.ID=ot.post_id
                    WHERE wpm.meta_key = '_customer_user' AND p.post_type='shop_order'";

        $query .= ' GROUP BY pm.email';

        if ( ! empty($filter)) {
            $query .= " HAVING $filter";
        }

        if ( ! empty($_REQUEST['orderby'])) {
            $order = ! empty($_REQUEST['order']) ? esc_sql($_REQUEST['order']) : 'desc';

            if ( ! in_array(strtolower($order), ['asc', 'desc'])) {
                $order = 'desc';
            }

            $query .= " ORDER BY ${_REQUEST['orderby']} ${order}";
        } else {
            $query .= " ORDER BY last_order_date desc";
        }

        $query .= " LIMIT ${limit}";

        if ( ! empty($offset)) {
            $query .= " OFFSET $offset";
        }

        return $query;
    }

    /**
     * Get query for guest listing.
     *
     * @param string|array $filter
     * @param int          $offset
     * @param int          $limit
     *
     * @return string
     */
    public function getGuestQuery($filter, $offset = 0, $limit = WSR_EXPORT_LIMIT)
    {
        $postsTable    = $this->db->wpdb->posts;
        $postMetaTable = $this->db->wpdb->postmeta;

        $query = "SELECT p.ID, pm.email, count(p.ID) AS total_orders, sum(ot.total_paid) AS total_paid, max(p.post_date) AS last_order_date  
                    FROM (
                        SELECT post_id, meta_value AS email FROM ${postMetaTable} WHERE meta_key='_billing_email'
                    ) pm
                    INNER JOIN ${postMetaTable} wpm ON pm.post_id=wpm.post_id
                    INNER JOIN ${postsTable} p ON wpm.post_id=p.ID
                    INNER JOIN (
                      SELECT post_id, meta_value AS total_paid FROM ${postMetaTable} WHERE meta_key='_order_total'
                    ) ot ON p.ID=ot.post_id
                    WHERE wpm.meta_key = '_customer_user' AND wpm.meta_value=0 AND p.post_type='shop_order'";

        $query .= ' GROUP BY pm.email';

        if ( ! empty($filter)) {
            $query .= " HAVING $filter";
        }

        if ( ! empty($_REQUEST['orderby'])) {
            $order = ! empty($_REQUEST['order']) ? esc_sql($_REQUEST['order']) : 'desc';

            if ( ! in_array(strtolower($order), ['asc', 'desc'])) {
                $order = 'desc';
            }

            $query .= " ORDER BY ${_REQUEST['orderby']} ${order}";
        } else {
            $query .= " ORDER BY last_order_date desc";
        }

        $query .= " LIMIT ${limit}";

        if ( ! empty($offset)) {
            $query .= " OFFSET $offset";
        }

        return $query;
    }

    /**
     * Get buyers list.
     *
     * @param int    $perPage
     * @param int    $currentPage
     * @param string $filter
     *
     * @return array|null|object
     */
    public function getBuyersList($perPage, $currentPage, $filter = '')
    {
        $query = $this->getBuyersQuery($filter, ($currentPage - 1) * $perPage, $perPage);

        return $this->db->raw($query);
    }

    /**
     * Get total buyers count.
     *
     * @param string $filter
     *
     * @return int
     */
    public function getBuyersTotal($filter = '')
    {
        $query = $this->getBuyersTotalQuery($filter);

        return $this->db->rawCount($query);
    }

    /**
     * Get guest list.
     *
     * @param int    $perPage
     * @param int    $currentPage
     * @param string $filter
     *
     * @return array|null|object
     */
    public function getGuestList($perPage, $currentPage, $filter = '')
    {
        $query = $this->getGuestQuery($filter, ($currentPage - 1) * $perPage, $perPage);

        return $this->db->raw($query);
    }

    /**
     * Get total guest users.
     *
     * @param string $filter
     *
     * @return int
     */
    public function getGuestTotal($filter = '')
    {
        $query = $this->getGuestTotalQuery($filter);

        return $this->db->rawCount($query);
    }

    /**
     * Get post meta by key.
     *
     * @param string $key
     * @param string $value
     *
     * @return array|null|object
     */
    public function getPostIdByMetaData($key, $value)
    {
        $postMetaTable = $this->db->wpdb->postmeta;

        $query = "SELECT DISTINCT post_id FROM ${postMetaTable} WHERE meta_key='${key}' AND meta_value='${value}'";

        return $this->db->raw($query);
    }

    /**
     * Get customer data to export.
     *
     * @param string $exportType
     *
     * @return array
     */
    public function getFilteredCustomerData($exportType)
    {
        $query = $this->getCustomerQuery($this->getExportDateFilterRaw($exportType, 'last_order_date'));

        return $this->db->raw($query);
    }

    /**
     * Get filtered customer total.
     *
     * @param string $exportType
     *
     * @return int
     */
    public function getFilteredCustomerTotal($exportType)
    {
        $query = $this->getCustomerTotalQuery($this->getExportDateFilterRaw($exportType, 'last_order_date'));

        return $this->db->rawCount($query);
    }

    /**
     * Get buyers data to export.
     *
     * @param string $exportType
     *
     * @return array
     */
    public function getFilteredBuyersData($exportType)
    {
        $query = $this->getBuyersQuery($this->getExportDateFilterRaw($exportType, 'last_order_date'));

        return $this->db->raw($query);
    }

    /**
     * Get filtered buyers total.
     *
     * @param string $exportType
     *
     * @return int
     */
    public function getFilteredBuyersTotal($exportType)
    {
        $query = $this->getCustomerTotalQuery($this->getExportDateFilterRaw($exportType, 'last_order_date'));

        return $this->db->rawCount($query);
    }

    /**
     * Get customer data to export.
     *
     * @param string $exportType
     *
     * @return array
     */
    public function getFilteredGuestData($exportType)
    {
        $query = $this->getGuestQuery($this->getExportDateFilterRaw($exportType, 'last_order_date'));

        return $this->db->raw($query);
    }

    /**
     * Get filtered customer total.
     *
     * @param string $exportType
     *
     * @return int
     */
    public function getFilteredGuestTotal($exportType)
    {
        $query = $this->getGuestTotalQuery($this->getExportDateFilterRaw($exportType, 'last_order_date'));

        return $this->db->rawCount($query);
    }

    /**
     * Get collection per country.
     *
     * @return array
     */
    public function getCollectionPerCountry()
    {
        $postsTable         = $this->db->wpdb->posts;
        $postMetaTable      = $this->db->wpdb->postmeta;
        $orderItemsTable    = $this->db->wpdb->prefix . 'woocommerce_order_items';
        $orderItemMetaTable = $this->db->wpdb->prefix . 'woocommerce_order_itemmeta';

        $query = "SELECT pm.country AS country, SUM(oim.meta_value) AS subtotal
                    FROM (
                        SELECT post_id, meta_value AS country FROM ${postMetaTable} WHERE meta_key='_billing_country'
                    ) pm
                    INNER JOIN ${postMetaTable} wpm ON pm.post_id=wpm.post_id
                    INNER JOIN ${postsTable} p ON wpm.post_id=p.ID
                    INNER JOIN ${orderItemsTable} oi ON p.ID = oi.order_id 
                    INNER JOIN ${orderItemMetaTable} oim ON oim.order_item_id = oi.order_item_id
                    WHERE wpm.meta_key = '_customer_user' AND p.post_type='shop_order' AND oi.order_item_type='line_item' AND oim.meta_key='_line_subtotal' 
                    GROUP BY pm.country";

        $collections = $this->db->raw($query);

        $collectionPerCountry = [];
        foreach ($collections as $collection) {
            $collectionPerCountry[$collection['country']] = [
                'subtotal' => $collection['subtotal'],
            ];
        }

        return $collectionPerCountry;
    }

    /**
     * Get collection per payment method.
     *
     * @return array
     */
    public function getCollectionPerPaymentMethod()
    {
        $postsTable         = $this->db->wpdb->posts;
        $postMetaTable      = $this->db->wpdb->postmeta;
        $orderItemsTable    = $this->db->wpdb->prefix . 'woocommerce_order_items';
        $orderItemMetaTable = $this->db->wpdb->prefix . 'woocommerce_order_itemmeta';

        $query = "SELECT pm.payment_method AS payment_method, SUM(oim.meta_value) AS subtotal
                    FROM (
                        SELECT post_id, meta_value AS payment_method FROM ${postMetaTable} WHERE meta_key='_payment_method'
                    ) pm
                    INNER JOIN ${postMetaTable} wpm ON pm.post_id=wpm.post_id
                    INNER JOIN ${postsTable} p ON wpm.post_id=p.ID
                    INNER JOIN ${orderItemsTable} oi ON p.ID = oi.order_id 
                    INNER JOIN ${orderItemMetaTable} oim ON oim.order_item_id = oi.order_item_id
                    WHERE wpm.meta_key = '_customer_user' AND p.post_type='shop_order' AND oi.order_item_type='line_item' AND oim.meta_key='_line_subtotal' 
                    GROUP BY pm.payment_method";

        $collections = $this->db->raw($query);

        $collectionPerCountry = [];
        foreach ($collections as $collection) {
            $collectionPerCountry[$collection['payment_method']] = [
                'subtotal' => $collection['subtotal'],
            ];
        }

        return $collectionPerCountry;
    }
}
