<?php

namespace App\Modules\CarePlans\Infrastructure\Models;

use App\Modules\CarePlans\Domain\Enums\CarePlanInstructionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CarePlanInstruction extends Model
{
    protected $fillable = [
        'care_plan_id',
        'instruction_type',
        'title',
        'body',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'instruction_type' => CarePlanInstructionType::class,
            'sort_order' => 'integer',
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(CarePlan::class, 'care_plan_id');
    }
}
