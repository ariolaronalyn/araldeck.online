<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Classroom;

class ClassController extends Controller
{
    public function store(Request $request) {
        // Details: class_name, school_year, section    
    $class = Classroom::create([
        'teacher_id' => auth()->id(),
        'name' => $request->name,
        'school_year' => $request->school_year,
        'section' => $request->section,
    ]);
        
    return redirect()->route('classes.index')->with('success', 'Class created successfully.');
}
}

