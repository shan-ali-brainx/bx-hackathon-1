<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Generated tasks — {{ config('app.name') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet"/>
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <script src="https://cdn.tailwindcss.com"></script>
        @endif
    </head>
    <body class="min-h-screen bg-[#FDFDFC] font-sans text-[#1b1b18] antialiased dark:bg-zinc-950 dark:text-[#EDEDEC]">
        <div class="mx-auto max-w-4xl px-4 py-10 sm:px-6 lg:px-8">
            <header class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight text-balance sm:text-3xl">
                        Generated tasks
                    </h1>
                    <p class="mt-2 text-sm leading-relaxed text-zinc-600 dark:text-zinc-400">
                        Filter by status or assignee, and move work forward with the action buttons on each row.
                    </p>
                </div>
                <a
                    href="{{ url('/') }}"
                    class="text-sm font-medium text-zinc-600 underline decoration-zinc-300 underline-offset-4 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100"
                >
                    ← Home
                </a>
            </header>

            @if (session('flash_success'))
                <div
                    class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900 dark:border-emerald-900/50 dark:bg-emerald-950/35 dark:text-emerald-100"
                    role="status"
                >
                    {{ session('flash_success') }}
                </div>
            @endif

            <section class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-950 sm:p-6">
                <form method="get" action="{{ route('generated-tasks.index') }}" class="flex flex-col gap-4 lg:flex-row lg:flex-wrap lg:items-end">
                    <div class="min-w-0 flex-1 lg:max-w-xs">
                        <label for="filter-status" class="block text-sm font-medium text-zinc-800 dark:text-zinc-200">Status</label>
                        <select
                            id="filter-status"
                            name="status"
                            class="mt-2 w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 shadow-sm focus:border-zinc-900 focus:outline-none focus:ring-2 focus:ring-zinc-900/15 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100"
                            onchange="this.form.submit()"
                        >
                            <option value="">All statuses</option>
                            @foreach ($statusOptions as $opt)
                                <option value="{{ $opt->value }}" @selected(($filters['status'] ?? '') === $opt->value)>
                                    {{ $opt->label() }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="min-w-0 flex-1 lg:max-w-xs">
                        <label for="filter-assignee" class="block text-sm font-medium text-zinc-800 dark:text-zinc-200">Assigned developer</label>
                        <select
                            id="filter-assignee"
                            name="assigned_to"
                            class="mt-2 w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 shadow-sm focus:border-zinc-900 focus:outline-none focus:ring-2 focus:ring-zinc-900/15 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100"
                            onchange="this.form.submit()"
                        >
                            <option value="">Anyone</option>
                            @foreach ($developers as $user)
                                <option value="{{ $user->id }}" @selected(($filters['assigned_to'] ?? '') === (string) $user->id)>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <noscript>
                        <button
                            type="submit"
                            class="rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white dark:bg-zinc-100 dark:text-zinc-900"
                        >
                            Apply filters
                        </button>
                    </noscript>
                </form>
            </section>

            <div class="mt-8">
                <x-generated-task-list :tasks="$tasks" />
            </div>
        </div>
    </body>
</html>
