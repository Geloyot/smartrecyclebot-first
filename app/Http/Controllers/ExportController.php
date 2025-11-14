<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Notification;
use App\Models\BinReading;
use App\Models\WasteObject;
use App\Exports\UsersExport;
use App\Exports\NotificationsExport;
use App\Exports\BinReadingsExport;
use App\Exports\ClassificationsExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    public function exportPdfUser()
    {
        $users = User::all();
        $pdf = PDF::loadView('exports.pdf_users', compact('users'));
        return $pdf->download('users_report.pdf');
    }
    public function exportPdfNotification()
    {
        $notifications = Notification::all();
        $pdf = PDF::loadView('exports.pdf_notifications', compact('notifications'));
        return $pdf->download('notifications_report.pdf');
    }
    public function exportPdfBinReading()
    {
        $bin_readings = BinReading::with('bin')->get();
        $pdf = PDF::loadView('exports.pdf_bin_readings', compact('bin_readings'));
        return $pdf->download('bin_readings_report.pdf');
    }
    public function exportPdfClassification()
    {
        $classifications = WasteObject::with('bin')->get();
        $pdf = PDF::loadView('exports.pdf_classifications', compact('classifications'));
        return $pdf->download('classifications_report.pdf');
    }

    public function exportCsvUser()
    {
        return Excel::download(new UsersExport, 'users_report.csv');
    }
    public function exportCsvNotification()
    {
        return Excel::download(new NotificationsExport, 'notifications_report.csv');
    }
    public function exportCsvBinReading()
    {
        return Excel::download(new BinReadingsExport, 'bin_readings_report.csv');
    }
    public function exportCsvClassification()
    {
        return Excel::download(new ClassificationsExport, 'classifications_report.csv');
    }
}
