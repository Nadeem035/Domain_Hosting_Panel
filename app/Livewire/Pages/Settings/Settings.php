<?php

namespace App\Livewire\Pages\Settings;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]

class Settings extends Component
{
    public function render()
    {
        return view('livewire.settings.index');
    }
}