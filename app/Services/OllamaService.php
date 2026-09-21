<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class OllamaService
{
    protected string $host;

    public function __construct()
    {
        $this->host = env('OLLAMA_HOST', 'http://ollama:11434');
    }

    public function ask(string $prompt, string $model = 'tinyllama'): string
    {
        $response = Http::timeout(300)->post("{$this->host}/api/generate", [
            'model' => $model,
            'prompt' => $prompt,
            'stream' => false,
        ]);

        if ($response->failed()) {
            return "Error: Unable to reach Ollama. Make sure it's running and a model is pulled ({$response->body()})";
        }

        return $response->json('response', 'No response from model');
    }

    public function chat(string $systemPrompt, array $history, string $model = 'tinyllama'): string
    {
        $messages = [['role' => 'system', 'content' => $systemPrompt]];

        foreach ($history as $msg) {
            $messages[] = ['role' => $msg['role'], 'content' => $msg['content']];
        }

        $response = Http::timeout(300)->post("{$this->host}/api/chat", [
            'model' => $model,
            'messages' => $messages,
            'stream' => false,
        ]);

        if ($response->failed()) {
            return "Error: Unable to reach Ollama ({$response->body()})";
        }

        $message = $response->json('message');
        return $message['content'] ?? 'No response from model';
    }

    public function listModels(): array
    {
        $response = Http::get("{$this->host}/api/tags");
        if ($response->successful()) {
            return $response->json('models', []);
        }
        return [];
    }

    public function pullModel(string $model): string
    {
        $response = Http::timeout(300)->post("{$this->host}/api/pull", [
            'model' => $model,
            'stream' => false,
        ]);

        if ($response->failed()) {
            return "Failed to pull model: {$response->body()}";
        }

        return "Model {$model} pulled successfully";
    }
}
