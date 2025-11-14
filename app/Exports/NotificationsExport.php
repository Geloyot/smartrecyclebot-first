<?php

namespace App\Exports;

use App\Models\Notification;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class NotificationsExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Notification::select('type', 'title', 'message', 'level', 'created_at')->get();
    }

    public function headings(): array
    {
        return ['Alert Type', 'Title', 'Description', 'Alert Urgency', 'Notification Date'];
    }
}
