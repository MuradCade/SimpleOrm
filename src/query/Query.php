<?php

namespace SimpleOrm\query;

use SimpleOrm\database\QueryExecutor;


class Query
{
    private string $table; // is used to store the table name when chaining class (find->update to update and delete)
    private array $conditions; // used to store the condition
    private ?array $data = null;
    public function __construct(
        private QueryExecutor $executor
    ) {}

    public function getAll(string $table): array
    {
        $sql = "SELECT * FROM {$table}";

        $stmt = $this->executor->execute($sql);

        $result = $stmt->get_result();

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getOne(string $table, array $conditions)
    {
        $where = [];
        $attribute_type = '';
        $params = [];
        foreach ($conditions as $column => $value) {
            $where[] = "$column = ?";
            if (is_int($value)) {
                $attribute_type .= 'i';
            } elseif (is_float($value)) {
                $attribute_type .= 'd';
            } else {
                $attribute_type .= 's';
            }
            $params[] = $value;
        }

        $sql = "SELECT * FROM {$table} WHERE " . implode(' AND ', $where);
        $stmt = $this->executor->execute($sql, $attribute_type, $params);
        $result = $stmt->get_result();

        return  $result->fetch_assoc() ?? null;
    }
    // public function getOne(string $table, array $conditions)
    // {
    //     $attribute = []; // holds the column name we need to fetch specific data from
    //     $attribute_type = ''; // the acutal value that attrube contains
    //     $params = []; // what kind of value we are loking for (integer (i) , string (s) , decimal (d))
    //     foreach ($conditions as $column => $value) {
    //         $attribute['column'] = "$column = ?";
    //         if (is_int($value)) {
    //             $attribute_type .= 'i';
    //         } elseif (is_float($value)) {
    //             $attribute_type .= 'd';
    //         } else {
    //             $attribute_type .= 's';
    //         }
    //         $params[] = $value;
    //     }
    //     $sql = "SELECT * FROM {$table} where {$attribute['column']}";
    //     $stmt = $this->executor->execute($sql, $attribute_type, $params);
    //     $result = $stmt->get_result();
    //     return $result->fetch_assoc() ?? null;
    // }

    public function create(string $table, array $data)
    {
        // if (empty($data)) {
        //     throw new InvalidArgumentException("create method can't be empty");
        // }

        $columns = array_keys($data);

        $columnList = implode(
            ', ',
            array_map(
                fn(string $column): string => "`{$column}`",
                $columns
            )
        );
        $placeholders = implode(', ', array_fill(0, count($columns), '?'));
        $sql = "insert into $table($columnList) values($placeholders)";

        $types = '';
        $params = [];
        foreach ($data as $value) {
            if (is_int($value)) {
                $types .= 'i';
            } elseif (is_float($value)) {
                $types .= 'd';
            } else {
                $types .= 's';
            }
            $params[] = $value;
        }

        $stmt = $this->executor->execute($sql, $types, $params);
        return $stmt;
    }

    public function find(string $table, array $conditions)
    {
        $data = $this->getOne($table, $conditions); // returns array|null
        $this->table = $table;
        $this->conditions = $conditions;
        $this->data = $data;
        return $this;
    }
    // this __get helps us to get data object from find method so we can easily access specific attribute
    public function __get(string $property)
    {
        return $this->data[$property] ?? null;
    }

    public function update(array $data)
    {
        // $row = $this->find($this->table, $this->conditions);
        // if (!$row) {
        //     return null;
        // }
        // Build update SQL
        $set = [];
        $types = '';
        $params = [];
        foreach ($data as $col => $val) {
            $set[] = "$col = ?";
            $types .= is_int($val) ? 'i' : (is_float($val) ? 'd' : 's');
            $params[] = $val;
        }
        $where = [];
        $whereTypes = '';
        $whereParams = [];
        foreach ($this->conditions as $col => $val) {
            $where[] = "$col = ?";
            $whereTypes .= is_int($val) ? 'i' : (is_float($val) ? 'd' : 's');
            $whereParams[] = $val;
        }
        $sql = "UPDATE {$this->table} SET " . implode(', ', $set) . " WHERE " . implode(' AND ', $where);
        // var_dump($sql);
        // die();
        $stmt = $this->executor->execute($sql, $types . $whereTypes, array_merge($params, $whereParams));

        return $stmt->affected_rows > 0;
    }


    public function delete()
    {
        $where = [];
        $types = '';
        $params = [];

        foreach ($this->conditions as $column => $value) {
            $where[] = "$column = ?";

            $types .= is_int($value)
                ? 'i'
                : (is_float($value) ? 'd' : 's');

            $params[] = $value;
        }

        $sql = "DELETE FROM {$this->table} WHERE " . implode(' AND ', $where);
        // var_dump($sql);
        // die();

        $stmt = $this->executor->execute(
            $sql,
            $types,
            $params
        );
        return $stmt->affected_rows > 0;
    }
}
