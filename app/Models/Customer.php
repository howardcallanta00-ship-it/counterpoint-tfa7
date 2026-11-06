<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['full_name', 'email', 'phone'];
}
