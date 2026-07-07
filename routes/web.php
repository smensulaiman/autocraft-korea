<?php

use Illuminate\Support\Facades\Route;

Route::livewire('/', 'pages::vehicles.index')->name('vehicles.index');
Route::livewire('/vehicles/{vehicle}', 'pages::vehicles.show')->name('vehicles.show');
