<?php

namespace KreeLabs\WSR\Database;

use Closure;

/**
 * A simple query builder.
 *
 * @package KreeLabs\Database
 */
class QueryBuilder
{
    /** @var wpdb */
    public $wpdb;

    /** @var string */
    protected $table;

    /** @var array */
    protected $select = ['*'];

    /** @var array */
    protected $wheres = [];

    /** @var array */
    protected $groups = [];

    /** @var array */
    protected $havings = [];

    /** @var array */
    protected $orders = [];

    /** @var array */
    protected $bindings = [];

    /** @var int */
    protected $limit = -1;

    /** @var int */
    protected $offset = -1;

    /**
     * QueryBuilder constructor.
     *
     * @global $wpdb
     */
    public function __construct()
    {
        global $wpdb;

        $this->wpdb = $wpdb;
    }

    /**
     * Set table.
     *
     * @param string $table
     * @param bool   $prefix
     *
     * @return $this
     */
    public function table($table, $prefix = true)
    {
        if ($prefix) {
            $table = $this->wpdb->prefix . $table;
        }

        $this->table = $table;

        return $this;
    }

    /**
     * Columns to be selected.
     *
     * @param array $columns
     *
     * @return $this
     */
    public function select($columns = ['*'])
    {
        $this->select = $columns;

        return $this;
    }

    /**
     * Constitute where clause. We can use it in many different ways:
     *
     * // Single where clause.
     * $this->where('created_at', '>', 5);
     *
     * // Multiple where clause.
     * $this->where(['created_at', '>', '5'], ['post_type', '=', 'product']);
     *
     * // Grouped where clause.
     * $this->where(function ($query) {
     *     $query->where([
     *         ['created_at', '>', '5', 'or'],
     *         ['is_mobile', '=', '1']
     *     ]);
     * });
     *
     * @param array|string $column
     * @param null|string  $operator
     * @param null|string  $value
     * @param string       $boolean
     *
     * @return $this|QueryBuilder
     */
    public function where($column, $operator = null, $value = null, $boolean = 'and')
    {
        if (is_array($column)) {
            return $this->addArrayOfWheres($column, $boolean);
        }

        if ($column instanceof Closure) {
            $query = new QueryBuilder;

            call_user_func($column, $query);

            $query->addArrayOfWheres($column, $boolean);

            // Wrap.
            $this->wheres[] = function () use ($query) {
                return $query->wheres;
            };
        } else {
            $this->wheres[] = compact('column', 'operator', 'value', 'boolean');
        }

        return $this;
    }

    /**
     * Add an array of where clauses to the query.
     *
     * @param  array  $column
     * @param  string $boolean
     *
     * @return $this
     */
    protected function addArrayOfWheres($column, $boolean)
    {
        foreach ($column as $key => $value) {
            if (is_numeric($key) && is_array($value)) {
                $this->where(...array_values($value));
            } else {
                $this->where($key, '=', $value, $boolean);
            }
        }

        return $this;
    }

