<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Deck extends Model
{
    protected $fillable = [
        'user_id', 
        'cloned_from_id', // ADD THIS
        'subject_id', 
        'course_id', 
        'name', 
        'topic', 
        'type', 
        'is_public'
    ];
    public function flashcards() {
        return $this->hasMany(Flashcard::class);
    }

    public function collaborators() { 
        return $this->hasMany(Collaboration::class, 'deck_id');
    }
    public function subject() {
        return $this->belongsTo(Subject::class);
    }

    public function course() {
        return $this->belongsTo(Course::class);
    }
    public function user(){
        return $this->belongsTo(User::class);
    }
    
}
