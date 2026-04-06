<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Flashcard extends Model
{
    protected $fillable = ['user_id', 'deck_id', 'subject_id', 'question', 'answer', 'reference', 'topic'];

    protected $casts = [
        'labels' => 'array', // Automatically handles JSON to Array conversion
    ];

    public function deck() {
        return $this->belongsTo(Deck::class);
    }

    /**
     * Get the subject that owns the flashcard.
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }
 
    public function collaborations()
    {
        return $this->hasMany(Collaboration::class, 'flashcard_id');
    }
}