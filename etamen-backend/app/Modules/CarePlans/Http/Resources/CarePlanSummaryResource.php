<?php

namespace App\Modules\CarePlans\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CarePlanSummaryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'total_plans' => $this['total_plans'],
            'active_plans' => $this['active_plans'],
            'plans' => CarePlanResource::collection($this['plans']),
            'safe_disclaimer' => $this['safe_disclaimer'],
        ];
    }
}
