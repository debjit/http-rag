<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class QdrantService
{
    private string $url;

    private ?string $apiKey;

    private string $defaultCollectionName;

    private int $defaultVectorSize;

    private string $defaultDistanceMetric;

    public function __construct()
    {
        $config = config('ai.qdrant');

        $this->url = rtrim($config['url'] ?? 'http://localhost:6333', '/');
        $this->apiKey = $config['api_key'] ?? null;
        $this->defaultCollectionName = $config['default_collection_name'] ?? 'my_documents';
        $this->defaultVectorSize = (int) ($config['default_vector_size'] ?? 768);
        $this->defaultDistanceMetric = $config['default_distance_metric'] ?? 'Cosine';

        if (empty($this->url)) {
            Log::error('Qdrant URL is not configured.');
            // Optionally throw an exception if the URL is critical for all operations
            // throw new \InvalidArgumentException('Qdrant URL is not configured.');
        }
    }

    /**
     * Creates a new collection in Qdrant.
     *
     * @param  string  $collectionName  The name of the collection.
     * @param  int  $vectorSize  The size of the vectors to be stored.
     * @param  string  $distance  The distance metric (e.g., Cosine, Euclid, Dot).
     * @return bool True on success, false on failure.
     */
    public function createCollection(string $collectionName, int $vectorSize, string $distance): bool
    {
        if (empty($this->url)) {
            Log::error('Qdrant service not configured for creating collection.');

            return false;
        }

        $requestUrl = $this->url.'/collections/'.$collectionName;
        $payload = [
            'vectors' => [
                'size' => $vectorSize,
                'distance' => $distance,
            ],
        ];

        $httpClient = Http::timeout(30);
        if (! empty($this->apiKey)) {
            $httpClient = $httpClient->withHeaders(['api-key' => $this->apiKey]);
        }

        $response = $httpClient->put($requestUrl, $payload);

        if ($response->successful()) {
            Log::info('Qdrant collection created/updated successfully.', ['collection' => $collectionName, 'status' => $response->status()]);

            return true;
        }

        Log::error('Failed to create/update Qdrant collection.', [
            'status' => $response->status(),
            'body' => $response->body(),
            'url' => $requestUrl,
            'collection' => $collectionName,
        ]);

        return false;
    }

    /**
     * Queries Qdrant for points within a collection based on vector similarity.
     *
     * @param  string  $collectionName  The name of the collection to query.
     * @param  array  $vector  The query vector (array of floats).
     * @param  int  $limit  The maximum number of results to return.
     * @param  ?float  $scoreThreshold  Optional minimum similarity score (e.g., 0.7). Only points meeting this threshold will be returned.
     * @param  array  $filter  Optional Qdrant filter conditions. See Qdrant docs for filter structure.
     * @param  bool  $withPayload  Whether to include the payload in the results. Defaults to true.
     * @param  bool  $withVector  Whether to include the vector in the results. Defaults to false.
     * @return array The query result containing points that match the criteria.
     *
     * @throws Exception If the query fails or the response is malformed.
     */
    public function search(
        string $collectionName,
        array $vector,
        int $limit = 3,
        ?float $scoreThreshold = null,
        array $filter = [],
    ): array {
        $url = rtrim($this->url, '/')."/collections/{$collectionName}/points/query"; // Ensure no double slash

        // Basic payload structure for vector search
        $payload = [
            'query' => $vector,
            'limit' => $limit,
            'with_payload' => true, // Defaulting to true, as payload is usually needed.
            'with_vector' => false, // Defaulting to false, as vector is often not needed with results.
        ];

        // Add score threshold if provided
        if ($scoreThreshold !== null) {
            $payload['score_threshold'] = $scoreThreshold;
        }

        // Add filter if provided
        if (! empty($filter)) {
            $payload['filter'] = $filter;
        }

        try {
            $response = Http::withHeaders(array_filter([
                'Content-Type' => 'application/json',
                'api-key' => $this->apiKey ?: null,
            ]))->timeout(30)
                ->post($url, $payload);

            // More detailed error checking
            if ($response->failed()) {
                $statusCode = $response->status();
                $errorBody = $response->body(); // Get raw body for better debugging
                Log::error("Qdrant query failed with status {$statusCode}. Response: {$errorBody}");
                throw new Exception("Qdrant query failed with status {$statusCode}. Response: ".$errorBody);
            }

            $data = $response->json();

            // Check if the expected 'result' key exists and is an array
            if (isset($data['result']) && is_array($data['result'])) {
                Log::info("Qdrant query successful for collection '{$collectionName}'. Found ".count($data['result']).' results.');

                return $data['result']; // Return the array of points
            } else {
                $responseBody = json_encode($data);
                Log::error("Qdrant query failed: Unexpected response format. Body: {$responseBody}");
                throw new Exception('Qdrant query failed: Unexpected response format: '.$responseBody);
            }

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            // Handle connection errors specifically
            Log::error('Qdrant Connection Error: '.$e->getMessage());
            throw new Exception('Could not connect to Qdrant: '.$e->getMessage(), 0, $e);
        } catch (Exception $e) {
            // Log other general exceptions if not already logged
            if (! str_contains($e->getMessage(), 'Qdrant query failed')) { // Avoid double logging known failures
                Log::error('Qdrant Query Error: '.$e->getMessage());
            }
            // Re-throw the original exception to allow for specific handling upstream
            throw $e;
        }
    }

    /**
     * Upserts points into a Qdrant collection.
     *
     * @param  string  $collectionName  The name of the collection.
     * @param  array  $points  An array of points to upsert.  Each point should be an
     *                         associative array with 'id', 'vector', and optionally 'payload' keys.
     * @return void
     *
     * @throws Exception
     */
    public function upsertPoints(string $collectionName, array $points): array
    {
        $url = "{$this->url}/collections/{$collectionName}/points";

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'api-key' => $this->apiKey,
            ])->put($url, [
                'points' => $points,
            ]);

            $data = $response->json();

            if ($response->failed()) {
                throw new Exception('Qdrant upsert failed: '.json_encode($data));
            }

            return $data;
            // Qdrant returns {} on success
            // if (empty($data))
            //     return;
            // else
            //      throw new Exception("Qdrant upsert failed: Unexpected response format" . json_encode($data));

        } catch (Exception $e) {
            // Log the error
            Log::error('Qdrant Upsert Error: '.$e->getMessage());
            throw $e; // Re-throw to allow for custom handling
        }
    }
}
