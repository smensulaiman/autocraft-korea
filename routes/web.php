<?php

use Illuminate\Support\Facades\Route;

Route::livewire('/', 'pages::vehicles.index')->name('vehicles.index');
Route::livewire('/vehicles/{vehicle}', 'pages::vehicles.show')->name('vehicles.show');

// Temporary: hero-filter design variants for review. Remove once one is picked.
Route::view('/design-demo', 'design-demo');
