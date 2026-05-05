<?php

namespace App\Modules\CarePlans\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CarePlanProgressResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'plan_id' => $this['plan_id'],
            'from' => $this['from'],
            'to' => $this['to'],
            'days_count' => $this['days_count'],
            'checkins_count' => $this['checkins_count'],
            'average_commitment_score' => $this['average_commitment_score'],
            'meal_logs_count' => $this['meal_logs_count'],
            'followed_count' => $this['followed_count'],
            'partially_followed_count' => $this['partially_followed_count'],
            'skipped_count' => $this['skipped_count'],
            'replaced_count' => $this['replaced_count'],
            'extra_meal_count' => $this['extra_meal_count'],
            'planned_required_meals_count' => $this['planned_required_meals_count'],
            'adherence_percentage' => $this['adherence_percentage'],
            'latest_checkin' => $this['latest_checkin'] ? new CarePlanCheckinResource($this['latest_checkin']) : null,
            'latest_meal_logs' => MealLogResource::collection($this['latest_meal_logs']),
            'safe_disclaimer' => $this['safe_disclaimer'],
        ];
    }
}
