<?php

namespace App\Console\Commands;

use App\Services\AiServices;
use Illuminate\Console\Command;

class TestAiChat extends Command
{
    protected $signature = 'ai:test:chat';

    protected $description = 'Tests the AI chat service with a simple prompt.';

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
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Testing AI Chat Service...');
        $question = $this->ask('What is your question?');
        $messages = [
            ['role' => 'system', 'content' => 'You are a helpful assistant.'],
            ['role' => 'user', 'content' => $question ?? 'Hello, who are you?'],
        ];

        $response = $this->aiService->chat($messages);

        if ($response) {
            $this->info('API Response:');
            $this->line(json_encode($response, JSON_PRETTY_PRINT));
        } else {
            $this->error('Failed to get a response from the AI chat service. Check logs for details.');
        }

        return Command::SUCCESS;
    }
}
