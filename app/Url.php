<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Url extends Model
{
    protected $fillable = [
        'encoded_url',
        'visited',
        'url'
    ];
}
