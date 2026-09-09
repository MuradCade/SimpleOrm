<?php


require_once __DIR__ . '/DbHelper.php';

use SimpleOrm\tests\DbHelper;
use SimpleOrm\database\DatabaseConnection;


class ConnectionTest extends DbHelper
{
    public function testConnectionSuccess()
    {
        $db = $this->getDb();
        $this->assertInstanceOf(DatabaseConnection::class, $db);
    }

    public function testConnectionFailure()
    {
        $this->expectException(\mysqli_sql_exception::class);
        new DatabaseConnection('invalid_host', 'bad_user', 'bad_pass', 'bad_db');
    }
}
