<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolClass extends Model
{
    // These tell Laravel which fields can be "Mass Assigned"
    protected $fillable = ['teacher_id', 'name', 'school_year', 'section'];

    /**
     * Relationship: A class belongs to one Teacher.
     */
    public function teacher() {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * Relationship: A class can have many Students.
     */
    public function students() {
        return $this->belongsToMany(User::class, 'class_students', 'school_class_id', 'student_id');
    }
}