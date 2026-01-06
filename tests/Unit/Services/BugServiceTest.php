<?php

use App\Models\Bug;
use App\Models\Tag;
use App\Models\User;
use App\Services\BugService;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
    $this->bugService = app(BugService::class);
});

it('creates a bug with basic data', function () {
    $bug = $this->bugService->create([
        'title' => 'Test Bug',
        'description' => 'Description',
        'priority' => 'high',
    ]);

    expect($bug->title)->toBe('Test Bug')
        ->and($bug->priority)->toBe('high')
        ->and($bug->reporter_id)->toBe($this->user->id)
        ->and($bug->status)->toBe('open');
});

it('creates a bug with tags', function () {
    $tags = Tag::factory()->count(2)->create();

    $bug = $this->bugService->create([
        'title' => 'Test Bug',
        'description' => 'Description',
        'priority' => 'medium',
        'tags' => $tags->pluck('id')->toArray(),
    ]);

    expect($bug->tags)->toHaveCount(2);
});

it('creates a bug with assignee', function () {
    $assignee = User::factory()->create();

    $bug = $this->bugService->create([
        'title' => 'Test Bug',
        'description' => 'Description',
        'assignee_id' => $assignee->id,
    ]);

    expect($bug->assignee_id)->toBe($assignee->id);
});

it('logs activity when bug is created', function () {
    $bug = $this->bugService->create([
        'title' => 'Test Bug',
        'description' => 'Description',
    ]);

    expect($bug->activities()->where('type', 'created')->exists())->toBeTrue();
});

it('updates bug basic fields', function () {
    $bug = Bug::factory()->create([
        'reporter_id' => $this->user->id,
        'title' => 'Original Title',
    ]);

    $this->bugService->update($bug, [
        'title' => 'Updated Title',
        'description' => 'Updated Description',
    ]);

    $bug->refresh();
    expect($bug->title)->toBe('Updated Title')
        ->and($bug->description)->toBe('Updated Description');
});

it('logs activity when status changes', function () {
    $bug = Bug::factory()->create([
        'reporter_id' => $this->user->id,
        'status' => 'open',
    ]);

    $this->bugService->update($bug, ['status' => 'in_progress']);

    expect($bug->fresh()->status)->toBe('in_progress')
        ->and($bug->activities()->where('type', 'status_changed')->exists())->toBeTrue();
});

it('sets resolved_at when status changes to resolved', function () {
    $bug = Bug::factory()->create([
        'reporter_id' => $this->user->id,
        'status' => 'open',
    ]);

    $this->bugService->update($bug, ['status' => 'resolved']);

    expect($bug->fresh()->resolved_at)->not->toBeNull();
});

it('sets closed_at when status changes to closed', function () {
    $bug = Bug::factory()->create([
        'reporter_id' => $this->user->id,
        'status' => 'open',
    ]);

    $this->bugService->update($bug, ['status' => 'closed']);

    expect($bug->fresh()->closed_at)->not->toBeNull();
});

it('clears resolved_at when status changes from resolved', function () {
    $bug = Bug::factory()->create([
        'reporter_id' => $this->user->id,
        'status' => 'resolved',
        'resolved_at' => now(),
    ]);

    $this->bugService->update($bug, ['status' => 'open']);

    expect($bug->fresh()->resolved_at)->toBeNull();
});

it('logs activity when priority changes', function () {
    $bug = Bug::factory()->create([
        'reporter_id' => $this->user->id,
        'priority' => 'low',
    ]);

    $this->bugService->update($bug, ['priority' => 'critical']);

    expect($bug->activities()->where('type', 'priority_changed')->exists())->toBeTrue();
});

it('logs activity when assignee changes', function () {
    $assignee = User::factory()->create();
    $bug = Bug::factory()->create([
        'reporter_id' => $this->user->id,
        'assignee_id' => null,
    ]);

    $this->bugService->update($bug, ['assignee_id' => $assignee->id]);

    expect($bug->activities()->where('type', 'assigned')->exists())->toBeTrue();
});

it('syncs tags and logs changes', function () {
    $oldTags = Tag::factory()->count(2)->create();
    $newTags = Tag::factory()->count(2)->create();

    $bug = Bug::factory()->create(['reporter_id' => $this->user->id]);
    $bug->tags()->sync($oldTags->pluck('id'));

    $this->bugService->update($bug, [
        'tags' => $newTags->pluck('id')->toArray(),
    ]);

    expect($bug->fresh()->tags->pluck('id')->toArray())
        ->toBe($newTags->pluck('id')->toArray());
});
