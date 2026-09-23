# SimpleOrm

A lightweight, framework-agnostic PHP ORM for MySQLi.

SimpleOrm provides a small and explicit API for working with MySQL databases without requiring a full framework. It includes CRUD operations, fluent update/delete queries, model hydration, validation, and basic `hasOne` / `hasMany` relationships.

## Features

- MySQLi database connection
- Prepared statements for query values
- Create, read, update, and delete operations
- Fluent `find()->update()` and `find()->delete()` queries
- Model-based database access
- Model hydration
- `where()` and `getWhere()` helpers
- `hasOne` relationships
- `hasMany` relationships
- SQL identifier and query-state validation
- PSR-4 autoloading
- Framework agnostic
- PHPUnit test suite

## Requirements

- PHP 8.0 or higher
- MySQL or a compatible MySQL database
- PHP `mysqli` extension
- Composer

## Installation

Install SimpleOrm with Composer:

```bash
composer require muradcade/simple-orm
```

Composer will register the `SimpleOrm\\` namespace automatically.

## Database Configuration

SimpleOrm does not require a specific configuration system. You can use environment variables, a `.env` file, or your application's existing configuration.

For example, with a `.env` file:

```env
DB_HOST=localhost
DB_USERNAME=root
DB_PASSWORD=
DB_DATABASE=your_database
```

Then create the database connection:

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use SimpleOrm\database\DatabaseConnection;
use SimpleOrm\database\QueryExecutor;
use SimpleOrm\query\Query;

$env = parse_ini_file(__DIR__ . '/.env');

$connection = new DatabaseConnection(
    $env['DB_HOST'],
    $env['DB_USERNAME'],
    $env['DB_PASSWORD'],
    $env['DB_DATABASE']
);

$executor = new QueryExecutor($connection);
$query = new Query($executor);
```

> `parse_ini_file()` is only used in this example. SimpleOrm itself does not require a `.env` package.

---

# Query API

The `Query` class provides the lower-level CRUD API.

```php
use SimpleOrm\query\Query;
```

## Get All Records

```php
$products = $query->getAll('products');
```

The result is an array of associative arrays:

```php
[
    [
        'id' => 1,
        'product_name' => 'Laptop',
        'price' => 1000,
        'quantity' => 5,
    ],
    // ...
]
```

## Get One Record

Use `getOne()` when you want a single associative array or `null` when no record matches.

```php
$product = $query->getOne('products', [
    'id' => 1,
]);

if ($product !== null) {
    echo $product['product_name'];
}
```

Multiple conditions are combined with `AND`:

```php
$product = $query->getOne('products', [
    'product_name' => 'Laptop',
    'quantity' => 5,
]);
```

## Select Multiple Records With Conditions

Use `selectWhere()` when you need all matching rows:

```php
$products = $query->selectWhere('products', [
    'status' => 'active',
]);
```

The result is an array of associative arrays.

## Create

Insert a new record with `create()`:

```php
$query->create('products', [
    'product_name' => 'Laptop',
    'price' => 1000,
    'quantity' => 5,
]);
```

Values are passed to a prepared statement rather than concatenated directly into the SQL query.

## Update

SimpleOrm supports fluent updates.

First select the record with `find()` and then call `update()`:

```php
$query
    ->find('products', ['id' => 1])
    ->update([
        'price' => 1200,
        'quantity' => 10,
    ]);
```

`update()` returns `true` when the database reports affected rows and `false` otherwise.

The `find()` conditions are used as the `WHERE` clause:

```php
$query
    ->find('products', ['id' => 1])
    ->update([
        'price' => 1200,
    ]);
```

## Delete

Delete a record using the same fluent API:

```php
$deleted = $query
    ->find('products', ['id' => 1])
    ->delete();

if ($deleted) {
    echo 'Record deleted.';
}
```

The conditions supplied to `find()` determine which record is deleted.

## Access the Selected Record

`find()` returns the same `Query` instance, allowing fluent operations.

The selected row's attributes can also be accessed through the query object's magic property access:

```php
$product = $query->find('products', ['id' => 1]);

