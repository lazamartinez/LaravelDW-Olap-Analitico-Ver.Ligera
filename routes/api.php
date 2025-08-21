<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Si algún día necesitas endpoints sin sesión (ej. para apps móviles),
| los dejás acá. Pero por ahora mejor todo en web.php.
|--------------------------------------------------------------------------
*/

Route::get('/status', function () {
    return response()->json(['status' => 'ok']);
});
