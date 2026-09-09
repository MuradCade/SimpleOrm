<?php
require_once __DIR__ . '/DbHelper.php';

use SimpleOrm\database\QueryExecutor;
use SimpleOrm\query\Query;
use SimpleOrm\tests\DbHelper;

class QueryTest extends DbHelper
{
    private Query $query;

    protected function setUp(): void
    {
        $dbConn = $this->getDb();
        $executor = new QueryExecutor($dbConn);
        $this->query = new Query($executor);
    }

    public function testCreateAndFind()
    {
        $this->query->create('products', [
            'product_name' => 'test_product',
            'price' => '99',
            'quantity' => '10'
        ]);

        $row = $this->query->getOne('products', ['product_name' => 'test_product']);
        $this->assertIsArray($row);
        $this->assertEquals('test_product', $row['product_name']);
    }

    public function testUpdate()
    {
        $data = $this->query->find(
            'products',
            ['product_name' => 'test_product']
        );
        $this->query->find('products', ['id' => $data->id])->update(['price' => '94']);
        $row = $this->query->getOne('products', ['price' => '94']);
        $this->assertEquals('94', $row['price']);
    }


    public function testDeletion()
    {
        $data = $this->query->find(
            'products',
            ['product_name' => 'test_product']
        );

        $this->assertNotNull($data);

        $deleted = $data->delete();

        $this->assertTrue($deleted);


        $row = $this->query->getOne(
            'products',
            ['id' => $data->id]
        );

        $this->assertNull($row);
    }
    // Add delete test if you implement delete method
}
