<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['username', 'full_name', 'avatar'];
}
