<?php

namespace App\Exports;

use App\Models\Attendance;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Carbon\Carbon;

class AttendanceExport implements 
    FromCollection, 
    WithHeadings, 
    WithMapping, 
    WithCustomStartCell, 
    WithEvents,
    WithTitle
{
    protected $attendances;
    protected $user;
    protected $startRow = 5; // Data starts at row 5 (after header)

    public function __construct($attendances = null, $user = null)
    {
        $this->attendances = $attendances;
        $this->user = $user;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        if ($this->attendances) {
            return collect($this->attendances);
        }

        return Attendance::with(['user', 'shift'])
            ->orderBy('date', 'desc')
            ->orderBy('time_in', 'desc')
            ->get();
    }

    /**
     * Start cell for data
     */
    public function startCell(): string
    {
        return 'A' . $this->startRow;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Date',
            'Employee Name',
            'Time In',
            'Time Out',
            'Work Hours',
            'OT Hours',
            'Status',
            'Notes'
        ];
    }

    /**
     * @param mixed $attendance
     */
    public function map($attendance): array
    {
        // Calculate work hours
        $workHours = 0;
        $otHours = 0;
        $standardHours = 8; // Standard working hours per day
        $breakHours = 1; // 1 hour break from 12 PM - 1 PM
        
        if ($attendance->time_in && $attendance->time_out) {
            $timeIn = Carbon::parse($attendance->time_in);
            $timeOut = Carbon::parse($attendance->time_out);
            
            // Use absolute value to avoid negative numbers
            $totalMinutes = abs($timeOut->diffInMinutes($timeIn));
            
            // Calculate total hours in decimal format (e.g., 9.5 hours)
            $totalHours = round($totalMinutes / 60, 2);
            
            // Subtract 1 hour break time
            $totalHours = max(0, $totalHours - $breakHours);
            
            // Calculate OT (anything above standard hours after break deduction)
            if ($totalHours > $standardHours) {
                $otHours = round($totalHours - $standardHours, 2);
                $workHours = $standardHours;
            } else {
                $workHours = $totalHours;
            }
        }

        return [
            $attendance->date ? Carbon::parse($attendance->date)->format('d/m/Y') : '',
            $attendance->user->name ?? 'N/A',
            $attendance->time_in ? Carbon::parse($attendance->time_in)->format('H:i') : '',
            $attendance->time_out ? Carbon::parse($attendance->time_out)->format('H:i') : '',
            $workHours,
            $otHours,
            $attendance->is_late ? 'Late' : 'On Time',
            $attendance->notes ?? ''
        ];
    }

    /**
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $collection = $this->collection();
                $dataRowCount = $collection->count();
                $lastRow = $this->startRow + $dataRowCount;
                
                // Get user name for header
                $userName = auth()->user() ? auth()->user()->name : 'Admin';
                if ($this->user) {
                    $userName = $this->user->name;
                } elseif ($collection->isNotEmpty() && $collection->first()->user) {
                    // If filtering by user, get the first user name
                    $firstUser = $collection->first()->user;
                    $allSameUser = $collection->every(function($attendance) use ($firstUser) {
                        return $attendance->user_id === $firstUser->id;
                    });
                    if ($allSameUser) {
                        $userName = $firstUser->name;
                    }
                }

                // --- HEADER SECTION ---
                // Main title
                $sheet->setCellValue('A1', 'Saat Attendance');
                $sheet->mergeCells('A1:H1');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 16,
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                // Date and User info
                $currentDate = Carbon::now()->format('d/m/Y');
                $sheet->setCellValue('A2', $currentDate . '    ' . $userName);
                $sheet->mergeCells('A2:H2');
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => [
                        'size' => 12,
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                    ],
                ]);

                // Empty row 3 for spacing
                
                // Row 4 is empty (spacing before data)
                
                // Style the data header row (row 5)
                $headerRow = $this->startRow;
                $sheet->getStyle("A{$headerRow}:H{$headerRow}")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '4472C4'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);

                // Style data rows
                if ($dataRowCount > 0) {
                    $dataStartRow = $this->startRow + 1;
                    $dataEndRow = $this->startRow + $dataRowCount;
                    
                    $sheet->getStyle("A{$dataStartRow}:H{$dataEndRow}")->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => 'CCCCCC'],
                            ],
                        ],
                        'alignment' => [
                            'vertical' => Alignment::VERTICAL_CENTER,
                        ],
                    ]);

                    // Center align specific columns
                    $sheet->getStyle("A{$dataStartRow}:A{$dataEndRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("C{$dataStartRow}:G{$dataEndRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }

                // --- FOOTER SECTION (Summary) ---
                $footerRow = $lastRow + 2;
                
                // Calculate totals
                $totalDays = $collection->where('time_in', '!=', null)->count();
                $totalWorkHours = 0;
                $totalOTHours = 0;
                $standardHours = 8;
                $breakHours = 1; // 1 hour lunch break

                foreach ($collection as $attendance) {
                    if ($attendance->time_in && $attendance->time_out) {
                        $timeIn = Carbon::parse($attendance->time_in);
                        $timeOut = Carbon::parse($attendance->time_out);
                        $totalMinutes = abs($timeOut->diffInMinutes($timeIn));
                        $hours = round($totalMinutes / 60, 2);
                        
                        // Subtract 1 hour break
                        $hours = max(0, $hours - $breakHours);
                        
                        if ($hours > $standardHours) {
                            $totalWorkHours += $standardHours;
                            $totalOTHours += ($hours - $standardHours);
                        } else {
                            $totalWorkHours += $hours;
                        }
                    }
                }

                // Calculate OT salary (1 hour = $1)
                $salaryOT = round($totalOTHours * 1, 2); // $1 per OT hour

                // Summary row
                $sheet->setCellValue("A{$footerRow}", 
                    "Total Days Worked: {$totalDays}     Total Time: " . round($totalWorkHours, 2) . " hrs     OT Time: " . round($totalOTHours, 2) . " hrs     Salary OT: $" . number_format($salaryOT, 2)
                );
                $sheet->mergeCells("A{$footerRow}:H{$footerRow}");
                $sheet->getStyle("A{$footerRow}")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 11,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'E7E6E6'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_LEFT,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);

                // Auto-size columns
                foreach (range('A', 'H') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                // Set minimum width for some columns
                $sheet->getColumnDimension('B')->setWidth(25); // Employee Name
                $sheet->getColumnDimension('H')->setWidth(30); // Notes

                // Set row heights
                $sheet->getRowDimension(1)->setRowHeight(25);
                $sheet->getRowDimension(2)->setRowHeight(20);
                $sheet->getRowDimension($headerRow)->setRowHeight(20);
                $sheet->getRowDimension($footerRow)->setRowHeight(25);
            },
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Attendance Report';
    }
}
