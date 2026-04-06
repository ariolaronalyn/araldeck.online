<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FlashcardExport implements FromCollection, WithHeadings, WithStyles
{
    protected $data;
    protected $course;
    protected $subject;

    public function __construct(array $data, $course, $subject)
    {
        $this->data = $data;
        $this->course = $course;
        $this->subject = $subject;
    }

    public function collection()
    {
        return collect($this->data);
    }

    public function headings(): array
    {
        // This builds the 6-row header required by your template
        return [
            ['Course:', $this->course],
            ['Subject:', $this->subject],
            ['Reference:', 'Compendious'],
            [''], // Row 4 Empty
            ['Course', 'Subject', 'Question', 'Answer', 'Reference', 'Topic'], // Row 5 Header
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            5 => ['font' => ['bold' => true]], // Make Row 5 (Headings) bold
        ];
    }
}