<?php

namespace App\Modules\CarePlans\Infrastructure\Models;

use App\Models\User;
use App\Modules\CarePlans\Domain\Enums\CarePlanSource;
use App\Modules\CarePlans\Domain\Enums\CarePlanStatus;
use App\Modules\CarePlans\Domain\Enums\CarePlanType;
use App\Modules\CarePlans\Domain\Enums\CarePlanVisibility;
use App\Modules\Providers\Infrastructure\Models\Provider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CarePlan extends Model
{
    public const SAFETY_DISCLAIMER = 'هذه الخطة للتنظيم والمتابعة ولا تعتبر تشخيصًا أو علاجًا طبيًا. في حالة وجود مرض مزمن أو حمل أو أعراض خطيرة، يجب الرجوع للطبيب أو المختص.';

    public const PROGRESS_DISCLAIMER = 'هذا الملخص يساعدك على متابعة الالتزام فقط، ولا يعني نجاح أو فشل علاج طبي. لا تغيّر دواء أو نظام علاجي بدون الرجوع للطبيب.';

    protected $fillable = [
        'patient_user_id',
        'assigned_by_user_id',
        'provider_id',
        'plan_type',
        'title',
        'description',
        'goal_text',
        'start_date',
        'end_date',
        'status',
        'visibility',
        'source',
        'notes',
        'safety_disclaimer',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'plan_type' => CarePlanType::class,
            'start_date' => 'date',
            'end_date' => 'date',
            'status' => CarePlanStatus::class,
            'visibility' => CarePlanVisibility::class,
            'source' => CarePlanSource::class,
            'metadata' => 'array',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_user_id');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by_user_id');
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function days(): HasMany
    {
        return $this->hasMany(CarePlanDay::class);
    }

    public function foodItems(): HasMany
    {
        return $this->hasMany(CarePlanFoodItem::class);
    }

    public function instructions(): HasMany
    {
        return $this->hasMany(CarePlanInstruction::class)->orderBy('sort_order');
    }

    public function checkins(): HasMany
    {
        return $this->hasMany(CarePlanCheckin::class);
    }

    public function mealLogs(): HasMany
    {
        return $this->hasMany(MealLog::class);
    }
}
