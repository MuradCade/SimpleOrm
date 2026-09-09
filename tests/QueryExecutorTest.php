<?php
require_once __DIR__ . '/DbHelper.php';

use SimpleOrm\tests\DbHelper;
use SimpleOrm\database\QueryExecutor;

class QueryExecutorTest extends DbHelper
{
    private QueryExecutor $executor;

    protected function setUp(): void
    {
        $dbConn = $this->getDb();
        $this->executor = new QueryExecutor($dbConn);
    }

    public function testExecuteValidQuery()
    {
        $stmt = $this->executor->execute('SELECT 1');
        $this->assertInstanceOf(\mysqli_stmt::class, $stmt);
    }

    public function testExecuteInvalidQuery()
    {
        $this->expectException(\RuntimeException::class);
        $this->executor->execute('INVALID SQL');
    }
}
