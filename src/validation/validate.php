<?php

namespace SimpleOrm\validation;

use InvalidArgumentException;

final class Validate
{
    /**
     * Validate a SQL identifier such as a table or column name.
     */
    public static function identifier(string $identifier): void
    {
        if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $identifier)) {
            throw new InvalidArgumentException(
                "Invalid SQL identifier: {$identifier}"
            );
        }
    }

    /**
     * Validate that an array is not empty.
     */
    public static function notEmpty(
        array $value,
        string $argument
    ): void {
        if ($value === []) {
            throw new InvalidArgumentException(
                "{$argument} cannot be empty."
            );
        }
    }

    /**
     * Validate conditions used in WHERE clauses.
     *
     * Example:
     * [
     *     'id' => 1,
     *     'status' => 'active'
     * ]
     */
    public static function conditions(array $conditions): void
    {
        self::notEmpty($conditions, 'Conditions');

        foreach ($conditions as $column => $value) {
            self::identifier((string) $column);
        }
    }

    /**
     * Validate data used for INSERT/UPDATE.
     *
     * Example:
     * [
     *     'name' => 'Laptop',
     *     'price' => 1000
     * ]
     */
    public static function data(array $data): void
    {
        self::notEmpty($data, 'Data');

        foreach ($data as $column => $value) {
            self::identifier((string) $column);
        }
    }

    /**
     * Validate that a query has been initialized
     * with find() before update/delete.
     */
    public static function queryState(
        ?string $table,
        ?array $conditions
    ): void {
        if ($table === null || $conditions === null) {
            throw new InvalidArgumentException(
                'Call find() before calling update() or delete().'
            );
        }

        self::conditions($conditions);
    }
}
