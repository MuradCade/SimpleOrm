<?php

namespace SimpleOrm\relations;


use SimpleOrm\model\Model;
// use SimpleOrm\query\Query;

class HasOne extends Model
{
    protected Model $parent;
    protected Model $related;
    protected string $foreignKey;
    protected string $localKey;


    public function __construct(
        Model $parent,
        Model $related,
        string $foreignKey,
        string $localKey
    ) {
        $this->parent = $parent;
        $this->related = $related;
        $this->foreignKey = $foreignKey;
        $this->localKey = $localKey;
    }


    /**
     *  Get The Related Model
     */

    public function get(): ?Model
    {

        /**
         * Get The Value From The Parent Model
         * example : User ID = 1
         */

        $localValue = $this->parent->{$this->localKey};
        if ($localValue === null) {
            return null;
        }

        /**
         * Ask Related Model To Find The Matching Record
         * Example:
         * Profile::where([
         * 'user_id'=>1
         * ])
         */
        // $this->related comes from profile class which extends(inherites) model class (real owner of where method is model clss)  
        return $this->related->where([$this->foreignKey => $localValue]);
    }
}
