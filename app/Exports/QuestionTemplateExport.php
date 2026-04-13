<?php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class QuestionTemplateExport implements FromArray, WithHeadings, WithStyles
{
    protected $course, $subject, $rows;

    public function __construct($course, $subject, $rows) {
        $this->course = $course;
        $this->subject = $subject;
        $this->rows = $rows;
    }

    public function headings(): array {
        return [
            'Course', 
            'Subject', 
            'Question Text', 
            'Correct Answer Guide', 
            'Default Points'
        ];
    }

    public function array(): array {
        $data = [];
        for ($i = 0; $i < $this->rows; $i++) {
            // First row pre-filled with selected course/subject, others empty
            $data[] = [
                $i === 0 ? $this->course : '',
                $i === 0 ? $this->subject : '',
                '', '', '5'
            ];
        }
        return $data;
    }

    public function styles(Worksheet $sheet) {
        return [1 => ['font' => ['bold' => true]]];
    }
}