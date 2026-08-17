<?php

namespace App\Livewire\Reports;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]

class ReportIndex extends Component
{
    public function render()
    {
        return view('livewire.reports.index');
    }
}