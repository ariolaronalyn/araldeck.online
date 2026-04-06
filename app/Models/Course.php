<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = ['title'];

    // A course can have many subjects
    public function subjects() {
        return $this->hasMany(Subject::class);
    }
}