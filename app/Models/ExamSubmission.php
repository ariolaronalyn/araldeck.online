<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamSubmission extends Model
{
    protected $fillable = [
        'exam_id', 
        'user_id', 
        'status', 
        'remaining_time_seconds', 
        'started_at', 
        'submitted_at', 
        'total_score'
    ];

    protected $casts = [
        'time_per_question' => 'array',
        'proctoring_logs' => 'array',
        'started_at' => 'datetime',
        'submitted_at' => 'datetime',
    ];

    public function exam() {
        return $this->belongsTo(Exam::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
    
    public function answers() {
        return $this->hasMany(ExamAnswer::class, 'exam_submission_id');
    }
}