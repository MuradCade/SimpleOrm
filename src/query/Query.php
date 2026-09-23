<?php

namespace SimpleOrm\query;

use SimpleOrm\database\QueryExecutor;
use SimpleOrm\validation\Validate;

class Query
{
    private ?string $table = null;
    private ?array $conditions = null;
    private ?array $data = null;

    public function __construct(
        private QueryExecutor $executor
    ) {}

    public function getAll(string $table): array
    {
        Validate::identifier($table);

        $sql = "SELECT * FROM `{$table}`";

        $stmt = $this->executor->execute($sql);

        return $stmt
            ->get_result()
            ->fetch_all(MYSQLI_ASSOC);
    }

    public function selectWhere(string $table, array $conditions): ?array
    {
        $where = [];
        $values = [];
        $types = '';

        foreach ($conditions as $column => $value) {
            $where[] = "`{$column}` = ?";
            $values[] = $value;
            $types .= $this->getParameterType($value);
        }

        $sql = "SELECT * FROM `{$table}` WHERE " . implode(' AND ', $where);

        $stmt = $this->executor->execute(
            $sql,
            $types,
            $values
        );

        return $stmt
            ->get_result()
            ->fetch_all(MYSQLI_ASSOC);
    }

    public function getOne(
        string $table,
        array $conditions
    ): ?array {
        Validate::identifier($table);
        Validate::conditions($conditions);

        $where = [];
        $types = '';
        $params = [];

        foreach ($conditions as $column => $value) {
            $where[] = "`{$column}` = ?";
            $types .= $this->getParameterType($value);
            $params[] = $value;
        }

        $sql = "SELECT * FROM `{$table}` WHERE "
            . implode(' AND ', $where);

        $stmt = $this->executor->execute(
            $sql,
            $types,
            $params
        );

        return $stmt
            ->get_result()
            ->fetch_assoc() ?? null;
    }

    public function create(
        string $table,
        array $data
    ) {
        Validate::identifier($table);
        Validate::data($data);

        $columns = array_keys($data);

        $columnList = implode(
            ', ',
            array_map(
                fn(string $column): string => "`{$column}`",
                $columns
            )
        );

        $placeholders = implode(
            ', ',
            array_fill(0, count($columns), '?')
        );

        $sql = "INSERT INTO `{$table}` ({$columnList})
                VALUES ({$placeholders})";

        $types = '';
        $params = [];

        foreach ($data as $value) {
            $types .= $this->getParameterType($value);
            $params[] = $value;
        }

        return $this->executor->execute(
            $sql,
            $types,
            $params
        );
    }

    public function find(
        string $table,
        array $conditions
    ): self {
        Validate::identifier($table);
        Validate::conditions($conditions);

        $this->table = $table;
        $this->conditions = $conditions;
        $this->data = $this->getOne(
            $table,
            $conditions
        );

        return $this;
    }

    public function __get(string $property): mixed
    {
        return $this->data[$property] ?? null;
    }

    public function update(array $data): bool
    {
        Validate::queryState(
            $this->table,
            $this->conditions
        );

        Validate::data($data);

        $set = [];
        $types = '';
        $params = [];

        foreach ($data as $column => $value) {
            $set[] = "`{$column}` = ?";
            $types .= $this->getParameterType($value);
            $params[] = $value;
        }

        $where = [];
        $whereTypes = '';
        $whereParams = [];

        foreach ($this->conditions as $column => $value) {
            $where[] = "`{$column}` = ?";
            $whereTypes .= $this->getParameterType($value);
            $whereParams[] = $value;
        }

        $sql = "UPDATE `{$this->table}`
                SET " . implode(', ', $set) . "
                WHERE " . implode(' AND ', $where);

        $stmt = $this->executor->execute(
            $sql,
            $types . $whereTypes,
            array_merge($params, $whereParams)
        );

        return $stmt->affected_rows > 0;
    }

    public function delete(): bool
    {
        Validate::queryState(
            $this->table,
            $this->conditions
        );

        $where = [];
        $types = '';
        $params = [];

        foreach ($this->conditions as $column => $value) {
            $where[] = "`{$column}` = ?";
            $types .= $this->getParameterType($value);
            $params[] = $value;
        }

        $sql = "DELETE FROM `{$this->table}`
                WHERE " . implode(' AND ', $where);

        $stmt = $this->executor->execute(
            $sql,
            $types,
            $params
        );

        return $stmt->affected_rows > 0;
    }

    private function getParameterType(mixed $value): string
    {
        return match (true) {
            is_int($value) => 'i',
            is_float($value) => 'd',
            default => 's',
        };
    }
}
