<?php

namespace App\Services;

use App\Models\Bug;
use Illuminate\Support\Facades\DB;

class BugService
{
    public function __construct(
        private ActivityService $activityService
    ) {}

    public function create(array $data): Bug
    {
        return DB::transaction(function () use ($data) {
            $bug = Bug::create([
                'title' => $data['title'],
                'description' => $data['description'],
                'priority' => $data['priority'] ?? 'medium',
                'reporter_id' => auth()->id(),
                'assignee_id' => $data['assignee_id'] ?? null,
            ]);

            if (! empty($data['tags'])) {
                $bug->tags()->sync($data['tags']);
            }

            $this->activityService->log($bug, 'created');

            return $bug;
        });
    }

    public function update(Bug $bug, array $data): Bug
    {
        return DB::transaction(function () use ($bug, $data) {
            $original = $bug->getOriginal();

            $bug->fill([
                'title' => $data['title'] ?? $bug->title,
                'description' => $data['description'] ?? $bug->description,
                'priority' => $data['priority'] ?? $bug->priority,
                'assignee_id' => array_key_exists('assignee_id', $data) ? $data['assignee_id'] : $bug->assignee_id,
            ]);

            if (isset($data['status']) && $data['status'] !== $bug->status) {
                $this->handleStatusChange($bug, $data['status']);
            }

            $this->logChanges($bug, $original);

            $bug->save();

            if (isset($data['tags'])) {
                $oldTags = $bug->tags->pluck('id')->toArray();
                $bug->tags()->sync($data['tags']);
                $this->logTagChanges($bug, $oldTags, $data['tags']);
            }

            return $bug->fresh();
        });
    }

    private function handleStatusChange(Bug $bug, string $newStatus): void
    {
        $oldStatus = $bug->status;
        $bug->status = $newStatus;

        if ($newStatus === 'resolved' && $oldStatus !== 'resolved') {
            $bug->resolved_at = now();
        } elseif ($newStatus !== 'resolved') {
            $bug->resolved_at = null;
        }

        if ($newStatus === 'closed' && $oldStatus !== 'closed') {
            $bug->closed_at = now();
        } elseif ($newStatus !== 'closed') {
            $bug->closed_at = null;
        }

        $this->activityService->log($bug, 'status_changed', 'status', $oldStatus, $newStatus);
    }

    private function logChanges(Bug $bug, array $original): void
    {
        $trackFields = ['title', 'description', 'priority', 'assignee_id'];

        foreach ($trackFields as $field) {
            if ($bug->isDirty($field)) {
                $type = match ($field) {
                    'assignee_id' => $bug->$field ? 'assigned' : 'unassigned',
                    'priority' => 'priority_changed',
                    default => 'updated',
                };

                $this->activityService->log(
                    $bug,
                    $type,
                    $field,
                    $original[$field] ?? null,
                    $bug->$field
                );
            }
        }
    }

    private function logTagChanges(Bug $bug, array $oldTags, array $newTags): void
    {
        $added = array_diff($newTags, $oldTags);
        $removed = array_diff($oldTags, $newTags);

        foreach ($added as $tagId) {
            $this->activityService->log($bug, 'tag_added', metadata: ['tag_id' => $tagId]);
        }

        foreach ($removed as $tagId) {
            $this->activityService->log($bug, 'tag_removed', metadata: ['tag_id' => $tagId]);
        }
    }
}