    /**
     * Compile select query.
     *
     * @return string
     */
    public function compileSelect()
    {
        // Clear bindings.
        $this->clearBindings();

        $pieces = [
            'SELECT',
            implode(',', $this->select),
            'FROM',
            $this->table,
        ];

        if ( ! empty($this->wheres)) {
            $pieces[] = 'WHERE';

            $where = [];
            foreach ($this->wheres as $value) {
                if (is_a($value, Closure::class)) {
                    $sql  = implode(' ', $this->parseNestedWhere($value()));
                    $glue = $this->getLeadingGlue($sql);

                    $where[] = '(' . $this->removeLeadingGlue($sql) . ") $glue";
                } else {
                    $where[] = implode(' ', $this->parseWhere($value));
                }
            }

            $pieces[] = $this->removeLeadingGlue(implode(' ', $where));
        }

        if ( ! empty($this->groups)) {
            $pieces[] = 'GROUP BY';
            $pieces[] = implode(', ', $this->groups);
        }

        if ( ! empty($this->havings)) {
            $pieces[] = 'HAVING';

            $having = [];
            foreach ($this->havings as $value) {
                $having[] = implode(' ', $this->parseWhere($value));
            }

            $pieces[] = $this->removeLeadingGlue(implode(' ', $having));
        }

        if ( ! empty($this->orders)) {
            $pieces[] = 'ORDER BY';

            $orderBy = [];
            foreach ($this->orders as $order) {
                $orderBy[] = "${order['column']} ${order['direction']}";
            }

            $pieces[] = implode(', ', $orderBy);
        }

        if ($this->limit >= 0) {
            $pieces[] = 'LIMIT';
            $pieces[] = $this->limit;
        }

        if ($this->offset >= 0) {
            $pieces[] = 'OFFSET';
            $pieces[] = $this->offset;
        }

        return implode(' ', $pieces);
    }

    /**
     * Parse where clause.
     *
     * @param $condition
     *
     * @return array
     */
    protected function parseWhere($condition)
    {
        $this->bindings[] = $condition['value'];

        // For our use case as of now, we are good with treating
        // all values as string. Later we should formats based
        // on the data type of the value.
        $condition['value'] = "%s";

        return $condition;
    }

    /**
     * Parse nested where condition.
     *
     * @param $value
     *
     * @return array
     */
    protected function parseNestedWhere($value)
    {
        $where = [];

        foreach ($value as $key => $val) {
            $where[] = implode(' ', $this->parseWhere($val));
        }

        return $where;
    }

    /**
     * Group by clause.
     *
     * @param array $columns
     *
     * @return $this
     */
    public function groupBy(...$columns)
    {
        $this->groups = $columns;

        return $this;
    }

    /**
     * Having clause.
     *
     * @param  string      $column
     * @param  string|null $operator
     * @param  string|null $value
     * @param  string      $boolean
     *
     * @return $this
     */
    public function having($column, $operator = null, $value = null, $boolean = 'and')
    {
        $this->havings[] = compact('column', 'operator', 'value', 'boolean');

        return $this;
    }

    /**
     * Order by clause.
     *
     * @param string $column
     * @param string $direction
     *
     * @return $this
     */
    public function orderBy($column, $direction = 'asc')
    {
        $direction = strtolower($direction);

        if ( ! in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'asc';
        }

        $this->orders[] = [
            'column' => $column,
            'direction' => $direction,
        ];

        return $this;
    }

    /**
     * Limit the query.
     *
     * @param  int $value
     *
     * @return $this
     */
    public function limit($value)
    {
        if ($value >= 0) {
            $this->limit = $value;
        }

        return $this;
    }

    /**
     * Add offset the query.
     *
     * @param  int $value
     *
     * @return $this
     */
    public function offset($value)
    {
        if ($value >= 0) {
            $this->offset = $value;
        }

        return $this;
    }

    /**
     * Execute the query as a "select" statement.
     *
     * @param  array|string $columns
     *
     * @return array
     */
    public function get($columns = null)
    {
        if ( ! empty($columns)) {
            $this->select($columns);
        }

        return [
            'query' => $this->compileSelect(),
            'bindings' => $this->bindings,
        ];
    }

    /**
     * Get the leading glue from a statement.
     *
     * @param  string $value
     *
     * @return string
     */
    protected function getLeadingGlue($value)
    {
        $value  = rtrim($value);
        $pieces = explode(' ', $value);

        return end($pieces);
    }

    /**
     * Remove the leading glue from a statement.
     *
     * @param  string $value
     *
     * @return string
     */
    protected function removeLeadingGlue($value)
    {
        return rtrim($value, 'and|or ');
    }

    /**
     * Clear all bindings.
     *
     * @return void
     */
    protected function clearBindings()
    {
        $this->bindings = [];
    }
}
