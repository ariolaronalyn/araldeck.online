<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamComment extends Model
{
    public function parent() {
        return $this->belongsTo(ExamComment::class, 'parent_id');
    }

    public function replies() {
        return $this->hasMany(ExamComment::class, 'parent_id')->with('replies');
    }
}
