<?php

namespace KreeLabs\WSR\Admin\Tables\Sales;

use WP_List_Table;

/**
 * Class AbstractMetaList.
 *
 * @package KreeLabs\Admin\Tables\Sales
 */
abstract class AbstractMetaList extends WP_List_Table
{
    /**
     * AbstractMetaList constructor.
     *
     * @param array $args
     */
    public function __construct(array $args = [])
    {
        parent::__construct($args);
    }

    /**
     * {@inheritdoc}
     */
    public function get_views()
    {
        $url = '?page=woo-sales-report-insights';

        $current = '';
        if (isset($_GET['show'])) {
            $current = sanitize_key($_GET['show']);
        }

        return [
            "buyers" => "<a href='${url}' class='" . ($current === '' ? 'current' : '') . "'>" . translate('All Buyers') . '</a>',
            "registered_users" => "<a href='${url}&show=registered_users' class='" . ($current === 'registered_users' ? 'current' : '') . "'>" . translate('Registered') . "</a>",
            "guest_users" => "<a href='${url}&show=guest_users' class='" . ($current === 'guest_users' ? 'current' : '') . "'>" . translate('Guests') . '</a>',
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function column_cb($item)
    {
        return '<input type="checkbox" name="customers[]" value="" disabled />';
    }

    /**
     * {@inheritdoc}
     */
    public function get_bulk_actions()
    {

    }

    /**
     * {@inheritdoc}
     */
    public function prepare_items()
    {
        $this->_column_headers = [
            $this->get_columns(),
            [],
            $this->get_sortable_columns(),
        ];
    }
}
