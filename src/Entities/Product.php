<?php

namespace KreeLabs\WSR\Entities;

use KreeLabs\WSR\Database\Database;

class Product extends Entity
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
}
