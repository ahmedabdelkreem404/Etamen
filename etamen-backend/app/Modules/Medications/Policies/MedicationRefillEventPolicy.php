<?php

namespace App\Modules\Medications\Policies;

class MedicationRefillEventPolicy
{
    use OwnsMedicationRecord;
}