echo $product->product_name;
echo $product->price;
```

This makes it possible to select a record once and then use it for an update or delete:

```php
$product = $query->find('products', ['id' => 1]);

$product->update([
    'price' => 1500,
]);

$product->delete();
```

---

# Models

For a more object-oriented API, SimpleOrm provides an abstract `Model` class.

Create a model by extending `SimpleOrm\model\Model` and defining its table:

```php
<?php

use SimpleOrm\model\Model;

class User extends Model
{
    protected string $table = 'users';
}
```

The model receives the `Query` instance through its constructor:

```php
$userModel = new User($query);
```

## Find a Model by ID

```php
$user = $userModel->find(1);

if ($user !== null) {
    echo $user->email;
}
```

`find()` returns a hydrated model instance or `null`.

## Find the First Matching Model

Use `where()` to retrieve the first matching record:

```php
$user = $userModel->where([
    'email' => 'user@example.com',
]);

if ($user !== null) {
    echo $user->name;
}
```

## Get Multiple Matching Models

Use `getWhere()` when multiple records may match:

```php
$users = $userModel->getWhere([
    'status' => 'active',
]);

foreach ($users as $user) {
    echo $user->name;
}
```

## Get All Models

```php
$users = $userModel->all();

foreach ($users as $user) {
    echo $user->name;
}
```

## Model Attributes

Database columns are exposed through magic property access:

```php
$user = $userModel->find(1);

echo $user->id;
echo $user->name;
echo $user->email;
```

Models are hydrated from database rows automatically.

---

# Relationships

The relationships API currently supports:

- `hasOne()`
- `hasMany()`

Relationships are defined inside your model classes.

## Has One

Suppose you have:

```text
users
  id

profiles
  id
  user_id
```

Create the models:

```php
<?php

use SimpleOrm\model\Model;

class User extends Model
{
    protected string $table = 'users';

    public function profile(): \SimpleOrm\relations\HasOne
    {
        return $this->hasOne(
            Profile::class,
            'user_id'
        );
    }
}
```

```php
<?php

use SimpleOrm\model\Model;

class Profile extends Model
{
    protected string $table = 'profiles';
}
```

Then load the user and its profile:

```php
$user = $userModel->find(1);

$profile = $user?->profile()->get();

if ($profile !== null) {
    echo $profile->user_id;
}
```

### Custom Local Key

The third argument can be used when the local key is not `id`:

```php
return $this->hasOne(
    Profile::class,
    'user_id',
    'id'
);
```

The default local key is `id`.

## Has Many

Suppose one user can have many posts:

```text
users
  id

posts
  id
  user_id
```

Define the relationship:

```php
<?php

use SimpleOrm\model\Model;
use SimpleOrm\relations\HasMany;

class User extends Model
{
    protected string $table = 'users';

    public function posts(): HasMany
    {
        return $this->hasMany(
            Post::class,
            'user_id'
        );
    }
}
```

Then:

```php
$user = $userModel->find(1);

$posts = $user?->posts()->get() ?? [];

foreach ($posts as $post) {
    echo $post->title;
}
```

`hasMany()->get()` returns an array of related model instances. If the parent model has no local key value, it returns an empty array.

### Custom Local Key

```php
return $this->hasMany(
    Post::class,
    'user_id',
    'id'
);
```

The default local key is `id`.

---

# Validation

SimpleOrm includes a dedicated `Validate` class for input used by the query layer.

It validates:

- SQL identifiers such as table and column names
- Empty condition arrays
- Empty insert/update data
- Conditions used by `WHERE`
- Query state before `update()` and `delete()`

For example, `find()` requires valid conditions:

```php
$query->find('products', [
    'id' => 1,
]);
```

Calling `update()` or `delete()` before `find()` results in an exception:

```php
$query->update([
    'price' => 100,
]);
```

The query must first be initialized:

```php
$query
    ->find('products', ['id' => 1])
    ->update([
        'price' => 100,
    ]);
