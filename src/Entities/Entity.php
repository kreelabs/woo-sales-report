<?php

namespace KreeLabs\WSR\Entities;

use KreeLabs\WSR\Database\Database;
use function KreeLabs\WSR\get_date_with_timezone;

abstract class Entity
{
    /** @var Database */
    protected $db;

    /**
     * Session constructor.
     *
     * @param Database $db
     */
    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Get table.
     *
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Get export date filters.
     *
     * @param string $exportType
     * @param string $field
     *
     * @return string
     */
    public function getExportDateFilterRaw($exportType, $field)
    {
        $filter = '';
        $today  = get_date_with_timezone('now')->format('Y-m-d');

        switch ($exportType) {
            case 'today':
                $filter .= "DATE(${field}) = '" . $today . "'";
                break;

            case 'yesterday':
                $filter .= "DATE(${field}) >= '" . get_date_with_timezone('-1 days')->format('Y-m-d') . "' AND DATE(${field}) < '" . date('Y-m-d') . "'";
                break;

            case 'this-week':
                $filter .= "DATE(${field}) >= '" . get_date_with_timezone('monday this week')->format('Y-m-d') . "' AND DATE(${field}) <= '${today}'";
                break;

            case 'this-month':
                $filter .= "DATE(${field}) >= '" . get_date_with_timezone('first day of this month')->format('Y-m-d') . "' AND DATE(${field}) <= '${today}'";
                break;
        }

        return $filter;
    }
}
