<?php

namespace App\Console\Commands;

use App\Services\AiServices;
use Illuminate\Console\Command;

class TestAiEmbedding extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ai:test:embedding';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tests the AI embedding service with user-provided text.';

    protected AiServices $aiService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(AiServices $aiService)
    {
        parent::__construct();
        $this->aiService = $aiService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Testing AI Embedding Service...');
        $content = $this->ask('Enter the text content to embed:');

        $embedding = $this->aiService->embed($content ?? 'This is a test sentence.');

        if ($embedding) {
            $this->info('API Response (Embedding Vector - first 5 elements):');
            $this->line(json_encode(array_slice($embedding, 0, 5), JSON_PRETTY_PRINT).' ... (truncated)');
        } else {
            $this->error('Failed to get an embedding from the AI service. Check logs for details.');
        }

        return Command::SUCCESS;
    }
}
