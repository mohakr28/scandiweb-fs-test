<?php

namespace App\Models;

use App\Config\Database;
use PDO;

abstract class AbstractModel
{
    protected PDO $db;
    protected string $table;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }
}