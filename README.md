# SimpleOrm

A lightweight, maintainable PHP ORM for basic CRUD operations using MySQLi.

## Installation

Install via Composer:
```bash
composer require muradcade/simple-orm
```

## Setup

1. Create a `.env` file in your project root:
    ```
    DB_HOST=localhost
    DB_USERNAME=root
    DB_PASSWORD=
    DB_DATABASE=your_database
    ```
2. Ensure your database and tables exist.

## Usage

```php
use SimpleOrm\database\DatabaseConnection;
use SimpleOrm\database\QueryExecutor;
use SimpleOrm\query\Query;

$env = parse_ini_file(__DIR__ . '/.env');
$dbConn = new DatabaseConnection(
    $env['DB_HOST'],
    $env['DB_USERNAME'],
    $env['DB_PASSWORD'],
    $env['DB_DATABASE']
);
$executor = new QueryExecutor($dbConn);
$query = new Query($executor);

// Create
$query->create('products', [
    'product_name' => 'Laptop',
    'price' => 1000,
    'quantity' => 5
]);

// Get all
$products = $query->getAll('products');

// Get one
$product = $query->getOne('products', ['id' => 1]);

// Update
$query->find('products', ['id' => 1])->update( ['price' => 1200]);

// Delete
$query->find('products', ['id' => 1])->delete();
```

## Folder Structure
- `src/database/DatabaseConnection.php` — Handles DB connection
- `src/database/QueryExecutor.php` — Executes prepared statements
- `src/query/Query.php` — Main ORM logic (CRUD)
- `src/querytest.php` — Example usage

## License
MIT
