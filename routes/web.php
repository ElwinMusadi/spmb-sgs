<?php

use App\Http\Controllers\InvoiceController;
use App\Models\Faq;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome', [
        'faqs' => Faq::where('is_active', true)
            ->orderBy('order')
            ->get(),
    ]);
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('dashboard', 'pages::dashboard')->name('dashboard');

    Route::livewire('admin/dashboard', 'pages::admin.dashboard')->name('admin.dashboard');

    Route::livewire('admin/users', 'pages::admin.users')->name('admin.users');

    Route::livewire('admin/roles', 'pages::admin.roles')->name('admin.roles');

    Route::livewire('admin/applicants', 'pages::admin.applicants')->name('admin.applicants');

    Route::livewire('admin/finance', 'pages::admin.finance')->name('admin.finance');

    Route::livewire('application', 'pages::application.my-application')->name(
        'application',
    );

    Route::get('application/{application}/invoice', [InvoiceController::class, 'download'])
        ->name('application.invoice.download');
});

require __DIR__.'/settings.php';
