<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class DataExport implements FromCollection, WithHeadings, WithMapping
{
    protected $data;
    protected $columns;
    protected $columnLabels;
    protected $booleanColumns;

    public function __construct($data, $columns, $columnLabels, $booleanColumns)
    {
        $this->data = $data;
        $this->columns = $columns;
        $this->columnLabels = $columnLabels;
        $this->booleanColumns = $booleanColumns;
    }

    public function collection()
    {
        return $this->data;
    }

    public function headings(): array
    {
        // Use custom column labels if available, otherwise fallback to column names
        return array_map(function ($column) {
            return $this->columnLabels[$column] ?? ucfirst($column);
        }, $this->columns);
    }

    public function map($row): array
    {
        $mappedRow = [];
        foreach ($this->columns as $column) {
            if (isset($this->booleanColumns[$column])) {
                // Handle boolean columns
                $booleanDisplay = $row[$column] ? $this->booleanColumns[$column]['true'] : $this->booleanColumns[$column]['false'];
                $mappedRow[] = $booleanDisplay['text'];
            } else {
                // Handle regular columns
                $mappedRow[] = $row[$column];
            }
        }
        return $mappedRow;
    }
}
