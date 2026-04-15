@props([
    'tasks',
])

@php
    use App\Enums\GeneratedTaskStatus;
@endphp

@if ($tasks->isEmpty())
    <div class="rounded-xl border border-dashed border-zinc-300 px-6 py-12 text-center text-sm text-zinc-600 dark:border-zinc-700 dark:text-zinc-400">
        No tasks match these filters yet. Clear a filter or add tasks in the database.
    </div>
@else
    <ul class="space-y-4" role="list">
        @foreach ($tasks as $task)
            <li class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-950 sm:p-6">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div class="min-w-0 flex-1 space-y-3">
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-50">
                                {{ $task->task_name }}
                            </h2>
                            @php
                                $st = $task->status;
                                $badge =
                                    match ($st) {
                                        GeneratedTaskStatus::Done =>
                                            'bg-emerald-100 text-emerald-900 dark:bg-emerald-950/50 dark:text-emerald-200',
                                        GeneratedTaskStatus::InProgress =>
                                            'bg-sky-100 text-sky-900 dark:bg-sky-950/50 dark:text-sky-200',
                                        GeneratedTaskStatus::Blocked =>
                                            'bg-rose-100 text-rose-900 dark:bg-rose-950/50 dark:text-rose-200',
                                        default =>
                                            'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200',
                                    };
                            @endphp
                            <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium {{ $badge }}">
                                {{ $st->label() }}
                            </span>
                        </div>
                        <p class="text-sm leading-relaxed text-zinc-700 dark:text-zinc-300">
                            {{ $task->description }}
                        </p>
                        <div class="grid gap-2 text-sm sm:grid-cols-2">
                            <p class="text-zinc-600 dark:text-zinc-400">
                                <span class="font-medium text-zinc-800 dark:text-zinc-200">Assigned:</span>
                                {{ $task->assignedTo?->name ?? 'Unassigned' }}
                            </p>
                            <p class="text-zinc-600 dark:text-zinc-400">
                                <span class="font-medium text-zinc-800 dark:text-zinc-200">Stack:</span>
                                @if (! empty($task->technology_stack))
                                    <span class="flex flex-wrap gap-1.5 pt-0.5">
                                        @foreach ($task->technology_stack as $tech)
                                            <span class="rounded-md bg-zinc-100 px-2 py-0.5 text-xs text-zinc-800 dark:bg-zinc-900 dark:text-zinc-200">{{ $tech }}</span>
                                        @endforeach
                                    </span>
                                @else
                                    <span class="text-zinc-500">—</span>
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="flex shrink-0 flex-wrap gap-2 border-t border-zinc-100 pt-4 lg:border-0 lg:border-l lg:border-zinc-100 lg:pl-6 lg:pt-0 dark:border-zinc-800 dark:lg:border-zinc-800">
                        <form method="post" action="{{ route('generated-tasks.update', $task) }}" class="inline">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="{{ GeneratedTaskStatus::InProgress->value }}" />
                            <button
                                type="submit"
                                @if ($task->status === GeneratedTaskStatus::InProgress) disabled aria-disabled="true" @endif
                                class="inline-flex items-center justify-center rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm font-medium text-zinc-900 shadow-sm transition hover:bg-zinc-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-zinc-600 dark:bg-zinc-950 dark:text-zinc-100 dark:hover:bg-zinc-900"
                            >
                                In progress
                            </button>
                        </form>
                        <form method="post" action="{{ route('generated-tasks.update', $task) }}" class="inline">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="{{ GeneratedTaskStatus::Done->value }}" />
                            <button
                                type="submit"
                                @if ($task->status === GeneratedTaskStatus::Done) disabled aria-disabled="true" @endif
                                class="inline-flex items-center justify-center rounded-lg bg-zinc-900 px-3 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-zinc-800 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-white"
                            >
                                Completed
                            </button>
                        </form>
                    </div>
                </div>
            </li>
        @endforeach
    </ul>
@endif
