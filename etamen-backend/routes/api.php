<?php

use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    require app_path('Modules/Identity/Http/routes.php');
    require app_path('Modules/Patients/Http/routes.php');
    require app_path('Modules/Providers/Http/routes.php');
    require app_path('Modules/Locations/Http/routes.php');
    require app_path('Modules/Payments/Http/routes.php');
});
