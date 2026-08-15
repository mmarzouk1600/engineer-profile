<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Events\BeforeExport;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class AdminCertificatesExport implements FromCollection, WithHeadings, WithMapping, WithEvents
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Return the collection of data for the export.
     */
    public function collection()
    {
        return $this->data;
    }

    /**
     * Map each row of data to the CSV format.
     */
    public function map($row): array
    {
        // $i = 0;
        return [
            // $this->getSequence($row , $i),
            $this->utf8Encode(optional($row->faculty->anydepartment)->name),
            $this->utf8Encode(optional($row->course)->name),
            $this->utf8Encode(optional($row->course?->admin)->name),
            $this->utf8Encode(optional($row->course?->admin)->email),
            $this->utf8Encode(optional($row->admin)->name),
            $row->is_graduate == 1 ? $this->utf8Encode('خريج') : $this->utf8Encode('طالب'),
            $this->utf8Encode(optional($row->admin)->email),
            $this->utf8Encode(trans('course.' . $row->status)),
            $this->utf8Encode($this->arabicDate($row->created_at)),
        ];
    }

    /**
     * Define the CSV headers.
     */
    public function headings(): array
    {
        return [
            // 'Sequence',
            'القسم',
            'اسم المقرر',
            'اسم المسئول عن المقرر',
            'ايميل المسئول عن المقرر',
            'اسم الطالب',
            'حالة الطالب',
            'البريد الالكتروني للطالب',
            'الحالة',
            'انشاء في',
        ];
    }

    /**
     * Return the sequence number for the row (custom logic as needed).
     */
    protected function getSequence($row, $i)
    {
        // Add logic to determine sequence if needed
        return $i++; // Placeholder or actual logic
    }

    /**
     * Convert the date to Arabic format (assuming you have a helper function for this).
     */
    protected function arabicDate($date)
    {
        // Assuming you have a helper function for this
        return arabicDate($date);
    }

    /**
     * Encode the strings in UTF-8 format.
     */
    protected function utf8Encode($value)
    {
        return mb_convert_encoding($value, 'UTF-8', 'auto');
    }

    /**
     * Register events to add the UTF-8 BOM for proper encoding.
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class    => function(AfterSheet $event) {
            
                $sheet = $event->sheet->getDelegate();
    
                // Set specific column widths
                $sheet->getColumnDimension('A')->setWidth(15); // Set width of column A
                $sheet->getColumnDimension('B')->setWidth(30); // Set width of column B
                $sheet->getColumnDimension('C')->setWidth(25); // Set width of column C
                $sheet->getColumnDimension('D')->setWidth(25); // Set width of column D
                $sheet->getColumnDimension('E')->setWidth(25); // Set width of column E
                $sheet->getColumnDimension('F')->setWidth(25); // Set width of column F
                $sheet->getColumnDimension('G')->setWidth(25); // Set width of column G
                $sheet->getColumnDimension('H')->setWidth(25); // Set width of column H
                $sheet->getColumnDimension('I')->setWidth(25); // Set width of column I
    
                // Apply right-to-left direction for Arabic text
                $sheet->setRightToLeft(true);
    
                // Apply styling for headers in row 1 (A1 to U1)
                $sheet->getStyle('A1:U1')
                      ->getFont()
                      ->setBold(true);  // Make the font bold for headers
    
                // Get the total number of rows to apply conditional formatting
                $highestRow = $sheet->getHighestRow();
    
                // Loop through the status column (assuming it's column H) and apply conditional formatting
                for ($row = 2; $row <= $highestRow; $row++) {
                    $cellValue = $sheet->getCell('H' . $row)->getValue();  // Assuming H column is for status
    
                    if ($cellValue == 'مرفوض') {
                        // Apply red background if the status is 'مرفوض'
                        $sheet->getStyle('H' . $row)->applyFromArray([
                            'font' => [
                                'color' => ['rgb' => Color::COLOR_WHITE], // White text color for better visibility
                            ],
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'FF0000'], // Red background
                            ],
                        ]);
                    } else if ($cellValue == 'مقبول') {
                        // Apply red background if the status is 'مرفوض'
                        $sheet->getStyle('H' . $row)->applyFromArray([
                            'font' => [
                                'color' => ['rgb' => Color::COLOR_WHITE], // White text color for better visibility
                            ],
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => '016d40'], // Red background
                            ],
                        ]);
                    }
                }
            },

            
            BeforeExport::class => function (BeforeExport $event) {
                $writer = $event->writer->getDelegate();
                // Add UTF-8 BOM to ensure correct encoding
                $writer->getProperties()->setCreator('Your App');

                // For CSV, force the addition of the BOM for proper encoding
                if ($event->writer instanceof \Maatwebsite\Excel\Writers\CsvWriter) {
                    $event->writer->setPreCalculateFormulas(false);
                    $event->writer->setUseBOM(true); // This ensures the UTF-8 BOM is added
                }
            },
        ];
    }
}
