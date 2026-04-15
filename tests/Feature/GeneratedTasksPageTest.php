<?php

namespace Tests\Feature;

use App\Enums\GeneratedTaskStatus;
use App\Models\GeneratedTask;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GeneratedTasksPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(ValidateCsrfToken::class);
    }

    public function test_index_lists_tasks_with_filters(): void
    {
        $user = User::factory()->create(['name' => 'Alex Developer']);
        $pending = GeneratedTask::factory()->create([
            'task_name' => 'Unique Pending Task Alpha',
            'status' => GeneratedTaskStatus::Pending,
            'assigned_to' => $user->id,
        ]);
        $done = GeneratedTask::factory()->create([
            'task_name' => 'Unique Done Task Beta',
            'status' => GeneratedTaskStatus::Done,
            'assigned_to' => $user->id,
        ]);

        $filtered = $this->get(route('generated-tasks.index', [
            'status' => GeneratedTaskStatus::Pending->value,
            'assigned_to' => $user->id,
        ]));

        $filtered->assertOk()
            ->assertSee('Unique Pending Task Alpha', false)
            ->assertDontSee('Unique Done Task Beta', false);
    }

    public function test_patch_sets_in_progress_and_completed(): void
    {
        $task = GeneratedTask::factory()->create([
            'status' => GeneratedTaskStatus::Pending,
        ]);

        $this->from(route('generated-tasks.index'))
            ->patch(route('generated-tasks.update', $task), [
                'status' => GeneratedTaskStatus::InProgress->value,
            ])
            ->assertRedirect();

        $task->refresh();
        $this->assertSame(GeneratedTaskStatus::InProgress, $task->status);

        $this->from(route('generated-tasks.index'))
            ->patch(route('generated-tasks.update', $task), [
                'status' => GeneratedTaskStatus::Done->value,
            ])
            ->assertRedirect();

        $task->refresh();
        $this->assertSame(GeneratedTaskStatus::Done, $task->status);
    }
}
