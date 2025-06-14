<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class AiServices
{
    private string $baseUrl;

    private string $apiKey;

    private string $defaultModel;

    private string $defaultEmbeddingModel;

    public function __construct()
    {
        $config = config('ai.openai_compatible_chat');

        $this->apiKey = $config['api_key'] ?? '';
        $this->baseUrl = rtrim($config['base_uri'] ?? '', '/');
        $this->defaultModel = $config['default_model'] ?? 'llama-3-8b-instruct';
        $this->defaultEmbeddingModel = $config['embeding_model'] ?? ''; // Matches key in config

        if (empty($this->apiKey) || empty($this->baseUrl)) {
            Log::error('OpenAI compatible API key or base URI is not configured.');
            // Optionally throw an exception here if these are critical
            // throw new \InvalidArgumentException('OpenAI compatible API key or base URI is not configured.');
        }

        if (empty($this->defaultEmbeddingModel)) {
            Log::warn('OpenAI compatible embedding model is not configured in AiServices. Embedding functionality might be affected.');
            // Optionally throw an exception if embedding is critical for this service
            // throw new \InvalidArgumentException('OpenAI compatible embedding model is not configured.');
        }
    }

    /**
     * Sends a message or a series of messages to the chat API.
     *
     * @param  array  $messages  An array of message objects (e.g., [['role' => 'user', 'content' => 'Hello']])
     * @param  string|null  $model  The model to use for this request, overrides the default if provided.
     * @return array|null The API resingponse as an associative array or null on failure.
     */
    public function chat(array $messages, ?string $model = null): ?array
    {
        if (empty($this->apiKey) || empty($this->baseUrl)) {
            Log::error('AI Service not configured for chat.');

            return null;
        }

        $modelToUse = $model ?? $this->defaultModel;
        $url = $this->baseUrl.'/chat/completions';

        $response = Http::withToken($this->apiKey)
            ->timeout(360) // Optional: set a timeout
            ->post($url, [
                'model' => $modelToUse,
                'messages' => $messages,
            ]);

        if ($response->failed()) {
            Log::error('OpenAI compatible API request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'url' => $url,
            ]);

            return null;
        }

        return $response->json();
    }

    /**
     * Generates an embedding for the given content.
     *
     * @param  string  $content  The text content to embed.
     * @param  string|null  $model  The model to usse for this request, overrides the default embedding model if provided.
     * @return array|null The embedding vector as an array or null on failure.
     */
    public function embed(string $content, ?string $model = null): ?array
    {
        if (empty($this->apiKey) || empty($this->baseUrl) || empty($this->defaultEmbeddingModel)) {
            Log::error('AI Service not configured for embedding. API key, base URL, or default embedding model is missing.');

            return null;
        }

        $modelToUse = $model ?? $this->defaultEmbeddingModel;
        if (empty($modelToUse)) { // Double check after potential override
            Log::error('No embedding model specified for AI Service embedding.');

            return null;
        }
        $url = $this->baseUrl.'/embeddings';

        $response = Http::withToken($this->apiKey)
            // ->timeout(30) // Optional: set a timeout
            ->post($url, [
                'input' => $content,
                'model' => $modelToUse,
            ]);

        if ($response->failed()) {
            Log::error('OpenAI compatible embedding API request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'url' => $url,
            ]);

            return null;
        }

        return $response->json('data.0.embedding');
    }
}
