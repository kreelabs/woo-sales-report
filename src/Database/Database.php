<?php

namespace KreeLabs\WSR\Database;

/**
 * Simple database abstraction.
 *
 * @package KreeLabs\Database
 */
class Database
{
    /** @var wpdb */
    public $wpdb;

    /** @var string */
    protected $tablePrefix;

    /**
     * Database constructor.
     */
    public function __construct()
    {
        global $wpdb;

        $this->wpdb        = $wpdb;
        $this->tablePrefix = $wpdb->prefix;
    }

    /**
     * Get new query builder.
     *
     * @return QueryBuilder
     */
    public function newQuery()
    {
        return new QueryBuilder;
    }

    /**
     * Execute raw query.
     *
     * @param string $query
     * @param string $output
     *
     * @return array|null|object
     */
    public function raw($query, $output = ARRAY_A)
    {
        return $this->wpdb->get_results($query, $output);
    }

    /**
     * Execute raw query and get a row.
     *
     * @param string $query
     * @param string $output
     *
     * @return array|null|object
     */
    public function rawRow($query, $output = ARRAY_A)
    {
        return $this->wpdb->get_row($query, $output);
    }

    /**
     * Get row from a table for given conditions.
     *
     * @param QueryBuilder $query
     * @param string       $output
     *
     * @return array|null|object
     */
    public function getRow(QueryBuilder $query, $output = ARRAY_A)
    {
        $data = $query->get();

        if (empty($data['bindings'])) {
            return $this->wpdb->get_row($data['query'], $output);
        }

        return $this->wpdb->get_row($this->wpdb->prepare($data['query'], $data['bindings']), $output);
    }

    /**
     * Get rows from a table for given conditions.
     *
     * @param QueryBuilder $query
     * @param string       $output
     *
     * @return array|null|object
     */
    public function getResults(QueryBuilder $query, $output = ARRAY_A)
    {
        $data = $query->get();

        if (empty($data['bindings'])) {
            return $this->wpdb->get_results($data['query'], $output);
        }

        return $this->wpdb->get_results($this->wpdb->prepare($data['query'], $data['bindings']), $output);
    }

    /**
     * Get count from a table for given conditions.
     *
     * @param QueryBuilder $query
     *
     * @return int
     */
    public function getCount($query)
    {
        $count = $this->getRow($query);

        return ! empty($count['total']) ? intval($count['total']) : 0;
    }

    /**
     * Get count from a table for given conditions.
     *
     * @param string $query
     * @param string $output
     *
     * @return int
     */
    public function rawCount($query, $output = ARRAY_A)
    {
        $count = $this->wpdb->get_row($query, $output);

        return ! empty($count['total']) ? intval($count['total']) : 0;
    }

    /**
     * Get latest row from a table for given conditions.
     *
     * @param string $table
     * @param array  $conditions
     * @param string $output
     *
     * @return array|null|object
     */
    public function getLatest($table, array $conditions, $output = ARRAY_A)
    {
        $query = $this
            ->newQuery()
            ->table($table)
            ->where($conditions)
            ->orderBy('created_at', 'desc');

        return $this->getRow($query, $output);
    }

    /**
     * Get first row from a table for given conditions.
     *
     * @param string $table
     * @param array  $conditions
     * @param string $output
     *
     * @return array|null|object
     */
    public function getFirst($table, array $conditions, $output = ARRAY_A)
    {
        $query = $this
            ->newQuery()
            ->table($table)
            ->where($conditions)
            ->orderBy('created_at', 'asc');

        return $this->getRow($query, $output);
    }

    /**
     * Save row in a table.
     *
     * @param string $table
     * @param array  $data
     *
     * @return bool|int
     */
    public function save($table, array $data)
    {
        $table = $this->tablePrefix . $table;

        if ($this->wpdb->insert($table, $data)) {
            return $this->wpdb->insert_id;
        }

        return false;
    }

    /**
     * Update a row.
     *
     * @param string $table
     * @param array  $data
     * @param array  $where
     *
     * @return false|int
     */
    public function update($table, array $data, array $where)
    {
        $table = $this->tablePrefix . $table;

        return $this->wpdb->update($table, $data, $where);
    }

    /**
     * Delete a row.
     *
     * @param string $table
     * @param array  $where
     *
     * @return bool|int
     */
    public function delete($table, array $where)
    {
        $table = $this->tablePrefix . $table;

        return $this->wpdb->delete($table, $where);
    }
}
