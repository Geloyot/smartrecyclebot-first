<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class UsersExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return User::query()->join('roles', 'users.role_id', '=', 'roles.id')
            ->select(
                'users.id',
                'roles.name as role',
                'users.name',
                'users.email',
                'users.created_at'
            )
            ->get();
    }

    public function headings(): array
    {
        return ['ID', 'Role', 'Name', 'Email', 'Registered At'];
    }
}
