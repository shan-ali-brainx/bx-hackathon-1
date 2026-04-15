<?php

namespace Database\Factories;

use App\Enums\GeneratedTaskStatus;
use App\Models\GeneratedTask;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GeneratedTask>
 */
class GeneratedTaskFactory extends Factory
{
    protected $model = GeneratedTask::class;

    public function definition(): array
    {
        return [
            'task_name' => fake()->sentence(4),
            'description' => fake()->paragraphs(2, true),
            'technology_stack' => ['Laravel', 'PHP', 'MySQL'],
            'dependencies' => ['Design sign-off', 'API contract'],
            'status' => GeneratedTaskStatus::Pending,
            'assigned_to' => null,
        ];
    }

    public function assigned(User $user): static
    {
        return $this->state(fn (): array => ['assigned_to' => $user->id]);
    }
}
