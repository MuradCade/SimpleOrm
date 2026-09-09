<?php


namespace SimpleOrm\database;

use SimpleOrm\database\DatabaseConnection;
use mysqli_stmt;

class QueryExecutor
{
    public function __construct(
        private DatabaseConnection $connection
    ) {}

    public function execute(
        string $sql,
        string $types = '',
        array $params = []
    ): mysqli_stmt {
        $mysqli = $this->connection->getConnection();

        $stmt = $mysqli->prepare($sql);
        if ($stmt === false) {
            throw new \RuntimeException($mysqli->error);
        }

        if ($params !== []) {
            $bindParams = [$types];

            foreach ($params as $key => &$value) {
                $bindParams[] = &$value;
            }

            call_user_func_array(
                [$stmt, 'bind_param'],
                $bindParams
            );
        }

        $stmt->execute();

        return $stmt;
    }
}
