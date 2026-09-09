<?php

namespace SimpleOrm\database;

use mysqli;


class DatabaseConnection
{

    private mysqli $connection;

    public function __construct(
        string $host,
        string $username,
        string $password,
        string $database
    ) {
        mysqli_report(
            MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT
        );

        $this->connection = new mysqli(
            $host,
            $username,
            $password,
            $database
        );
    }

    public function getConnection(): mysqli
    {
        return $this->connection;
    }
}
