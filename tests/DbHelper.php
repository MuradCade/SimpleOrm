<?php

namespace SimpleOrm\tests;

use PHPUnit\Framework\TestCase;
use SimpleOrm\database\DatabaseConnection;

class DbHelper extends TestCase
{
    protected static ?DatabaseConnection $db = null;

    public static function setUpBeforeClass(): void
    {
        $env = parse_ini_file(__DIR__ . '/../.env');
        self::$db = new DatabaseConnection(
            $env['DB_HOST'],
            $env['DB_USERNAME'],
            $env['DB_PASSWORD'],
            $env['DB_DATABASE']
        );
    }

    protected function getDb()
    {
        return self::$db;
    }
}
