<?php

namespace App\Exports;

use App\Models\BinReading;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BinReadingsExport implements FromCollection, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return BinReading::query()->join('bins', 'bin_readings.bin_id', '=', 'bins.id')
            ->select(
                'bin_readings.id',
                'bins.name as bin_name',
                'bin_readings.fill_level',
                'bin_readings.created_at'
            )
            ->get();
    }

    /**
    * @return array
    */
    public function headings(): array
    {
        return [
            'ID',
            'Bin Location',
            'Fill Level',
            'Timestamp'
        ];
    }
}
