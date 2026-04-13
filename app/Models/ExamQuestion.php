<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'course_id',
        'subject_id',
        'question_text',
        'correct_answer_guide',
        'default_points',
        'is_public',
        'cloned_from_id'
    ];

    // Relationship to the User who created it
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relationship to Course
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    // Relationship to Subject
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
}