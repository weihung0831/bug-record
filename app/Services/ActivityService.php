<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\Bug;

class ActivityService
{
    public function log(
        Bug $bug,
        string $type,
        ?string $field = null,
        mixed $oldValue = null,
        mixed $newValue = null,
        ?array $metadata = null
    ): Activity {
        return Activity::create([
            'bug_id' => $bug->id,
            'user_id' => auth()->id(),
            'type' => $type,
            'field' => $field,
            'old_value' => is_array($oldValue) ? json_encode($oldValue) : $oldValue,
            'new_value' => is_array($newValue) ? json_encode($newValue) : $newValue,
            'metadata' => $metadata,
        ]);
    }
}
