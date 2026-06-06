<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'content',
    ];
}
