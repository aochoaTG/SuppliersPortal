<?php

namespace App\Database\Connectors;

use Illuminate\Database\Connectors\SqlServerConnector;
use PDO;

class LegacySqlServerConnector extends SqlServerConnector
{
    /**
     * Avoid PDO attributes that older pdo_sqlsrv builds reject on PHP 8.2.
     *
     * @var array<int, mixed>
     */
    protected $options = [
        PDO::ATTR_CASE => PDO::CASE_NATURAL,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,
    ];
}
