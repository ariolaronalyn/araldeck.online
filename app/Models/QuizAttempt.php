<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizAttempt extends Model
{
    protected $fillable = ['user_id', 'deck_id', 'score', 'total_questions', 'details', 'override_logs'];

    public function deck() {
        return $this->belongsTo(Deck::class);
    }
}