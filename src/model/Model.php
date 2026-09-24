<?php

namespace SimpleOrm\model;

use SimpleOrm\query\Query;
use SimpleOrm\relations\HasMany;
use SimpleOrm\relations\HasOne;

abstract class Model
{
  protected Query $query;
  protected string $table;
  protected array $attributes = [];

  public function __construct(Query $query)
  {
    $this->query = $query;
  }

  /** 
   * Insert new record through model
   */

  public function create(array $data): int
  {

    $record = $this->query->create($this->table, $data);
    return $record;
  }
  /** 
   * Update already exist record through model
   */
  public function update(array $data): bool
  {

    // var_dump($this->attributes['id']);
    // first find the row been updated
    $find_row = $this->query->find($this->table, ['id' => $this->attributes['id']]);
    $update = $find_row->update($data);
    // $updated_record_response = $;
    return $update;
  }




  /** 
   * Find Model By Its Primary Key 
   */

  public function find(int|string $id): ?static
  {
    $data = $this->query->getOne($this->table, ['id' => $id]);
    if ($data === null) {
      return null;
    }

    return $this->hydrate($data);
  }


  /**
   * Get on or more record using where
   */

  public function getWhere(array $condition): array
  {
    $rows = $this->query->selectWhere(
      $this->table,
      $condition
    );

    $models = [];

    foreach ($rows as $row) {
      $models[] = $this->hydrate($row);
    }

    return $models;
  }

  /**
   * Get All Records From The Table 
   */

  public function all(): ?array
  {

    $rows = $this->query->getAll($this->table);

    if ($rows === null) {
      return null;
    }
    $models = [];
    foreach ($rows as $row) {
      $models[] = $this->hydrate($row);
    }
    return $models;
  }

  /**
   *  Find The First Record Matching The Condition 
   */

  public function where(array $conditions): ?static
  {

    $data = $this->query->getOne($this->table, $conditions);
    if ($data === null) {
      return null;
    }
    return $this->hydrate($data);
  }

  /**
   * Access Database Attributes 
   */

  public function __get(string $name): mixed
  {
    return $this->attributes[$name] ?? null;
  }


  /** 
   * Convert Database Data Into A Model Object.
   */

  public function hydrate(array $data): static
  {
    $model = new static($this->query);
    $model->attributes = $data;
    return $model;
  }

  /**
   * This Is Where HasOne Comes From
   */

  public function hasOne(
    string $related,
    string $foreignKey,
    string $localKey = 'id'
  ): HasOne {
    $relatedModel = new $related($this->query);
    return new HasOne(
      $this,
      $relatedModel,
      $foreignKey,
      $localKey
    );
  }

  /**
   * This Is Where HasMany Comes From
   */

  public function hasMany(
    string $related,
    string $foreignKey,
    string $localKey = 'id'
  ): HasMany {
    $relatedModel = new $related($this->query);
    return new HasMany(
      $this,
      $relatedModel,
      $foreignKey,
      $localKey
    );
  }
}
