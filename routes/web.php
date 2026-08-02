<?php

use Illuminate\Support\Facades\Route;

Route::get("/", function () {
  return view("welcome", [
    "faqs" => \App\Models\Faq::where("is_active", true)
      ->orderBy("order")
      ->get(),
  ]);
})->name("home");

Route::middleware(["auth", "verified"])->group(function () {
  Route::view("dashboard", "dashboard")->name("dashboard");
});

require __DIR__ . "/settings.php";
