<?php

namespace App\Livewire\Services;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]

class ServiceShow extends Component
{
    public function render()
    {
        return view('livewire.services.show');
    }
}