<?php

namespace KreeLabs\WSR;

use WooCommerce;
use KreeLabs\WSR\Entities\Customer;
use KreeLabs\WSR\Database\Database;
use KreeLabs\WSR\Entities\Order;
use KreeLabs\WSR\Admin\Dashboard;
use KreeLabs\WSR\Entities\Product;
use KreeLabs\WSR\Admin\UserColumn;
use KreeLabs\WSR\Admin\ProductColumn;
use KreeLabs\WSR\Admin\SalesInsights;

/**
 * Plugin bootstrap class.
 */
class Reports
{
    /** @var Database */
    protected static $db;

    /** @var Customer */
    protected $customer;

    /** @var Order */
    protected $order;

    /** @var Product */
    protected $product;

    /**
     * Reports constructor.
     */
    public function __construct()
    {
        $db = self::getDatabase();

        $this->order    = new Order($db);
        $this->product  = new Product($db);
        $this->customer = new Customer($db);
    }

    /**
     * Get database.
     *
     * @return Database
     */
    public static function getDatabase()
    {
        if ( ! empty(static::$db)) {
            return static::$db;
        }

        return new Database;
    }

    /**
     * Get order entity.
     *
     * @return Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Get product entity.
     *
     * @return Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * Get customer entity.
     *
     * @return Customer
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * Initialize admin.
     *
     * @return void
     */
    public function initAdmin()
    {
        if ( ! class_exists(WooCommerce::class)) {
            return;
        }

        new Dashboard;
        new SalesInsights;
        new UserColumn;
        new ProductColumn;
    }

    /**
     * Check if required plugins are installed and activated.
     *
     * @return void
     */
    public function requirementsCheck()
    {
        $wooCommerce = '<a href="https://wordpress.org/plugins/woocommerce/">WooCommerce</a>';

        error_notice(
            "Woo Sales Report plugin requires ${wooCommerce} plugin to work properly. 
            Please make sure that WooCommerce is installed and activated."
        );
    }
}
