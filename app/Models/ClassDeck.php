<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassDeck extends Model
{
    protected $fillable = ['school_class_id', 'deck_name', 'subject_id', 'start_at', 'end_at'];
}