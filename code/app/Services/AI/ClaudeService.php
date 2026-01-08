<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class ClaudeService implements AIProviderInterface
{
    protected string $apiKey;
    protected string $model;
    protected string $baseUrl = 'https://api.anthropic.com/v1';
    protected string $apiVersion = '2023-06-01';

    public function __construct(string $apiKey, string $model = 'claude-3-5-haiku-20241022')
    {
        $this->apiKey = $apiKey;
        $this->model = $model;
    }

    public function chat(array $messages, array $options = []): array
    {
        try {
            $startTime = microtime(true);

            $systemMessage = '';
            $conversationMessages = [];

            foreach ($messages as $message) {
                if ($message['role'] === 'system') {
                    $systemMessage = $message['content'];
                } else {
                    $conversationMessages[] = [
                        'role' => $message['role'] === 'assistant' ? 'assistant' : 'user',
                        'content' => $message['content'],
                    ];
                }
            }

            $model = $options['model'] ?? $this->model;

            $payload = [
                'model' => $model,
                'messages' => $conversationMessages,
                'max_tokens' => $options['max_tokens'] ?? 1000,
                'temperature' => $options['temperature'] ?? 0.7,
            ];

            if ($systemMessage) {
                $payload['system'] = $systemMessage;
            }

            $response = Http::withHeaders([
                'x-api-key' => $this->apiKey,
                'anthropic-version' => $this->apiVersion,
                'content-type' => 'application/json',
            ])->timeout(60)->post("{$this->baseUrl}/messages", $payload);

            if (!$response->successful()) {
                throw new Exception('Claude API error: ' . $response->body());
            }

            $result = $response->json();

            if (isset($result['error'])) {
                throw new Exception($result['error']['message'] ?? 'Claude API error');
            }

            $endTime = microtime(true);
            $responseTime = round(($endTime - $startTime), 2);

            $message = $result['content'][0]['text'] ?? '';
            $tokensUsed = ($result['usage']['input_tokens'] ?? 0) + ($result['usage']['output_tokens'] ?? 0);

            return [
                'success' => true,
                'message' => $message,
                'confidence' => $this->calculateConfidence($result),
                'tokens_used' => $tokensUsed,
                'cost' => $this->calculateCost($tokensUsed, $model),
                'response_time' => $responseTime,
                'provider' => 'claude',
                'model' => $model,
                'raw_response' => $result,
            ];
        } catch (Exception $e) {
            Log::error('Claude chat error: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => '',
                'error' => $e->getMessage(),
                'confidence' => 0,
                'tokens_used' => 0,
                'cost' => 0,
                'response_time' => 0,
                'provider' => 'claude',
            ];
        }
    }

    public function analyzeImage(string $imagePath, string $prompt, array $options = []): array
    {
        try {
            $startTime = microtime(true);

            $imageData = $this->encodeImage($imagePath);
            $mimeType = $this->getImageMimeType($imagePath);

            $payload = [
                'model' => 'claude-3-5-sonnet-20241022',
                'max_tokens' => $options['max_tokens'] ?? 1000,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => [
                            [
                                'type' => 'image',
                                'source' => [
                                    'type' => 'base64',
                                    'media_type' => $mimeType,
                                    'data' => $imageData,
                                ],
                            ],
                            [
                                'type' => 'text',
                                'text' => $prompt,
                            ],
                        ],
                    ],
                ],
            ];

            $response = Http::withHeaders([
                'x-api-key' => $this->apiKey,
                'anthropic-version' => $this->apiVersion,
                'content-type' => 'application/json',
            ])->timeout(60)->post("{$this->baseUrl}/messages", $payload);

            if (!$response->successful()) {
                throw new Exception('Claude Vision API error: ' . $response->body());
            }

            $result = $response->json();

            $endTime = microtime(true);
            $responseTime = round(($endTime - $startTime), 2);

            $message = $result['content'][0]['text'] ?? '';
            $tokensUsed = ($result['usage']['input_tokens'] ?? 0) + ($result['usage']['output_tokens'] ?? 0);

            return [
                'success' => true,
                'message' => $message,
                'tokens_used' => $tokensUsed,
                'cost' => $this->calculateCost($tokensUsed, 'claude-3-5-sonnet-20241022'),
                'response_time' => $responseTime,
                'provider' => 'claude',
            ];
        } catch (Exception $e) {
            Log::error('Claude vision error: ' . $e->getMessage());

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
        return [
            'success' => false,
            'embedding' => [],
            'error' => 'Claude does not support embeddings. Use OpenAI or Gemini for embeddings.',
        ];
    }

    public function verifyApiKey(): bool
    {
        try {
            $response = Http::withHeaders([
                'x-api-key' => $this->apiKey,
                'anthropic-version' => $this->apiVersion,
            ])->timeout(10)->post("{$this->baseUrl}/messages", [
                'model' => $this->model,
                'messages' => [
                    ['role' => 'user', 'content' => 'test'],
                ],
                'max_tokens' => 10,
            ]);

            return $response->successful();
        } catch (Exception $e) {
            Log::error('Claude key verification failed: ' . $e->getMessage());
            return false;
        }
    }

    public function getProviderName(): string
    {
        return 'claude';
    }

    public function supportsVision(): bool
    {
        return true;
    }

    public function calculateCost(int $tokensUsed, string $model = 'claude-3-5-haiku-20241022'): float
    {
        $pricing = [
            'claude-3-5-sonnet-20241022' => ['input' => 3.00 / 1000000, 'output' => 15.00 / 1000000],
            'claude-3-5-haiku-20241022' => ['input' => 0.80 / 1000000, 'output' => 4.00 / 1000000],
            'claude-3-opus-20240229' => ['input' => 15.00 / 1000000, 'output' => 75.00 / 1000000],
        ];

        if (!isset($pricing[$model])) {
            $model = 'claude-3-5-haiku-20241022';
        }

        $estimatedInputTokens = $tokensUsed * 0.7;
        $estimatedOutputTokens = $tokensUsed * 0.3;

        $cost = ($estimatedInputTokens * $pricing[$model]['input']) +
                ($estimatedOutputTokens * $pricing[$model]['output']);

        return round($cost, 6);
    }

    protected function calculateConfidence(array $result): float
    {
        if (isset($result['stop_reason'])) {
            $reason = $result['stop_reason'];

            if ($reason === 'end_turn') {
                return 0.95;
            }

            if ($reason === 'max_tokens') {
                return 0.70;
            }
        }

        return 0.80;
    }

    protected function encodeImage(string $imagePath): string
    {
        if (filter_var($imagePath, FILTER_VALIDATE_URL)) {
            $imageContent = file_get_contents($imagePath);
        } else {
            $imageContent = file_get_contents($imagePath);
        }

        return base64_encode($imageContent);
    }

    protected function getImageMimeType(string $imagePath): string
    {
        if (filter_var($imagePath, FILTER_VALIDATE_URL)) {
            $extension = pathinfo(parse_url($imagePath, PHP_URL_PATH), PATHINFO_EXTENSION);
        } else {
            $extension = pathinfo($imagePath, PATHINFO_EXTENSION);
        }

        $mimeTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
        ];

        return $mimeTypes[strtolower($extension)] ?? 'image/jpeg';
    }
}
