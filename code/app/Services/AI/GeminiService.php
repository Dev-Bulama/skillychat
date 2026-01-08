<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class GeminiService implements AIProviderInterface
{
    protected string $apiKey;
    protected string $model;
    protected string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta';

    public function __construct(string $apiKey, string $model = 'gemini-1.5-flash')
    {
        $this->apiKey = $apiKey;
        $this->model = $model;
    }

    public function chat(array $messages, array $options = []): array
    {
        try {
            $startTime = microtime(true);

            $contents = $this->convertMessagesToGeminiFormat($messages);

            $model = $options['model'] ?? $this->model;
            $url = "{$this->baseUrl}/models/{$model}:generateContent?key={$this->apiKey}";

            $payload = [
                'contents' => $contents,
                'generationConfig' => [
                    'temperature' => $options['temperature'] ?? 0.7,
                    'maxOutputTokens' => $options['max_tokens'] ?? 1000,
                ],
            ];

            $response = Http::timeout(60)->post($url, $payload);

            if (!$response->successful()) {
                throw new Exception('Gemini API error: ' . $response->body());
            }

            $result = $response->json();

            if (isset($result['error'])) {
                throw new Exception($result['error']['message'] ?? 'Gemini API error');
            }

            $endTime = microtime(true);
            $responseTime = round(($endTime - $startTime), 2);

            $message = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';
            $tokensUsed = ($result['usageMetadata']['promptTokenCount'] ?? 0) +
                         ($result['usageMetadata']['candidatesTokenCount'] ?? 0);

            return [
                'success' => true,
                'message' => $message,
                'confidence' => $this->calculateConfidence($result),
                'tokens_used' => $tokensUsed,
                'cost' => $this->calculateCost($tokensUsed, $model),
                'response_time' => $responseTime,
                'provider' => 'gemini',
                'model' => $model,
                'raw_response' => $result,
            ];
        } catch (Exception $e) {
            Log::error('Gemini chat error: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => '',
                'error' => $e->getMessage(),
                'confidence' => 0,
                'tokens_used' => 0,
                'cost' => 0,
                'response_time' => 0,
                'provider' => 'gemini',
            ];
        }
    }

    public function analyzeImage(string $imagePath, string $prompt, array $options = []): array
    {
        try {
            $startTime = microtime(true);

            $imageData = $this->encodeImage($imagePath);

            $url = "{$this->baseUrl}/models/gemini-1.5-flash:generateContent?key={$this->apiKey}";

            $payload = [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt],
                            [
                                'inline_data' => [
                                    'mime_type' => 'image/jpeg',
                                    'data' => $imageData,
                                ],
                            ],
                        ],
                    ],
                ],
            ];

            $response = Http::timeout(60)->post($url, $payload);

            if (!$response->successful()) {
                throw new Exception('Gemini Vision API error: ' . $response->body());
            }

            $result = $response->json();

            $endTime = microtime(true);
            $responseTime = round(($endTime - $startTime), 2);

            $message = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';
            $tokensUsed = ($result['usageMetadata']['totalTokenCount'] ?? 0);

            return [
                'success' => true,
                'message' => $message,
                'tokens_used' => $tokensUsed,
                'cost' => $this->calculateCost($tokensUsed, 'gemini-1.5-flash'),
                'response_time' => $responseTime,
                'provider' => 'gemini',
            ];
        } catch (Exception $e) {
            Log::error('Gemini vision error: ' . $e->getMessage());

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
            $url = "{$this->baseUrl}/models/text-embedding-004:embedContent?key={$this->apiKey}";

            $response = Http::timeout(30)->post($url, [
                'model' => 'models/text-embedding-004',
                'content' => [
                    'parts' => [
                        ['text' => $text],
                    ],
                ],
            ]);

            if (!$response->successful()) {
                throw new Exception('Gemini Embedding API error: ' . $response->body());
            }

            $result = $response->json();

            return [
                'success' => true,
                'embedding' => $result['embedding']['values'] ?? [],
                'tokens_used' => 0,
            ];
        } catch (Exception $e) {
            Log::error('Gemini embedding error: ' . $e->getMessage());

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
            $url = "{$this->baseUrl}/models?key={$this->apiKey}";
            $response = Http::timeout(10)->get($url);

            return $response->successful();
        } catch (Exception $e) {
            Log::error('Gemini key verification failed: ' . $e->getMessage());
            return false;
        }
    }

    public function getProviderName(): string
    {
        return 'gemini';
    }

    public function supportsVision(): bool
    {
        return true;
    }

    public function calculateCost(int $tokensUsed, string $model = 'gemini-1.5-flash'): float
    {
        $pricing = [
            'gemini-1.5-pro' => ['input' => 1.25 / 1000000, 'output' => 5.00 / 1000000],
            'gemini-1.5-flash' => ['input' => 0.075 / 1000000, 'output' => 0.30 / 1000000],
            'gemini-2.0-flash-exp' => ['input' => 0.00 / 1000000, 'output' => 0.00 / 1000000],
        ];

        if (!isset($pricing[$model])) {
            $model = 'gemini-1.5-flash';
        }

        $estimatedInputTokens = $tokensUsed * 0.7;
        $estimatedOutputTokens = $tokensUsed * 0.3;

        $cost = ($estimatedInputTokens * $pricing[$model]['input']) +
                ($estimatedOutputTokens * $pricing[$model]['output']);

        return round($cost, 6);
    }

    protected function convertMessagesToGeminiFormat(array $messages): array
    {
        $contents = [];

        foreach ($messages as $message) {
            $role = $message['role'] === 'assistant' ? 'model' : 'user';

            if ($message['role'] === 'system') {
                continue;
            }

            $contents[] = [
                'role' => $role,
                'parts' => [
                    ['text' => $message['content']],
                ],
            ];
        }

        return $contents;
    }

    protected function calculateConfidence(array $result): float
    {
        if (isset($result['candidates'][0]['finishReason'])) {
            $reason = $result['candidates'][0]['finishReason'];

            if ($reason === 'STOP') {
                return 0.95;
            }

            if ($reason === 'MAX_TOKENS') {
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
}
