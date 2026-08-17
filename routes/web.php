<?php

use App\Livewire\Dashboard;
use App\Livewire\Pages\Settings\Settings;
use App\Livewire\Clients\ClientIndex;
use App\Livewire\Clients\ClientShow;
use App\Livewire\Clients\ClientForm;
use App\Livewire\Panels\PanelIndex;
use App\Livewire\Services\ServiceIndex;
use App\Livewire\Services\ServiceForm;
use App\Livewire\Services\ServiceShow;
use App\Livewire\Reports\ReportIndex;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', App\Livewire\Dashboard::class)->name('dashboard');

    Route::prefix('clients')->name('clients.')->group(function () {
        Route::get('/', ClientIndex::class)->name('index');
        Route::get('/create', ClientForm::class)->name('create');
        Route::get('/{client}/edit', ClientForm::class)->name('edit');
        Route::get('/{client}', ClientShow::class)->name('show');
    });

    Route::prefix('panels')->name('panels.')->group(function () {
        Route::get('/', PanelIndex::class)->name('index');
    });

    Route::prefix('services')->name('services.')->group(function () {
        Route::get('/', ServiceIndex::class)->name('index');
        Route::get('/create', ServiceForm::class)->name('create');
        Route::get('/{service}/edit', ServiceForm::class)->name('edit');
        Route::get('/{service}', ServiceShow::class)->name('show');
    });

    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', ReportIndex::class)->name('index');
    });

    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', Settings::class)->name('index');
    });
});

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';