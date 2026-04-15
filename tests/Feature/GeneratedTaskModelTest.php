<?php

namespace Tests\Feature;

use App\Enums\GeneratedTaskStatus;
use App\Models\GeneratedTask;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GeneratedTaskModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_and_retrieves_generated_task(): void
    {
        $task = GeneratedTask::query()->create([
            'task_name' => 'Implement dashboard graph view',
            'description' => 'Add time-series charts for revenue with date filters.',
            'technology_stack' => ['Laravel', 'Livewire', 'Chart.js'],
            'dependencies' => ['Design mock-ups', 'Metrics API'],
            'status' => GeneratedTaskStatus::Pending,
            'assigned_to' => null,
        ]);

        $this->assertDatabaseHas('generated_tasks', [
            'id' => $task->id,
            'task_name' => 'Implement dashboard graph view',
            'status' => 'pending',
        ]);

        $fresh = GeneratedTask::query()->findOrFail($task->id);
        $this->assertSame(GeneratedTaskStatus::Pending, $fresh->status);
        $this->assertSame(['Laravel', 'Livewire', 'Chart.js'], $fresh->technology_stack);
        $this->assertSame(['Design mock-ups', 'Metrics API'], $fresh->dependencies);
        $this->assertNotNull($fresh->created_at);
    }

    public function test_assigns_task_to_user(): void
    {
        $user = User::factory()->create();

        $task = GeneratedTask::factory()->assigned($user)->create([
            'task_name' => 'API integration',
        ]);

        $task->load('assignedTo');

        $this->assertTrue($user->is($task->assignedTo));
        $this->assertCount(1, $user->assignedGeneratedTasks);
        $this->assertTrue($user->assignedGeneratedTasks->first()->is($task));
    }
}
