<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $fillable = ['course_id', 'title'];

    // A subject belongs to a course
    public function course() {
        return $this->belongsTo(Course::class);
    }
}