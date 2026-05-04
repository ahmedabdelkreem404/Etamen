<?php

namespace App\Modules\Locations\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\Locations\Http\Resources\AreaResource;
use App\Modules\Locations\Http\Resources\CityResource;
use App\Modules\Locations\Infrastructure\Models\Area;
use App\Modules\Locations\Infrastructure\Models\City;

class LocationController extends ApiController
{
    public function cities()
    {
        return $this->success(
            CityResource::collection(City::query()->where('is_active', true)->orderBy('name_en')->get()),
            'Active cities.',
        );
    }

    public function areas()
    {
        return $this->success(
            AreaResource::collection(Area::query()->where('is_active', true)->orderBy('name_en')->get()),
            'Active areas.',
        );
    }
}
