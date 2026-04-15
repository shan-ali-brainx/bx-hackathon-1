<?php

namespace App\Http\Controllers;

use App\Enums\GeneratedTaskStatus;
use App\Http\Requests\UpdateGeneratedTaskStatusRequest;
use App\Models\GeneratedTask;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class GeneratedTasksController extends Controller
{
    public function index(Request $request): View
    {
        $statusFilter = $request->query('status');
        $assignedFilter = $request->query('assigned_to');

        $tasks = GeneratedTask::query()
            ->with('assignedTo')
            ->when(
                filled($statusFilter) && is_string($statusFilter),
                fn ($query) => $query->where('status', $statusFilter)
            )
            ->when(
                filled($assignedFilter) && is_numeric($assignedFilter),
                fn ($query) => $query->where('assigned_to', (int) $assignedFilter)
            )
            ->orderByDesc('updated_at')
            ->get();

        $developers = User::query()->orderBy('name')->get();

        return view('generated-tasks.index', [
            'tasks' => $tasks,
            'developers' => $developers,
            'statusOptions' => GeneratedTaskStatus::cases(),
            'filters' => [
                'status' => $statusFilter,
                'assigned_to' => $assignedFilter !== null && $assignedFilter !== '' ? (string) $assignedFilter : null,
            ],
        ]);
    }

    public function update(UpdateGeneratedTaskStatusRequest $request, GeneratedTask $generated_task): RedirectResponse
    {
        $generated_task->update([
            'status' => $request->validated('status'),
        ]);

        return back()
            ->with('flash_success', 'Task status updated.');
    }
}
