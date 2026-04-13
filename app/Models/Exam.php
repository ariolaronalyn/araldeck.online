<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'course_id',
        'subject_id',
        'type',
        'assessment_type_id',
        'timer_type',
        'total_time_minutes',
        'allow_pause',
        'pause_limit',
        'collaborators'
    ];

    protected $casts = [
        'collaborators' => 'array',
        'allow_pause' => 'boolean'
    ];

    public function assessmentType() {
        return $this->belongsTo(AssessmentType::class);
    }

    public function questions() {
        return $this->belongsToMany(ExamQuestion::class, 'exam_question_list', 'exam_id', 'exam_question_id');
    }
}
