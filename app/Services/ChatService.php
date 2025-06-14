<?php

namespace App\Services;

use App\Models\Chat;
use App\Models\ChatMessage;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Config;

class ChatService
{
    protected AiServices $aiService;

    protected QdrantService $qdrantService;

    protected string $defaultSystemPrompt;

    protected string $qdrantCollectionName;

    public function __construct(AiServices $aiService, QdrantService $qdrantService)
    {
        $this->aiService = $aiService;
        $this->qdrantService = $qdrantService;
        $this->defaultSystemPrompt = Config::get('ai.chat.default_system_prompt', 'You are a helpful assistant.');
        $this->qdrantCollectionName = Config::get('ai.qdrant.default_collection_name', 'my_documents');
    }

    public function listChats(int $perPage = 15): LengthAwarePaginator
    {
        // Eager load latest message for preview if needed, or handle in resource
        return Chat::latest()->paginate($perPage);
    }

    public function createChat(array $data): Chat
    {
        return Chat::create([
            'title' => $data['title'] ?? null,
            // 'user_id' => auth()->id(), // If using authentication
        ]);
    }

    public function findChat(string $chatId): ?Chat
    {
        return Chat::with('messages')->findOrFail($chatId);
    }

    public function updateChat(Chat $chat, array $data): Chat
    {
        $chat->update([
            'title' => $data['title'] ?? $chat->title,
        ]);

        return $chat;
    }

    public function deleteChat(Chat $chat): bool
    {
        return $chat->delete();
    }

    public function addMessageToChat(Chat $chat, string $role, string $content, ?array $metadata = null): ChatMessage
    {
        return $chat->messages()->create([
            'role' => $role,
            'content' => $content,
            'metadata' => $metadata,
        ]);
    }

    public function getAiReply(Chat $chat, string $userMessageContent): ChatMessage
    {
        // Store the user's message first
        $userMessage = $this->addMessageToChat($chat, 'user', $userMessageContent);

        // --- RAG Process Start ---
        // 1. Embed the user's question
        $questionEmbedding = $this->aiService->embed($userMessageContent);

        if (! $questionEmbedding) {
            logger()->error('Failed to embed the question for chat: '.$chat->id);

            return $this->addMessageToChat($chat, 'assistant', 'Sorry, I could not process your question. Embedding failed.', ['error' => 'Embedding failed']);
        }

        // 2. Search Qdrant for relevant documents
        $topK = 3; // You might want to make this configurable
        try {
            $searchResults = $this->qdrantService->search($this->qdrantCollectionName, $questionEmbedding, $topK);
        } catch (\Exception $e) {
            logger()->error('Error searching Qdrant for chat: '.$chat->id.' - '.$e->getMessage());

            return $this->addMessageToChat($chat, 'assistant', 'Sorry, I encountered an error while searching for information.', ['error' => 'Qdrant search failed']);
        }

        $context = '';
        if ($searchResults && ! empty($searchResults['points'])) {
            $contextSnippets = [];
            foreach ($searchResults['points'] as $hit) {
                if (! empty($hit['payload']['searchable_content'])) {
                    $snippet = "Source Document (ID: {$hit['id']}, Score: ".round($hit['score'], 4)."):\n";
                    if (! empty($hit['payload']['title']) && strpos($hit['payload']['searchable_content'], "Title: {$hit['payload']['title']}") === false) {
                        $snippet .= "Title: {$hit['payload']['title']}\n";
                    }
                    $snippet .= $hit['payload']['searchable_content'];
                    $contextSnippets[] = $snippet;
                }
            }
            if (! empty($contextSnippets)) {
                $context = implode("\n\n---\n\n", $contextSnippets);
            }
        }

        // Prepare messages for AI
        $messagesForAi = [];
        $messagesForAi[] = ['role' => 'system', 'content' => $this->defaultSystemPrompt];

        // Construct the user prompt with context
        $userPromptWithContext = "Question: {$userMessageContent}";
        if (! empty($context)) {
            $userPromptWithContext = "Context:\n---\n{$context}\n---\n\n".$userPromptWithContext;
        } else {
            // If no context found, inform the AI it cannot answer based on provided info
            $userPromptWithContext .= "\n\n(No relevant information found in knowledge base to answer this question.)";
        }

        // Add the user's message (with context) to the messages for AI
        $messagesForAi[] = ['role' => 'user', 'content' => $userPromptWithContext];

        // Reload chat with messages to ensure we have the latest, including the one just added
        // Note: We only need the *user's* message for embedding and context.
        // The full chat history is not typically sent to the LLM in a pure RAG setup
        // unless multi-turn conversation with context is desired.
        // For now, we'll send only the system prompt and the current user message with context.
        // If multi-turn RAG is needed, the loop below would be re-enabled and context
        // would need to be managed for each turn.
        // $chat->load('messages');
        // foreach ($chat->messages as $message) {
        //     $messagesForAi[] = ['role' => $message->role, 'content' => $message->content];
        // }

        $aiResponse = $this->aiService->chat($messagesForAi);

        $aiContent = 'Sorry, I could not generate a response.';
        $metadata = null;

        if ($aiResponse && isset($aiResponse['choices'][0]['message']['content'])) {
            $aiContent = $aiResponse['choices'][0]['message']['content'];
            // You might want to store other parts of $aiResponse in metadata
            // $metadata = ['usage' => $aiResponse['usage'] ?? null];
        } elseif ($aiResponse) {
            // Log unexpected AI response structure
            logger()->warning('Unexpected AI response structure', ['response' => $aiResponse]);
            $metadata = ['error' => 'Unexpected AI response structure'];
        } else {
            logger()->error('Failed to get AI response for chat: '.$chat->id);
            $metadata = ['error' => 'AI service request failed'];
        }

        return $this->addMessageToChat($chat, 'assistant', $aiContent, $metadata);
    }

    public function getChatMessages(Chat $chat, int $perPage = 30): LengthAwarePaginator
    {
        return $chat->messages()->latest()->paginate($perPage);
    }
}
