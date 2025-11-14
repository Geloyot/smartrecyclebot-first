<?php

namespace App\Exports;

use App\Models\WasteObject;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ClassificationsExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return WasteObject::query()->join('bins', 'waste_objects.bin_id', '=', 'bins.id')
            ->select(
                'waste_objects.id',
                'bins.name as bin_name',
                'waste_objects.score',
                'waste_objects.model_name'
            )
            ->get();
    }

    public function headings(): array
    {
        return ['Classification ID', 'Classification', 'Score', 'Model File Name'];
    }
}
