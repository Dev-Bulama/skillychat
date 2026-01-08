<?php

namespace App\Services\AI;

use Orhanerday\OpenAi\OpenAi;
use Illuminate\Support\Facades\Log;
use Exception;

class OpenAIService implements AIProviderInterface
{
    protected OpenAi $client;
    protected string $apiKey;
    protected string $model;

    public function __construct(string $apiKey, string $model = 'gpt-4o-mini')
    {
        $this->apiKey = $apiKey;
        $this->model = $model;
        $this->client = new OpenAi($apiKey);
    }

    public function chat(array $messages, array $options = []): array
    {
        try {
            $startTime = microtime(true);

            $params = [
                'model' => $options['model'] ?? $this->model,
                'messages' => $messages,
                'temperature' => $options['temperature'] ?? 0.7,
                'max_tokens' => $options['max_tokens'] ?? 1000,
            ];

            $response = $this->client->chat($params);
            $result = json_decode($response, true);

            if (isset($result['error'])) {
                throw new Exception($result['error']['message'] ?? 'OpenAI API error');
            }

            $endTime = microtime(true);
            $responseTime = round(($endTime - $startTime), 2);

            $message = $result['choices'][0]['message']['content'] ?? '';
            $tokensUsed = $result['usage']['total_tokens'] ?? 0;

            return [
                'success' => true,
                'message' => $message,
                'confidence' => $this->calculateConfidence($result),
                'tokens_used' => $tokensUsed,
                'cost' => $this->calculateCost($tokensUsed, $params['model']),
                'response_time' => $responseTime,
                'provider' => 'openai',
                'model' => $params['model'],
                'raw_response' => $result,
            ];
        } catch (Exception $e) {
            Log::error('OpenAI chat error: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => '',
                'error' => $e->getMessage(),
                'confidence' => 0,
                'tokens_used' => 0,
                'cost' => 0,
                'response_time' => 0,
                'provider' => 'openai',
            ];
        }
    }

    public function analyzeImage(string $imagePath, string $prompt, array $options = []): array
    {
        try {
            $startTime = microtime(true);

            $messages = [
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => $prompt,
                        ],
                        [
                            'type' => 'image_url',
                            'image_url' => [
                                'url' => $imagePath,
                            ],
                        ],
                    ],
                ],
            ];

            $params = [
                'model' => 'gpt-4o-mini',
                'messages' => $messages,
                'max_tokens' => $options['max_tokens'] ?? 500,
            ];

            $response = $this->client->chat($params);
            $result = json_decode($response, true);

            if (isset($result['error'])) {
                throw new Exception($result['error']['message'] ?? 'OpenAI Vision API error');
            }

            $endTime = microtime(true);
            $responseTime = round(($endTime - $startTime), 2);

            $message = $result['choices'][0]['message']['content'] ?? '';
            $tokensUsed = $result['usage']['total_tokens'] ?? 0;

            return [
                'success' => true,
                'message' => $message,
                'tokens_used' => $tokensUsed,
                'cost' => $this->calculateCost($tokensUsed, 'gpt-4o-mini'),
                'response_time' => $responseTime,
                'provider' => 'openai',
                'model' => 'gpt-4o-mini',
            ];
        } catch (Exception $e) {
            Log::error('OpenAI vision error: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => '',
                'error' => $e->getMessage(),
                'tokens_used' => 0,
                'cost' => 0,
            ];
        }
    }

    public function generateEmbedding(string $text): array
    {
        try {
            $response = $this->client->embeddings([
                'model' => 'text-embedding-ada-002',
                'input' => $text,
            ]);

            $result = json_decode($response, true);

            if (isset($result['error'])) {
                throw new Exception($result['error']['message'] ?? 'OpenAI Embedding API error');
            }

            return [
                'success' => true,
                'embedding' => $result['data'][0]['embedding'] ?? [],
                'tokens_used' => $result['usage']['total_tokens'] ?? 0,
            ];
        } catch (Exception $e) {
            Log::error('OpenAI embedding error: ' . $e->getMessage());

            return [
                'success' => false,
                'embedding' => [],
                'error' => $e->getMessage(),
            ];
        }
    }

    public function verifyApiKey(): bool
    {
        try {
            $response = $this->client->listModels();
            $result = json_decode($response, true);

            return !isset($result['error']);
        } catch (Exception $e) {
            Log::error('OpenAI key verification failed: ' . $e->getMessage());
            return false;
        }
    }

    public function getProviderName(): string
    {
        return 'openai';
    }

    public function supportsVision(): bool
    {
        return true;
    }

    public function calculateCost(int $tokensUsed, string $model = 'gpt-4o-mini'): float
    {
        $pricing = [
            'gpt-4o' => ['input' => 2.50 / 1000000, 'output' => 10.00 / 1000000],
            'gpt-4o-mini' => ['input' => 0.150 / 1000000, 'output' => 0.600 / 1000000],
            'gpt-4-turbo' => ['input' => 10.00 / 1000000, 'output' => 30.00 / 1000000],
            'gpt-3.5-turbo' => ['input' => 0.50 / 1000000, 'output' => 1.50 / 1000000],
        ];

        if (!isset($pricing[$model])) {
            $model = 'gpt-4o-mini';
        }

        $estimatedInputTokens = $tokensUsed * 0.7;
        $estimatedOutputTokens = $tokensUsed * 0.3;

        $cost = ($estimatedInputTokens * $pricing[$model]['input']) +
                ($estimatedOutputTokens * $pricing[$model]['output']);

        return round($cost, 6);
    }

    protected function calculateConfidence(array $result): float
    {
        if (isset($result['choices'][0]['finish_reason']) && $result['choices'][0]['finish_reason'] === 'stop') {
            return 0.95;
        }

        if (isset($result['choices'][0]['finish_reason']) && $result['choices'][0]['finish_reason'] === 'length') {
            return 0.70;
        }

        return 0.80;
    }
}
