<?php

use App\Livewire\Dashboard;
use App\Livewire\Pages\Settings\Settings;
use App\Livewire\Clients\ClientIndex;
use App\Livewire\Clients\ClientShow;
use App\Livewire\Clients\ClientForm;
use App\Livewire\Audit\AuditIndex;
use App\Livewire\Billing\InvoiceIndex;
use App\Livewire\Panels\PanelIndex;
use App\Livewire\Panels\PanelForm;
use App\Livewire\Panels\PanelShow;
use App\Livewire\Reports\ReportIndex;
use App\Livewire\Services\ServiceIndex;
use App\Livewire\Services\ServiceForm;
use App\Livewire\Services\ServiceShow;
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
        Route::get('/create', PanelForm::class)->name('create');
        Route::get('/{panel}/edit', PanelForm::class)->name('edit');
        Route::get('/{panel}', PanelShow::class)->name('show');
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

    Route::middleware('admin')->prefix('users')->name('users.')->group(function () {
        Route::get('/', App\Livewire\Users\UserIndex::class)->name('index');
        Route::get('/create', App\Livewire\Users\UserForm::class)->name('create');
        Route::get('/{user}/edit', App\Livewire\Users\UserForm::class)->name('edit');
    });

    Route::prefix('audit')->name('audit.')->group(function () {
        Route::get('/', AuditIndex::class)->name('index');
    });

    Route::prefix('invoices')->name('invoices.')->group(function () {
        Route::get('/', InvoiceIndex::class)->name('index');
    });

    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', Settings::class)->name('index');
    });
});

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';