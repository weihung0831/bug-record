<?php

use App\Models\Bug;
use App\Models\Tag;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
});

it('can list bugs', function () {
    Bug::factory()->count(5)->create(['reporter_id' => $this->user->id]);

    $this->actingAs($this->user)
        ->get(route('bugs.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Bugs/Index')
            ->has('bugs.data', 5)
        );
});

it('can view create bug page', function () {
    $this->actingAs($this->user)
        ->get(route('bugs.create'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Bugs/Create')
            ->has('priorities')
            ->has('tags')
            ->has('users')
        );
});

it('can create a bug', function () {
    $bugData = [
        'title' => 'Test Bug',
        'description' => 'This is a test bug description',
        'priority' => 'high',
    ];

    $this->actingAs($this->user)
        ->post(route('bugs.store'), $bugData)
        ->assertRedirect();

    $this->assertDatabaseHas('bugs', [
        'title' => 'Test Bug',
        'reporter_id' => $this->user->id,
        'priority' => 'high',
    ]);
});

it('can create a bug with tags', function () {
    $tags = Tag::factory()->count(2)->create();

    $bugData = [
        'title' => 'Bug with Tags',
        'description' => 'Description',
        'priority' => 'medium',
        'tags' => $tags->pluck('id')->toArray(),
    ];

    $this->actingAs($this->user)
        ->post(route('bugs.store'), $bugData)
        ->assertRedirect();

    $bug = Bug::where('title', 'Bug with Tags')->first();
    expect($bug->tags)->toHaveCount(2);
});

it('can view a bug', function () {
    $bug = Bug::factory()->create(['reporter_id' => $this->user->id]);

    $this->actingAs($this->user)
        ->get(route('bugs.show', $bug))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Bugs/Show')
            ->has('bug')
            ->where('bug.id', $bug->id)
        );
});

it('can view edit bug page', function () {
    $bug = Bug::factory()->create(['reporter_id' => $this->user->id]);

    $this->actingAs($this->user)
        ->get(route('bugs.edit', $bug))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Bugs/Edit')
            ->where('bug.id', $bug->id)
        );
});

it('can update a bug', function () {
    $bug = Bug::factory()->create(['reporter_id' => $this->user->id]);

    $this->actingAs($this->user)
        ->put(route('bugs.update', $bug), [
            'title' => 'Updated Title',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('bugs', [
        'id' => $bug->id,
        'title' => 'Updated Title',
    ]);
});

it('can update bug status', function () {
    $bug = Bug::factory()->create([
        'reporter_id' => $this->user->id,
        'status' => 'open',
    ]);

    $this->actingAs($this->user)
        ->put(route('bugs.update', $bug), [
            'status' => 'in_progress',
        ])
        ->assertRedirect();

    expect($bug->fresh()->status)->toBe('in_progress');
});

it('can delete a bug', function () {
    $bug = Bug::factory()->create(['reporter_id' => $this->user->id]);

    $this->actingAs($this->user)
        ->delete(route('bugs.destroy', $bug))
        ->assertRedirect();

    $this->assertSoftDeleted('bugs', ['id' => $bug->id]);
});

it('cannot delete bug created by another user', function () {
    $otherUser = User::factory()->create();
    $bug = Bug::factory()->create(['reporter_id' => $otherUser->id]);

    $this->actingAs($this->user)
        ->delete(route('bugs.destroy', $bug))
        ->assertForbidden();
});

it('validates required fields when creating bug', function () {
    $this->actingAs($this->user)
        ->post(route('bugs.store'), [])
        ->assertSessionHasErrors(['title', 'description']);
});

it('requires authentication to access bugs', function () {
    $this->get(route('bugs.index'))
        ->assertRedirect(route('login'));
});
