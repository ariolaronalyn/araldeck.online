<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Collaboration extends Model
{
    protected $fillable = ['deck_id', 'invited_user_id', 'status'];  

    public function deck() {
        return $this->belongsTo(Deck::class);
    }

    public function invitedUser() {
        return $this->belongsTo(User::class, 'invited_user_id');
    }
}