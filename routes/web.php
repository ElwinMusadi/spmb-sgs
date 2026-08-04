<?php

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
    Route::view('dashboard', 'dashboard')->name('dashboard');

    Route::livewire('application', 'pages::application.my-application')->name(
        'application',
    );
});

require __DIR__.'/settings.php';
