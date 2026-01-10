<?php

namespace App\Services\AI;

use App\Models\Chatbot;
use App\Models\ChatbotApiKey;
use Exception;
use Illuminate\Support\Facades\Log;

class AIManager
{
    /**
     * Get AI provider instance for a chatbot
     *
     * @param Chatbot $chatbot
     * @param string|null $customProvider Override chatbot's default provider
     * @return AIProviderInterface
     * @throws Exception
     */
    public function getProvider(Chatbot $chatbot, ?string $customProvider = null): AIProviderInterface
    {
        $provider = $customProvider ?? $chatbot->ai_provider;

        $apiKey = $this->getApiKeyForProvider($chatbot, $provider);

        if (!$apiKey) {
            throw new Exception("No API key configured for provider: {$provider}");
        }

        return $this->createProviderInstance($provider, $apiKey);
    }

    /**
     * Get AI provider instance from API key model
     *
     * @param ChatbotApiKey $apiKeyModel
     * @return AIProviderInterface
     * @throws Exception
     */
    public function getProviderFromApiKey(ChatbotApiKey $apiKeyModel): AIProviderInterface
    {
        return $this->createProviderInstance($apiKeyModel->provider, $apiKeyModel->api_key);
    }

    /**
     * Get AI provider instance by provider name and API key
     *
     * @param string $provider Provider name (openai, gemini, claude)
     * @param string $apiKey API key
     * @param string|null $model Optional model override
     * @return AIProviderInterface
     * @throws Exception
     */
    public function createProvider(string $provider, string $apiKey, ?string $model = null): AIProviderInterface
    {
        return $this->createProviderInstance($provider, $apiKey, $model);
    }

    /**
     * Get all available AI providers
     *
     * @return array
     */
    public function getAvailableProviders(): array
    {
        return [
            'openai' => [
                'name' => 'OpenAI',
                'models' => [
                    'gpt-4o' => 'GPT-4o (Most Capable)',
                    'gpt-4o-mini' => 'GPT-4o Mini (Recommended)',
                    'gpt-4-turbo' => 'GPT-4 Turbo',
                    'gpt-3.5-turbo' => 'GPT-3.5 Turbo (Fastest)',
                ],
                'supports_vision' => true,
                'supports_embeddings' => true,
            ],
            'gemini' => [
                'name' => 'Google Gemini',
                'models' => [
                    'gemini-1.5-pro' => 'Gemini 1.5 Pro',
                    'gemini-1.5-flash' => 'Gemini 1.5 Flash (Recommended)',
                    'gemini-2.0-flash-exp' => 'Gemini 2.0 Flash (Experimental)',
                ],
                'supports_vision' => true,
                'supports_embeddings' => true,
            ],
            'claude' => [
                'name' => 'Anthropic Claude',
                'models' => [
                    'claude-3-5-sonnet-20241022' => 'Claude 3.5 Sonnet',
                    'claude-3-5-haiku-20241022' => 'Claude 3.5 Haiku (Recommended)',
                    'claude-3-opus-20240229' => 'Claude 3 Opus',
                ],
                'supports_vision' => true,
                'supports_embeddings' => false,
            ],
        ];
    }

    /**
     * Verify if an API key is valid
     *
     * @param string $provider
     * @param string $apiKey
     * @return bool
     */
    public function verifyApiKey(string $provider, string $apiKey): bool
    {
        try {
            $providerInstance = $this->createProviderInstance($provider, $apiKey);
            return $providerInstance->verifyApiKey();
        } catch (Exception $e) {
            Log::error("API key verification failed for {$provider}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get the best provider for embeddings (falls back to OpenAI)
     *
     * @param Chatbot $chatbot
     * @return AIProviderInterface
     * @throws Exception
     */
    public function getEmbeddingProvider(Chatbot $chatbot): AIProviderInterface
    {
        $provider = $chatbot->ai_provider;

        if ($provider === 'claude') {
            $provider = 'openai';
        }

        return $this->getProvider($chatbot, $provider);
    }

    /**
     * Create provider instance
     *
     * @param string $provider
     * @param string $apiKey
     * @param string|null $model
     * @return AIProviderInterface
     * @throws Exception
     */
    protected function createProviderInstance(string $provider, string $apiKey, ?string $model = null): AIProviderInterface
    {
        switch (strtolower($provider)) {
            case 'openai':
                return $model ? new OpenAIService($apiKey, $model) : new OpenAIService($apiKey);

            case 'gemini':
                return $model ? new GeminiService($apiKey, $model) : new GeminiService($apiKey);

            case 'claude':
                return $model ? new ClaudeService($apiKey, $model) : new ClaudeService($apiKey);

            default:
                throw new Exception("Unsupported AI provider: {$provider}");
        }
    }

    /**
     * Get API key for a provider from chatbot's configured keys
     *
     * @param Chatbot $chatbot
     * @param string $provider
     * @return string|null
     */
    protected function getApiKeyForProvider(Chatbot $chatbot, string $provider): ?string
    {
        $apiKeyModel = ChatbotApiKey::where('chatbot_id', $chatbot->id)
            ->where('provider', $provider)
            ->where('status', 'active')
            ->orderBy('is_default', 'desc')
            ->first();

        if ($apiKeyModel) {
            return $apiKeyModel->api_key;
        }

        $userApiKey = ChatbotApiKey::where('user_id', $chatbot->user_id)
            ->where('provider', $provider)
            ->where('status', 'active')
            ->where('is_default', true)
            ->whereNull('chatbot_id')
            ->first();

        if ($userApiKey) {
            return $userApiKey->api_key;
        }

        return $this->getSystemApiKey($provider);
    }

    /**
     * Get system-wide API key from environment or config
     *
     * @param string $provider
     * @return string|null
     */
    protected function getSystemApiKey(string $provider): ?string
    {
        $envKeys = [
            'openai' => env('OPENAI_API_KEY'),
            'gemini' => env('GEMINI_API_KEY'),
            'claude' => env('CLAUDE_API_KEY'),
        ];

        return $envKeys[$provider] ?? null;
    }
}
