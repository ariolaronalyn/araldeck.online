<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FlashcardTemplateExport implements FromCollection, WithHeadings, WithDrawings, WithCustomStartCell, WithStyles, ShouldAutoSize, WithEvents
{
    protected $course, $subject, $rows;

    public function __construct($course, $subject, $rows) {
        $this->course = $course; $this->subject = $subject; $this->rows = $rows;
    }

        
    public function collection() {
        $data = [];
        for ($i = 0; $i < $this->rows; $i++) {
            // Added a 6th empty string for Topic
            $data[] = [$this->course, $this->subject, '', '', '', '']; 
        }
        return collect($data);
    }

    public function headings(): array {
        return ['Course', 'Subject', 'Question', 'Answer', 'Reference', 'Topic'];
    }

    public function registerEvents(): array {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->getProtection()->setPassword('araldeck123')->setSheet(true);

                // Unlock columns C, D, E, and F (Question to Topic)
                $sheet->getStyle('C6:F500')->getProtection()
                    ->setLocked(\PhpOffice\PhpSpreadsheet\Style\Protection::PROTECTION_UNPROTECTED);
            },
        ];
    }
     

    public function startCell(): string { return 'A5'; }

    public function drawings() {
        $drawing = new Drawing();
        $drawing->setPath(public_path('images/araldeck_full_logo.png')); 
        $drawing->setHeight(90); $drawing->setCoordinates('A1');
        return $drawing;
    }

    public function styles(Worksheet $sheet) {
        for ($i = 1; $i <= 4; $i++) { $sheet->getRowDimension($i)->setRowHeight(30); }
        return [
            5 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '4F81BD']],
            ],
        ];
    }

   
}