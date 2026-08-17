<?php

namespace App\Livewire\Panels;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]

class PanelIndex extends Component
{
    public function render()
    {
        return view('livewire.panels.index');
    }
}