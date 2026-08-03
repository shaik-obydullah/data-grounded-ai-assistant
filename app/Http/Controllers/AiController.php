<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Models\Company;
use App\Services\OllamaService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AiController extends Controller
{
    protected OllamaService $ollama;

    public function __construct(OllamaService $ollama)
    {
        $this->ollama = $ollama;
    }

    public function index(Request $request)
    {
        $sessionId = $request->cookie('chat_session', (string) Str::uuid());
        $models = $this->ollama->listModels();
        $messages = ChatMessage::where('session_id', $sessionId)
            ->orderBy('created_at')
            ->get();

        return response(view('ai.index', compact('models', 'messages', 'sessionId')))
            ->cookie('chat_session', $sessionId, 60 * 24 * 30);
    }

    public function ask(Request $request)
    {
        set_time_limit(300);

        try {
            $request->validate([
                'question' => 'required|string|max:2000',
                'model' => 'nullable|string|max:100',
                'session_id' => 'required|string',
            ]);

            $model = $request->input('model', 'tinyllama');
            $sessionId = $request->session_id;

            $userMessage = ChatMessage::create([
                'session_id' => $sessionId,
                'role' => 'user',
                'content' => $request->question,
                'model' => $model,
            ]);

            $history = ChatMessage::where('session_id', $sessionId)
                ->orderBy('created_at')
                ->get()
                ->map(fn($m) => ['role' => $m->role, 'content' => $m->content])
                ->toArray();

            $stats = [
                'total_companies' => Company::count(),
                'by_route' => Company::selectRaw('route, count(*) as count')->groupBy('route')->pluck('count', 'route')->toArray(),
                'by_type' => Company::selectRaw('type_rating, count(*) as count')->groupBy('type_rating')->pluck('count', 'type_rating')->toArray(),
                'recent_changes' => [
                    'new' => Company::where('change_type', 'new')->count(),
                    'updated' => Company::where('change_type', 'updated')->count(),
                    'removed' => Company::where('change_type', 'removed')->count(),
                ],
            ];

            $sample = Company::select('organisation_name', 'town_city', 'county', 'type_rating', 'route')
                ->limit(3)
                ->get()
                ->toArray();

            $systemPrompt = "You are a data analyst for a data grounded AI assistant. "
                . "Current dataset has " . number_format($stats['total_companies']) . " companies. "
                . "Stats: " . json_encode($stats) . ". "
                . "Sample records: " . json_encode($sample) . ". "
                . "Answer concisely using the data.";

            $answer = $this->ollama->chat($systemPrompt, $history, $model);

            $assistantMessage = ChatMessage::create([
                'session_id' => $sessionId,
                'role' => 'assistant',
                'content' => $answer,
                'model' => $model,
            ]);

            return response()->json([
                'answer' => $answer,
                'message_id' => $assistantMessage->id,
                'user_message_id' => $userMessage->id,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'answer' => 'Error: ' . $e->getMessage(),
                'error' => true,
            ]);
        }
    }

    public function history(Request $request)
    {
        $sessionId = $request->cookie('chat_session');
        if (!$sessionId) {
            return response()->json([]);
        }

        $messages = ChatMessage::where('session_id', $sessionId)
            ->orderBy('created_at')
            ->get()
            ->map(fn($m) => [
                'id' => $m->id,
                'role' => $m->role,
                'content' => $m->content,
                'model' => $m->model,
                'created_at' => $m->created_at->toISOString(),
            ]);

        return response()->json($messages);
    }

    public function clear(Request $request)
    {
        $sessionId = $request->cookie('chat_session');
        if ($sessionId) {
            ChatMessage::where('session_id', $sessionId)->delete();
        }
        return redirect()->route('ai.index')->with('success', 'Chat cleared');
    }

    public function pullModel(Request $request)
    {
        $request->validate(['model' => 'required|string|max:100']);
        $result = $this->ollama->pullModel($request->model);
        return back()->with('model_result', $result);
    }
}
