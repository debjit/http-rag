<?php

return [
    /*
    |--------------------------------------------------------------------------
    | OpenAI Compatible Chat Service
    |--------------------------------------------------------------------------
    |
    | This configuration is for an OpenAI-compatible chat service.
    | You'll need to provide your API key and the base URI for the
    | service. A default model is also specified.
    |
    */
    'openai_compatible_chat' => [
        'api_key' => env('OPENAI_COMPATIBLE_API_KEY'),
        'base_uri' => env('OPENAI_COMPATIBLE_BASE_URI'),
        'default_model' => env('OPENAI_COMPATIBLE_DEFAULT_MODEL', 'llama-3-8b-instruct'),
        'embeding_model' => env('OPENAI_COMPATIBLE_EMBEDING_MODEL'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Qdrant Vector Database Service
    |--------------------------------------------------------------------------
    */
    'qdrant' => [
        'api_key' => env('QDRANT_API_KEY'), // Optional, depending on your Qdrant setup
        'url' => env('QDRANT_URL', 'http://localhost:6333'),
        'default_collection_name' => env('QDRANT_DEFAULT_COLLECTION_NAME', 'my_documents'),
        'default_vector_size' => (int) env('QDRANT_DEFAULT_VECTOR_SIZE', 768), // Example size, adjust as needed
        'default_distance_metric' => env('QDRANT_DEFAULT_DISTANCE_METRIC', 'Cosine'), // Cosine, Dot, Euclid
    ],

    'chat' => [
        'default_system_prompt' => 'You are a helpful AI assistant. '.
            'DO NOT reference, mention, or explain the context. '.
            "Answer the user's question strictly using the provided context only. ".
            "If the answer is not present in the context, respond with: 'I cannot answer that with the given information.' ".
            'Do not list what can or cannot be answered. '.
            'Only provide a direct and concise answer to the specific question. '.
            'Never add additional commentary, disclaimers, or summaries.',
    ],
];
