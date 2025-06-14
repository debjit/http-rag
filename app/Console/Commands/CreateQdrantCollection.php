<?php

namespace App\Console\Commands;

use App\Services\QdrantService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

class CreateQdrantCollection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qdrant:create-collection
                            {name? : The name of the collection to create. Defaults to config value.}
                            {--size= : The vector size for the collection. Defaults to config value.}
                            {--distance= : The distance metric (e.g., Cosine, Euclid, Dot). Defaults to config value.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates or updates a collection in Qdrant.';

    protected QdrantService $qdrantService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(QdrantService $qdrantService)
    {
        parent::__construct();
        $this->qdrantService = $qdrantService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Attempting to create/update Qdrant collection...');

        $defaultCollectionName = Config::get('ai.qdrant.default_collection_name', 'my_documents');
        $defaultVectorSize = (int) Config::get('ai.qdrant.default_vector_size', 768);
        $defaultDistanceMetric = Config::get('ai.qdrant.default_distance_metric', 'Cosine');

        $collectionName = $this->argument('name');
        if (! $collectionName) {
            $collectionName = $this->ask('Enter the name for the Qdrant collection', $defaultCollectionName);
        }

        $vectorSizeOption = $this->option('size');
        $vectorSize = $vectorSizeOption ? (int) $vectorSizeOption : (int) $this->ask("Enter the vector size for '{$collectionName}'", $defaultVectorSize);

        $distanceMetricOption = $this->option('distance');
        $distanceMetric = $distanceMetricOption ?: $this->ask("Enter the distance metric for '{$collectionName}' (e.g., Cosine, Euclid, Dot)", $defaultDistanceMetric);

        if (empty($collectionName) || $vectorSize <= 0 || empty($distanceMetric)) {
            $this->error('Collection name, a positive vector size, and distance metric are required.');

            return Command::FAILURE;
        }

        $this->line("Processing collection '{$collectionName}' with vector size {$vectorSize} and distance metric '{$distanceMetric}'...");

        if ($this->qdrantService->createCollection($collectionName, $vectorSize, $distanceMetric)) {
            $this->info("Qdrant collection '{$collectionName}' processed successfully.");

            return Command::SUCCESS;
        }

        $this->error("Failed to process Qdrant collection '{$collectionName}'. Check logs for details.");

        return Command::FAILURE;
    }
}
