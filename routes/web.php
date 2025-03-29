<?php

use Illuminate\Support\Facades\Route;


# Index Page
Route::get('/', function () {
    return view('welcome');
});


#
Route::get('/', function () {
    return view('Tasks');
});


