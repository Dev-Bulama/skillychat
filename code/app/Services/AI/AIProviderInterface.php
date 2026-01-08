<?php

namespace App\Services\AI;

interface AIProviderInterface
{
    /**
     * Send a message to the AI provider and get a response
     *
     * @param array $messages Chat history messages
     * @param array $options Additional options (temperature, max_tokens, etc.)
     * @return array Response data containing message, confidence, tokens_used, cost
     */
    public function chat(array $messages, array $options = []): array;

    /**
     * Analyze an image with AI (if provider supports vision)
     *
     * @param string $imagePath Path or URL to the image
     * @param string $prompt Question about the image
     * @param array $options Additional options
     * @return array Response data
     */
    public function analyzeImage(string $imagePath, string $prompt, array $options = []): array;

    /**
     * Generate embeddings for text (for training data)
     *
     * @param string $text Text to embed
     * @return array Embedding vector
     */
    public function generateEmbedding(string $text): array;

    /**
     * Verify if the API key is valid
     *
     * @return bool
     */
    public function verifyApiKey(): bool;

    /**
     * Get the provider name
     *
     * @return string
     */
    public function getProviderName(): string;

    /**
     * Check if provider supports vision/image analysis
     *
     * @return bool
     */
    public function supportsVision(): bool;

    /**
     * Calculate estimated cost for a request
     *
     * @param int $tokensUsed
     * @param string $model
     * @return float Cost in USD
     */
    public function calculateCost(int $tokensUsed, string $model = 'default'): float;
}
