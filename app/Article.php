<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    protected $fillable = [
        'encoded_url',
        'free',
        'url',
        'tweeted',
        'date',
        'timestamp'
    ];
}
