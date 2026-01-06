<?php

namespace Database\Factories;

use App\Models\Bug;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comment>
 */
class CommentFactory extends Factory
{
    protected $model = Comment::class;

    public function definition(): array
    {
        return [
            'bug_id' => Bug::factory(),
            'user_id' => User::factory(),
            'body' => fake()->paragraphs(2, true),
            'parent_id' => null,
        ];
    }

    public function reply(Comment $parent): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => $parent->id,
            'bug_id' => $parent->bug_id,
        ]);
    }
}
