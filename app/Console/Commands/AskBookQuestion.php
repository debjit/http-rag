<?php

namespace App\Console\Commands;

use App\Services\AiServices;
use App\Services\QdrantService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

class AskBookQuestion extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ai:ask-book
                            {--top_k=3 : Number of results to fetch from Qdrant.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Asks a question against book data in Qdrant and uses an LLM to generate an answer.';

    protected AiServices $aiService;

    protected QdrantService $qdrantService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(AiServices $aiService, QdrantService $qdrantService)
    {
        parent::__construct();
        $this->aiService = $aiService;
        $this->qdrantService = $qdrantService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting RAG process for books...');

        $collectionName = Config::get('ai.qdrant.default_collection_name', 'my_documents');

        $topK = (int) $this->option('top_k');
        if ($topK <= 0) {
            $this->error('Top K must be a positive integer.');

            return Command::FAILURE;
        }

        $question = $this->ask('What is your question about the books?');
        if (empty($question)) {
            $this->error('Question cannot be empty.');

            return Command::FAILURE;
        }

        $this->line('Embedding your question...');
        $questionEmbedding = $this->aiService->embed($question);

        if (! $questionEmbedding) {
            $this->error('Failed to embed the question. Check AI service logs.');
            logger(json_encode($questionEmbedding));

            return Command::FAILURE;
        }

        $this->line("Searching for relevant documents in '{$collectionName}' (top {$topK})...");
        try {
            $searchResults = $this->qdrantService->search($collectionName, $questionEmbedding);
        } catch (\Exception $e) {
            $this->error('Error searching Qdrant: '.$e->getMessage());

            return Command::FAILURE;
        }

        if (! $searchResults || empty($searchResults['points'])) {
            $this->info('No relevant book information found in Qdrant to answer your question.');

            return Command::SUCCESS;
        }

        $this->line('Building context from search results...');
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

        if (empty($contextSnippets)) {
            $this->info('No usable content found in the retrieved documents to form a context.');
            $this->line('Could not find specific information to answer your question based on the retrieved documents.');

            return Command::SUCCESS;
        }

        $context = implode("\n\n---\n\n", $contextSnippets);

        $this->info('Context built. Asking LLM...');

        $systemPrompt = 'You are a helpful AI assistant.'
            ."Your task is to answer the user's question based *only* on the provided context."
            .'If the context does not contain the answer, you *must* state that you cannot answer the question with the given information.'
            .'Do *not* use any external knowledge.';
        $userPrompt = "Context:\n---\n{$context}\n---\n\nQuestion: {$question}";

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userPrompt],
        ];

        $llmResponse = $this->aiService->chat($messages);

        if ($llmResponse) {
            // Assuming $llmResponse is the structured API response.
            // Adjust this based on your AiServices::chat() return format.
            // For OpenAI-like responses:
            $responseText = $llmResponse['choices'][0]['message']['content'] ?? null;
            if ($responseText) {
                $this->info('AI Response:');
                $this->line($responseText);
            } else {
                $this->warn('Could not extract message content from AI response. Raw response:');
                $this->line(json_encode($llmResponse, JSON_PRETTY_PRINT));
            }
        } else {
            $this->error('Failed to get a response from the AI chat service. Check logs.');

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
