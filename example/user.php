<?php

use SimpleOrm\model\Model;
use SimpleOrm\relations\HasMany;
use SimpleOrm\relations\HasOne;

require_once './profile.php';

class User extends Model
{
    protected string $table = 'users';

    public function profile(): HasMany
    {
        return $this->hasMany(
            Profile::class,
            'user_id'
        );
    }
}
