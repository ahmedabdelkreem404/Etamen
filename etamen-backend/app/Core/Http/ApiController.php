<?php

namespace App\Core\Http;

use App\Core\Traits\ApiResponds;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

abstract class ApiController extends Controller
{
    use ApiResponds, AuthorizesRequests;
}
