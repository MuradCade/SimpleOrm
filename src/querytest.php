<?php
require_once __DIR__ . '/../vendor/autoload.php';

use SimpleOrm\database\DatabaseConnection;
use SimpleOrm\database\QueryExecutor;
use SimpleOrm\query\Query;

$env = parse_ini_file(__DIR__ . '/../.env');

$dbConn = new DatabaseConnection(
    $env['DB_HOST'],
    $env['DB_USERNAME'],
    $env['DB_PASSWORD'],
    $env['DB_DATABASE']
);
$query = new QueryExecutor($dbConn);

// $stmt = $query->execute('SELECT * FROM products');
// $result = $stmt->get_result();
// $rows = $result->fetch_all(MYSQLI_ASSOC);

// foreach ($rows as $row) {
//     print_r($row);
// }

$stmt = new Query($query);
// $result = $stmt->getAll('products');


// $get = $stmt->getOne('products', [
//     'id' => 1
// ]);

// var_dump($get->id);


// $insert = $stmt->create('products', [
//     'product_name' => 'mobile',
//     'price' => '2',
//     'quantity' => '4'
// ]);







// $update = $stmt->find('products', ['id' => 1])->update();

// $row = $stmt->find('products', ['id' => 2])
// ->update(
//     ['product_name' => 'laptops0001', 'price' => '60', 'quantity' => '50']
// );
// var_dump($row);


// $data = $stmt->getOne('products', ['id' => 2]);

// $delete = $stmt->find('products', ['id' => 4])->delete();
// var_dump($delete);
// if ($row) {
//     $stmt->update('products', ['id' => 1], ['product_name' => 'laptop10', 'price' => '10.5']);
// }
// print_r($result);
// foreach ($result as $row) {
//     var_dump($row);
// }
