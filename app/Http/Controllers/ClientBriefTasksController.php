<?php

namespace App\Http\Controllers;

use App\Http\Requests\ClarifyBriefRequest;
use App\Http\Requests\GenerateTasksRequest;
use App\Services\ClientBriefClarificationMerger;
use App\Services\ClientBriefParser;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Throwable;

class ClientBriefTasksController extends Controller
{
    public function create(): View
    {
        return view('client-brief-tasks');
    }

    public function store(GenerateTasksRequest $request, ClientBriefParser $parser): RedirectResponse|View
    {
        $brief = $request->validated('brief');

        try {
            $result = $parser->parse($brief);
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with('generationError', 'Task generation failed. Please try again later.');
        }

        if (count($result->ambiguities) > 0) {
            session([
                'client_brief_clarification' => [
                    'original_brief' => $brief,
                    'effective_brief' => $brief,
                    'pending_ambiguities' => array_map(
                        fn ($a) => [
                            'topic' => $a->topic,
                            'what_is_unclear' => $a->whatIsUnclear,
                            'suggested_question' => $a->suggestedQuestion,
                        ],
                        $result->ambiguities
                    ),
                    'round' => 1,
                ],
            ]);
        } else {
            session()->forget('client_brief_clarification');
        }

        return view('client-brief-tasks', [
            'result' => $result,
            'brief' => $brief,
            'needsClarification' => count($result->ambiguities) > 0,
        ]);
    }

    public function clarify(
        ClarifyBriefRequest $request,
        ClientBriefParser $parser,
        ClientBriefClarificationMerger $merger,
    ): RedirectResponse|View {
        $pending = session('client_brief_clarification');

        if (! is_array($pending) || empty($pending['pending_ambiguities'])) {
            return redirect()
                ->route('client-brief.create')
                ->with('generationError', 'Clarification session expired. Submit your client brief again.');
        }

        /** @var array<int, string> $answers */
        $answers = $request->validated('clarifications');

        $merged = $merger->merge(
            (string) $pending['effective_brief'],
            $pending['pending_ambiguities'],
            $answers,
            (int) ($pending['round'] ?? 1)
        );

        try {
            $result = $parser->parse($merged);
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with('generationError', 'Task generation failed after clarifications. Please try again later.');
        }

        $originalBrief = (string) $pending['original_brief'];

        if (count($result->ambiguities) > 0) {
            session([
                'client_brief_clarification' => [
                    'original_brief' => $originalBrief,
                    'effective_brief' => $merged,
                    'pending_ambiguities' => array_map(
                        fn ($a) => [
                            'topic' => $a->topic,
                            'what_is_unclear' => $a->whatIsUnclear,
                            'suggested_question' => $a->suggestedQuestion,
                        ],
                        $result->ambiguities
                    ),
                    'round' => ((int) ($pending['round'] ?? 1)) + 1,
                ],
            ]);
        } else {
            session()->forget('client_brief_clarification');
        }

        return view('client-brief-tasks', [
            'result' => $result,
            'brief' => $originalBrief,
            'needsClarification' => count($result->ambiguities) > 0,
        ]);
    }
}
