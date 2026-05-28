<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    protected $fillable = ['name', 'region'];

    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}
