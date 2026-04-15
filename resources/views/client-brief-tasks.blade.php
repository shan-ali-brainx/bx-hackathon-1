<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Client brief → tasks — {{ config('app.name') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet"/>
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            {{-- Fallback when only `php artisan serve` runs (no Vite). For production use `npm run build`. --}}
            <script src="https://cdn.tailwindcss.com"></script>
        @endif
    </head>
    <body class="min-h-screen bg-[#FDFDFC] font-sans text-[#1b1b18] antialiased dark:bg-zinc-950 dark:text-[#EDEDEC]">
        <div class="mx-auto max-w-3xl px-4 py-10 sm:px-6 lg:px-8">
            <header class="mb-8">
                <h1 class="text-2xl font-semibold tracking-tight text-balance sm:text-3xl">
                    Turn a client brief into tasks
                </h1>
                <p class="mt-2 text-sm leading-relaxed text-zinc-600 dark:text-zinc-400">
                    Paste the raw brief below. We’ll extract actionable tasks and list anything that still needs
                    clarification from the client.
                </p>
            </header>

            <section class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-950 sm:p-6">
                <form method="post" action="{{ route('client-brief.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label for="brief" class="block text-sm font-medium text-zinc-800 dark:text-zinc-200">
                            Client brief
                        </label>
                        <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-500">
                            Minimum 20 characters (server validation).
                        </p>
                        <textarea
                            id="brief"
                            name="brief"
                            rows="10"
                            required
                            class="mt-3 w-full rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-sm leading-relaxed text-zinc-900 shadow-sm outline-none ring-zinc-400/30 placeholder:text-zinc-400 focus:border-zinc-900 focus:ring-2 focus:ring-zinc-900/15 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100 dark:placeholder:text-zinc-600 dark:focus:border-zinc-300 dark:focus:ring-zinc-300/20"
                            placeholder="Example: We need a new landing page with pricing, testimonials, and a signup flow..."
                        >{{ old('brief', $brief ?? null) }}</textarea>
                        @error('brief')
                            <p class="mt-2 text-sm text-rose-700 dark:text-rose-300">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <button
                            type="submit"
                            class="inline-flex items-center justify-center rounded-lg bg-zinc-900 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-zinc-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-zinc-900 focus-visible:ring-offset-2 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-white dark:focus-visible:ring-zinc-100"
                        >
                            Generate tasks
                        </button>
                        <p class="text-xs text-zinc-500 dark:text-zinc-500">
                            Submits to the server; the page reloads when the response is ready.
                        </p>
                    </div>
                </form>
            </section>

            @if (session('generationError'))
                <div
                    class="mt-6 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-900 dark:border-rose-900/60 dark:bg-rose-950/40 dark:text-rose-100"
                    role="alert"
                >
                    {{ session('generationError') }}
                </div>
            @endif

            @isset($result)
                @php
                    $needsClarification = $needsClarification ?? false;
                @endphp
                <section class="mt-10 space-y-8">
                    @if ($result->briefSummary)
                        <div class="rounded-xl border border-zinc-200 bg-zinc-50 px-5 py-4 dark:border-zinc-800 dark:bg-zinc-900/40">
                            <h2 class="text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                                Summary
                            </h2>
                            <p class="mt-2 text-sm leading-relaxed text-zinc-800 dark:text-zinc-200">
                                {{ $result->briefSummary }}
                            </p>
                        </div>
                    @endif

                    @if ($needsClarification && count($result->ambiguities))
                        <div class="rounded-xl border border-indigo-200 bg-indigo-50/90 p-5 shadow-sm dark:border-indigo-900/60 dark:bg-indigo-950/35 sm:p-6">
                            <h2 class="text-lg font-semibold text-indigo-950 dark:text-indigo-100">
                                Clarify these points
                            </h2>
                            <p class="mt-2 text-sm leading-relaxed text-indigo-900/90 dark:text-indigo-200/90">
                                The model could not fully pin down the items below. Answer each prompt so we can refine the
                                task list—your responses are merged into the brief and generation runs again.
                            </p>

                            <form method="post" action="{{ route('client-brief.clarify') }}" class="mt-6 space-y-6">
                                @csrf
                                @foreach ($result->ambiguities as $index => $item)
                                    <div class="rounded-lg border border-indigo-200/80 bg-white/90 p-4 dark:border-indigo-900/50 dark:bg-zinc-950/60">
                                        <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600 dark:text-indigo-300/90">
                                            {{ $item->topic }}
                                        </p>
                                        <p class="mt-2 text-sm text-zinc-700 dark:text-zinc-300">
                                            {{ $item->whatIsUnclear }}
                                        </p>
                                        <label for="clarification-{{ $index }}" class="mt-4 block text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                            {{ $item->suggestedQuestion }}
                                        </label>
                                        <textarea
                                            id="clarification-{{ $index }}"
                                            name="clarifications[]"
                                            rows="3"
                                            required
                                            class="mt-2 w-full rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-sm leading-relaxed text-zinc-900 shadow-sm outline-none ring-zinc-400/30 placeholder:text-zinc-400 focus:border-indigo-700 focus:ring-2 focus:ring-indigo-700/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100 dark:focus:border-indigo-400 dark:focus:ring-indigo-400/25"
                                            placeholder="Type your answer…"
                                        >{{ old('clarifications.'.$index) }}</textarea>
                                        @error('clarifications.'.$index)
                                            <p class="mt-2 text-sm text-rose-700 dark:text-rose-300">{{ $message }}</p>
                                        @enderror
                                    </div>
                                @endforeach
                                @error('clarifications')
                                    <p class="text-sm text-rose-700 dark:text-rose-300">{{ $message }}</p>
                                @enderror

                                <div class="flex flex-wrap items-center gap-3">
                                    <button
                                        type="submit"
                                        class="inline-flex items-center justify-center rounded-lg bg-indigo-900 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-indigo-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-900 focus-visible:ring-offset-2 dark:bg-indigo-300 dark:text-indigo-950 dark:hover:bg-indigo-200"
                                    >
                                        Apply clarifications &amp; regenerate tasks
                                    </button>
                                    <p class="text-xs text-indigo-900/80 dark:text-indigo-200/80">
                                        Your answers are appended to the brief and sent through the model again.
                                    </p>
                                </div>
                            </form>
                        </div>
                    @endif

                    @if (count($result->tasks))
                        <div>
                            <div class="flex flex-wrap items-end justify-between gap-2">
                                <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-50">Action items</h2>
                                @if ($needsClarification)
                                    <span class="inline-flex items-center rounded-full border border-amber-300/90 bg-amber-100 px-3 py-1 text-xs font-medium text-amber-950 dark:border-amber-900/50 dark:bg-amber-950/40 dark:text-amber-100">
                                        Draft — may change after you clarify
                                    </span>
                                @endif
                            </div>
                            <ol class="mt-4 space-y-4">
                                @foreach ($result->tasks as $index => $task)
                                    <li class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-950">
                                        <div class="flex flex-wrap items-start justify-between gap-3">
                                            <div class="min-w-0 flex-1">
                                                <p class="text-[11px] font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">
                                                    Task {{ $index + 1 }}
                                                </p>
                                                <h3 class="mt-1 text-base font-semibold text-zinc-900 dark:text-zinc-50">
                                                    {{ $task->title }}
                                                </h3>
                                            </div>
                                            @if ($task->estimatedComplexity)
                                                @php
                                                    $cx = strtolower((string) $task->estimatedComplexity);
                                                    $badge =
                                                        match ($cx) {
                                                            'high' => 'bg-rose-100 text-rose-800 dark:bg-rose-950/50 dark:text-rose-200',
                                                            'medium' => 'bg-amber-100 text-amber-900 dark:bg-amber-950/50 dark:text-amber-100',
                                                            'low' => 'bg-emerald-100 text-emerald-900 dark:bg-emerald-950/50 dark:text-emerald-100',
                                                            default =>
                                                                'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300',
                                                        };
                                                @endphp
                                                <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-medium {{ $badge }}">
                                                    {{ $task->estimatedComplexity }} effort
                                                </span>
                                            @endif
                                        </div>
                                        <p class="mt-3 text-sm leading-relaxed text-zinc-700 dark:text-zinc-300">
                                            {{ $task->description }}
                                        </p>
                                        @if ($task->acceptanceCriteria)
                                            <div class="mt-4 rounded-lg bg-zinc-50 px-3 py-2.5 text-sm dark:bg-zinc-900/60">
                                                <span class="font-medium text-zinc-800 dark:text-zinc-200">Acceptance criteria: </span>
                                                <span class="text-zinc-700 dark:text-zinc-300">{{ $task->acceptanceCriteria }}</span>
                                            </div>
                                        @endif
                                    </li>
                                @endforeach
                            </ol>
                        </div>
                    @else
                        <p class="rounded-lg border border-dashed border-zinc-300 px-4 py-3 text-sm text-zinc-600 dark:border-zinc-700 dark:text-zinc-400">
                            No concrete tasks were returned. Try adding more detail to the brief.
                        </p>
                    @endif

                    @if (count($result->ambiguities) && ! $needsClarification)
                        <div class="space-y-3">
                            <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-50">
                                Still open (optional follow-up with the client)
                            </h2>
                            <ul class="space-y-3">
                                @foreach ($result->ambiguities as $item)
                                    <li class="rounded-xl border border-amber-200/80 bg-amber-50/80 px-5 py-4 dark:border-amber-900/50 dark:bg-amber-950/25">
                                        <p class="text-sm font-semibold text-amber-950 dark:text-amber-100">
                                            {{ $item->topic }}
                                        </p>
                                        <p class="mt-2 text-sm leading-relaxed text-amber-950/90 dark:text-amber-50/90">
                                            {{ $item->whatIsUnclear }}
                                        </p>
                                        <p class="mt-3 border-t border-amber-200/80 pt-3 text-sm font-medium text-amber-950 dark:border-amber-900/40 dark:text-amber-50">
                                            Suggested question:
                                            <span class="font-normal text-amber-950/95 dark:text-amber-100/95">{{ $item->suggestedQuestion }}</span>
                                        </p>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </section>
            @endisset
        </div>
    </body>
</html>
