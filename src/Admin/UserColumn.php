<?php

namespace KreeLabs\WSR\Admin;

use function KreeLabs\WSR\translate;

/**
 * Class UserColumn.
 *
 * @package KreeLabs\Admin
 */
class UserColumn
{
    /**
     * UserColumn constructor.
     */
    public function __construct()
    {
        add_action('manage_users_columns', [$this, 'addNewColumns']);
        add_action('manage_users_custom_column', [$this, 'newUserColumnsData'], 10, 3);

        add_filter('user_row_actions', [$this, 'newRowActions'], 10, 2);
    }

    /**
     * Add new user columns.
     *
     * @param array $columnHeaders
     *
     * @return mixed
     */
    public function addNewColumns($columnHeaders)
    {
        $columnHeaders['orders'] = translate('Orders');

        return $columnHeaders;
    }

    /**
     * Get data for new user columns.
     *
     * @param string $value
     * @param string $columnName
     * @param int    $userId
     *
     * @return string
     */
    public function newUserColumnsData($value, $columnName, $userId)
    {
        switch ($columnName) {
            case 'orders':
                $orders = wc_get_customer_order_count($userId);
                $nonce  = wp_create_nonce('bulk-Customers');

                if ($orders <= 0) {
                    return 0;
                }

                return sprintf(
                    '<a href="%s" title="%s">%s</a>',
                    admin_url() . "admin.php?page=woo-sales-report-insights&tab=orders&_wpnonce=${nonce}&_customer=${userId}&filter_action=Filter",
                    translate('View all orders of this user'),
                    $orders
                );
        }

        return $value;
    }

    /**
     * Add link to user activities in row actions.
     *
     * @param array     $actions
     * @param \stdClass $user
     *
     * @return array
     */
    public function newRowActions(array $actions, $user)
    {
        return $actions;
    }
}