```

Invalid SQL identifiers are rejected before being placed into SQL statements.

---

# Prepared Statements

Values supplied to CRUD operations are sent through MySQLi prepared statements.

For example:

```php
$query->getOne('users', [
    'email' => 'user@example.com',
]);
```

The value is bound as a query parameter rather than directly concatenated into the SQL statement.

SimpleOrm also determines the MySQLi parameter type for integer and floating-point values.

> Table and column names are validated as SQL identifiers because SQL identifiers cannot be bound as normal prepared-statement parameters.

---

# Project Structure

The package is organized into small components:

```text
src/
├── database/
│   ├── DatabaseConnection.php
│   └── QueryExecutor.php
├── model/
│   └── Model.php
├── query/
│   └── Query.php
├── relations/
│   ├── HasOne.php
│   └── HasMany.php
└── validation/
    └── validate.php
```

### `DatabaseConnection`

Creates and manages the MySQLi connection.

### `QueryExecutor`

Prepares and executes SQL statements and handles parameter binding.

### `Query`

Provides the lower-level CRUD and fluent query API.

### `Model`

Provides model hydration, record retrieval, and relationship definitions.

### `HasOne`

Handles one-to-one style relationships.

### `HasMany`

Handles one-to-many style relationships.

### `Validate`

Validates identifiers, conditions, data, and query state before SQL execution.

---

# Complete Example

A minimal application can look like this:

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use SimpleOrm\database\DatabaseConnection;
use SimpleOrm\database\QueryExecutor;
use SimpleOrm\model\Model;
use SimpleOrm\query\Query;
use SimpleOrm\relations\HasMany;

$env = parse_ini_file(__DIR__ . '/.env');

$connection = new DatabaseConnection(
    $env['DB_HOST'],
    $env['DB_USERNAME'],
    $env['DB_PASSWORD'],
    $env['DB_DATABASE']
);

$executor = new QueryExecutor($connection);
$query = new Query($executor);

class User extends Model
{
    protected string $table = 'users';

    public function posts(): HasMany
    {
        return $this->hasMany(
            Post::class,
            'user_id'
        );
    }
}

class Post extends Model
{
    protected string $table = 'posts';
}

$users = (new User($query))->getWhere([
    'status' => 'active',
]);

foreach ($users as $user) {
    echo $user->name;

    $posts = $user->posts()->get();

    foreach ($posts as $post) {
        echo $post->title;
    }
}
```

---

# Testing

The package uses PHPUnit.

Install development dependencies:

```bash
composer install
```

Run the test suite:

```bash
vendor/bin/phpunit
```

The test suite covers database connections, query execution, CRUD operations, and query behavior.

> The database tests require a working test database and the database configuration expected by the test environment.

---

# Design Goals

SimpleOrm is intentionally small.

It focuses on providing a straightforward database abstraction without trying to replace a complete framework.

The main goals are:

- Keep the API easy to understand.
- Keep database operations explicit.
- Use prepared statements for query values.
- Separate connection, execution, querying, validation, models, and relationships.
- Provide useful model relationships without requiring a full framework.

---

# Roadmap

Potential future improvements include:

- More relationship types
- Eager loading
- Relationship constraints
- More advanced query conditions
- Ordering and pagination
- Insert/update model methods
- Mass-assignment support
- Transactions
- Improved schema/query helpers
- More comprehensive integration tests

---

# Contributing

Contributions, bug reports, and feature suggestions are welcome.

1. Fork the repository.
2. Create a feature branch.
3. Make your changes.
4. Add or update tests where appropriate.
5. Run the test suite.
6. Open a pull request.

Repository:

https://github.com/MuradCade/SimpleOrm

---

# License

SimpleOrm is open-sourced software licensed under the [MIT License](LICENSE).

---

## Author

**MuradCade**

Package:

`muradcade/simple-orm`

Packagist:

https://packagist.org/packages/muradcade/simple-orm
