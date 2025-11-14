<?php

namespace App\Livewire;

use App\Models\WasteObject;
use Livewire\Component;

class WasteClassify extends Component
{
    public $classifications = [];
    public $stats = [];

    public function mount()
    {
        $this->loadData();
    }

    public function loadData()
    {
        $today = now()->startOfDay();

        $this->classifications = WasteObject::latest()->take(10)->get();

        $this->stats = [
            'total_today' => WasteObject::where('created_at', '>=', $today)->count(),
            'biodegradable' => WasteObject::where('classification', 'Biodegradable')->count(),
            'non_biodegradable' => WasteObject::where('classification', 'Non-Biodegradable')->count(),
        ];
    }

    public function render()
    {
        return view('livewire.waste-classify');
    }
}
