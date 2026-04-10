<?php

namespace App\Http\Requests;

use App\Models\BatchSchedule;

class UpdateBatchScheduleRequest extends StoreBatchScheduleRequest
{
    public function authorize(): bool
    {
        $schedule = $this->route('schedule');

        return $schedule instanceof BatchSchedule
            ? ($this->user()?->can('update', $schedule) ?? false)
            : false;
    }
}
