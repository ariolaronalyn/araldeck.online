<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamAnswer extends Model
{
    protected $fillable = [
        'exam_submission_id',
        'exam_question_id',
        'answer_text',
        'points_given'
    ];

    

    public function submission() {
        return $this->belongsTo(ExamSubmission::class, 'exam_submission_id');
    }

    public function question() {
        return $this->belongsTo(ExamQuestion::class, 'exam_question_id');
    }
}