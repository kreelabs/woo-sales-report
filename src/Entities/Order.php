<?php

namespace KreeLabs\WSR\Entities;

use KreeLabs\WSR\Database\Database;

use function KreeLabs\WSR\get_country_name;

class Order extends Entity
{
    /** @var string */
    protected $table;

    /**
     * {@inheritdoc}
     */
    public function __construct(Database $db)
    {
        parent::__construct($db);

        $this->table = $this->db->wpdb->posts;
    }

    /**
     * Get total orders count.
     *
     * @param array $filter
     *
     * @return int
     */
    public function getTotal(array $filter = [])
    {
        $wcOrders = wc_get_orders($filter + [
                'return' => 'ids',
                'limit' => -1,
            ]);

        return count($wcOrders);
    }

    /**
     * Get orders.
     *
     * @param array $args
     *
     * @return array
     */
    public function getOrders(array $args = [])
    {
        $wcOrders = wc_get_orders($args);

        $orders = [];
        foreach ($wcOrders as $order) {
            $data = $order->get_data();

            $data['items'] = $order->get_items();

            $orders[] = $data;
        }

        return $orders;
    }
}
