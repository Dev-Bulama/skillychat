<?php

namespace App\Services;

use App\Models\Chatbot;
use App\Models\ChatbotAgent;
use App\Models\ChatbotApiKey;
use App\Models\ChatbotConversation;
use App\Models\ChatbotMessage;
use App\Models\ChatbotTrainingData;
use App\Models\ChatbotUsageLog;
use App\Models\User;
use App\Services\AI\AIManager;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ChatbotService
{
    protected AIManager $aiManager;

    public function __construct(AIManager $aiManager)
    {
        $this->aiManager = $aiManager;
    }

    /**
     * Create a new chatbot
     *
     * @param User $user
     * @param array $data
     * @return Chatbot
     * @throws Exception
     */
    public function createChatbot(User $user, array $data): Chatbot
    {
        $this->checkSubscriptionLimits($user);

        return DB::transaction(function () use ($user, $data) {
            $chatbot = Chatbot::create([
                'user_id' => $user->id,
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'domain' => $data['domain'] ?? null,
                'language' => $data['language'] ?? 'en',
                'tone' => $data['tone'] ?? 'professional',
                'welcome_message' => $data['welcome_message'] ?? 'Hello! How can I help you today?',
                'offline_message' => $data['offline_message'] ?? 'We are currently offline. Please leave a message.',
                'primary_color' => $data['primary_color'] ?? '#0084ff',
                'widget_position' => $data['widget_position'] ?? 'bottom-right',
                'emoji_support' => $data['emoji_support'] ?? true,
                'voice_support' => $data['voice_support'] ?? false,
                'image_support' => $data['image_support'] ?? false,
                'human_takeover_enabled' => $data['human_takeover_enabled'] ?? true,
                'ai_provider' => $data['ai_provider'] ?? 'openai',
                'ai_confidence_threshold' => $data['ai_confidence_threshold'] ?? 0.70,
                'status' => 'active',
            ]);

            $this->addDefaultAgent($chatbot, $user);

            return $chatbot;
        });
    }

    /**
     * Update chatbot settings
     *
     * @param Chatbot $chatbot
     * @param array $data
     * @return Chatbot
     */
    public function updateChatbot(Chatbot $chatbot, array $data): Chatbot
    {
        $chatbot->update(array_filter($data));

        return $chatbot->fresh();
    }

    /**
     * Delete a chatbot and all related data
     *
     * @param Chatbot $chatbot
     * @return bool
     */
    public function deleteChatbot(Chatbot $chatbot): bool
    {
        return DB::transaction(function () use ($chatbot) {
            $chatbot->trainingData()->delete();
            $chatbot->conversations()->delete();
            $chatbot->agents()->delete();
            $chatbot->usageLogs()->delete();
            $chatbot->apiKeys()->delete();

            return $chatbot->delete();
        });
    }

    /**
     * Add training data to chatbot
     *
     * @param Chatbot $chatbot
     * @param string $type text, file, url, faq
     * @param array $data
     * @return ChatbotTrainingData
     * @throws Exception
     */
    public function addTrainingData(Chatbot $chatbot, string $type, array $data): ChatbotTrainingData
    {
        $this->checkTrainingDataLimits($chatbot);

        $trainingData = ChatbotTrainingData::create([
            'chatbot_id' => $chatbot->id,
            'type' => $type,
            'title' => $data['title'] ?? null,
            'content' => $data['content'] ?? null,
            'source_url' => $data['source_url'] ?? null,
            'status' => 'processing',
        ]);

        if ($type === 'text' || $type === 'faq') {
            $this->processTextTraining($trainingData);
        } elseif ($type === 'url') {
            $this->processUrlTraining($trainingData);
        }

        return $trainingData;
    }

    /**
     * Upload and add file training data
     *
     * @param Chatbot $chatbot
     * @param UploadedFile $file
     * @return ChatbotTrainingData
     * @throws Exception
     */
    public function uploadTrainingFile(Chatbot $chatbot, UploadedFile $file): ChatbotTrainingData
    {
        $this->checkTrainingDataLimits($chatbot);

        $path = $file->store('chatbot_training/' . $chatbot->id, 'public');

        $trainingData = ChatbotTrainingData::create([
            'chatbot_id' => $chatbot->id,
            'type' => 'file',
            'title' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'status' => 'processing',
        ]);

        $this->processFileTraining($trainingData);

        return $trainingData;
    }

    /**
     * Process a chat message and generate AI response
     *
     * @param Chatbot $chatbot
     * @param ChatbotConversation $conversation
     * @param string $message
     * @param array $options
     * @return ChatbotMessage
     * @throws Exception
     */
    public function processMessage(Chatbot $chatbot, ChatbotConversation $conversation, string $message, array $options = []): ChatbotMessage
    {
        DB::beginTransaction();

        try {
            $visitorMessage = $conversation->addMessage([
                'sender_type' => 'visitor',
                'message' => $message,
                'message_type' => $options['message_type'] ?? 'text',
            ]);

            if ($conversation->status === 'human_active') {
                DB::commit();
                return $visitorMessage;
            }

            if ($this->shouldTriggerHumanTakeover($conversation, $message)) {
                $conversation->requestHumanTakeover();
                $this->autoAssignAgent($conversation);

                DB::commit();
                return $visitorMessage;
            }

            $chatHistory = $this->buildChatHistory($conversation);
            $systemPrompt = $this->buildSystemPrompt($chatbot);

            $messages = array_merge(
                [['role' => 'system', 'content' => $systemPrompt]],
                $chatHistory,
                [['role' => 'user', 'content' => $message]]
            );

            $aiProvider = $this->aiManager->getProvider($chatbot);
            $aiResponse = $aiProvider->chat($messages, [
                'temperature' => 0.7,
                'max_tokens' => 1000,
            ]);

            if (!$aiResponse['success']) {
                throw new Exception($aiResponse['error'] ?? 'AI provider error');
            }

            $aiMessage = $conversation->addMessage([
                'sender_type' => 'ai',
                'message' => $aiResponse['message'],
                'message_type' => 'text',
                'ai_confidence' => $aiResponse['confidence'],
                'ai_provider' => $aiResponse['provider'],
                'ai_tokens_used' => $aiResponse['tokens_used'],
                'ai_cost' => $aiResponse['cost'],
                'response_time' => $aiResponse['response_time'],
            ]);

            $this->logUsage($chatbot, $conversation, $aiResponse);

            if ($aiResponse['confidence'] < $chatbot->ai_confidence_threshold && $chatbot->human_takeover_enabled) {
                $conversation->requestHumanTakeover();
                $this->autoAssignAgent($conversation);
            }

            DB::commit();

            return $aiMessage;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('ChatbotService::processMessage error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Process image message with AI vision
     *
     * @param Chatbot $chatbot
     * @param ChatbotConversation $conversation
     * @param UploadedFile $image
     * @param string $prompt
     * @return ChatbotMessage
     * @throws Exception
     */
    public function processImageMessage(Chatbot $chatbot, ChatbotConversation $conversation, UploadedFile $image, string $prompt): ChatbotMessage
    {
        DB::beginTransaction();

        try {
            $imagePath = $image->store('chatbot_images/' . $chatbot->id, 'public');
            $fullPath = Storage::disk('public')->path($imagePath);

            $visitorMessage = $conversation->addMessage([
                'sender_type' => 'visitor',
                'message' => $prompt,
                'message_type' => 'image',
                'file_path' => $imagePath,
                'file_type' => $image->getClientMimeType(),
                'file_size' => $image->getSize(),
            ]);

            if ($conversation->status === 'human_active') {
                DB::commit();
                return $visitorMessage;
            }

            $aiProvider = $this->aiManager->getProvider($chatbot);
            $aiResponse = $aiProvider->analyzeImage($fullPath, $prompt);

            if (!$aiResponse['success']) {
                throw new Exception($aiResponse['error'] ?? 'AI vision error');
            }

            $aiMessage = $conversation->addMessage([
                'sender_type' => 'ai',
                'message' => $aiResponse['message'],
                'message_type' => 'text',
                'ai_provider' => $aiResponse['provider'],
                'ai_tokens_used' => $aiResponse['tokens_used'],
                'ai_cost' => $aiResponse['cost'],
                'response_time' => $aiResponse['response_time'],
            ]);

            $this->logUsage($chatbot, $conversation, $aiResponse);

            DB::commit();

            return $aiMessage;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('ChatbotService::processImageMessage error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get or create conversation for visitor
     *
     * @param Chatbot $chatbot
     * @param string $visitorId
     * @param array $visitorData
     * @return ChatbotConversation
     */
    public function getOrCreateConversation(Chatbot $chatbot, string $visitorId, array $visitorData = []): ChatbotConversation
    {
        $conversation = ChatbotConversation::where('chatbot_id', $chatbot->id)
            ->where('visitor_id', $visitorId)
            ->whereIn('status', ['ai_active', 'human_requested', 'human_active'])
            ->orderBy('last_message_at', 'desc')
            ->first();

        if (!$conversation) {
            $conversation = ChatbotConversation::create([
                'chatbot_id' => $chatbot->id,
                'visitor_id' => $visitorId,
                'visitor_name' => $visitorData['name'] ?? null,
                'visitor_email' => $visitorData['email'] ?? null,
                'visitor_ip' => $visitorData['ip'] ?? request()->ip(),
                'visitor_user_agent' => $visitorData['user_agent'] ?? request()->userAgent(),
                'current_page_url' => $visitorData['page_url'] ?? null,
                'referrer_url' => $visitorData['referrer'] ?? null,
                'status' => 'ai_active',
            ]);

            $chatbot->increment('total_conversations');
        }

        return $conversation;
    }

    /**
     * Assign conversation to a human agent
     *
     * @param ChatbotConversation $conversation
     * @param ChatbotAgent $agent
     * @return void
     */
    public function assignToAgent(ChatbotConversation $conversation, ChatbotAgent $agent): void
    {
        $conversation->assignToAgent($agent);
        $agent->incrementHandled();
        $agent->updateActivity();
    }

    /**
     * Send message as human agent
     *
     * @param ChatbotConversation $conversation
     * @param ChatbotAgent $agent
     * @param string $message
     * @param bool $isInternal
     * @return ChatbotMessage
     */
    public function sendAgentMessage(ChatbotConversation $conversation, ChatbotAgent $agent, string $message, bool $isInternal = false): ChatbotMessage
    {
        return $conversation->addMessage([
            'sender_type' => 'agent',
            'agent_id' => $agent->id,
            'message' => $message,
            'message_type' => 'text',
            'is_internal_note' => $isInternal,
        ]);
    }

    /**
     * Build system prompt with training data
     *
     * @param Chatbot $chatbot
     * @return string
     */
    protected function buildSystemPrompt(Chatbot $chatbot): string
    {
        $basePrompt = <<<PROMPT
You are a helpful AI assistant representing {$chatbot->name}.
{$chatbot->description}

Tone: {$chatbot->tone}
Language: {$chatbot->language}

Use the following knowledge base to answer questions accurately and helpfully.
If you don't know the answer, politely say so and ask if they'd like to speak with a human agent.

PROMPT;

        $trainingData = $chatbot->trainingData()
            ->where('status', 'active')
            ->where('is_processed', true)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        if ($trainingData->isNotEmpty()) {
            $basePrompt .= "\n\nKnowledge Base:\n";

            foreach ($trainingData as $data) {
                $basePrompt .= "- " . $data->content . "\n";
            }
        }

        return $basePrompt;
    }

    /**
     * Build chat history for AI context
     *
     * @param ChatbotConversation $conversation
     * @return array
     */
    protected function buildChatHistory(ChatbotConversation $conversation): array
    {
        $messages = $conversation->messages()
            ->where('is_internal_note', false)
            ->orderBy('created_at', 'asc')
            ->limit(20)
            ->get();

        $history = [];

        foreach ($messages as $msg) {
            $role = match ($msg->sender_type) {
                'visitor' => 'user',
                'ai' => 'assistant',
                'agent' => 'assistant',
                default => 'user',
            };

            $history[] = [
                'role' => $role,
                'content' => $msg->message,
            ];
        }

        return $history;
    }

    /**
     * Check if human takeover should be triggered
     *
     * @param ChatbotConversation $conversation
     * @param string $message
     * @return bool
     */
    protected function shouldTriggerHumanTakeover(ChatbotConversation $conversation, string $message): bool
    {
        $triggers = [
            'talk to human',
            'speak to agent',
            'human support',
            'customer support',
            'real person',
            'speak to someone',
            'talk to someone',
        ];

        $messageLower = strtolower($message);

        foreach ($triggers as $trigger) {
            if (str_contains($messageLower, $trigger)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Auto-assign conversation to available agent
     *
     * @param ChatbotConversation $conversation
     * @return void
     */
    protected function autoAssignAgent(ChatbotConversation $conversation): void
    {
        $agent = ChatbotAgent::where('chatbot_id', $conversation->chatbot_id)
            ->online()
            ->canTakeover()
            ->autoAssign()
            ->orderBy('total_conversations_handled', 'asc')
            ->first();

        if ($agent) {
            $this->assignToAgent($conversation, $agent);
        }
    }

    /**
     * Log usage for analytics
     *
     * @param Chatbot $chatbot
     * @param ChatbotConversation $conversation
     * @param array $aiResponse
     * @return void
     */
    protected function logUsage(Chatbot $chatbot, ChatbotConversation $conversation, array $aiResponse): void
    {
        ChatbotUsageLog::create([
            'chatbot_id' => $chatbot->id,
            'conversation_id' => $conversation->id,
            'user_id' => $chatbot->user_id,
            'ai_provider' => $aiResponse['provider'] ?? null,
            'tokens_used' => $aiResponse['tokens_used'] ?? 0,
            'cost' => $aiResponse['cost'] ?? 0,
            'messages_count' => 1,
            'usage_date' => now()->toDateString(),
        ]);
    }

    /**
     * Process text training data
     *
     * @param ChatbotTrainingData $trainingData
     * @return void
     */
    protected function processTextTraining(ChatbotTrainingData $trainingData): void
    {
        $trainingData->markAsProcessed();
    }

    /**
     * Process URL training data
     *
     * @param ChatbotTrainingData $trainingData
     * @return void
     */
    protected function processUrlTraining(ChatbotTrainingData $trainingData): void
    {
        try {
            $content = file_get_contents($trainingData->source_url);
            $text = strip_tags($content);
            $text = preg_replace('/\s+/', ' ', $text);
            $text = trim(substr($text, 0, 5000));

            $trainingData->update(['content' => $text]);
            $trainingData->markAsProcessed();
        } catch (Exception $e) {
            Log::error('URL training error: ' . $e->getMessage());
            $trainingData->markAsFailed();
        }
    }

    /**
     * Process file training data
     *
     * @param ChatbotTrainingData $trainingData
     * @return void
     */
    protected function processFileTraining(ChatbotTrainingData $trainingData): void
    {
        try {
            $path = Storage::disk('public')->path($trainingData->file_path);
            $content = '';

            if (str_ends_with($trainingData->file_type, 'pdf')) {
                $content = 'PDF processing requires additional library';
            } else {
                $content = file_get_contents($path);
            }

            $trainingData->update(['content' => substr($content, 0, 10000)]);
            $trainingData->markAsProcessed();
        } catch (Exception $e) {
            Log::error('File training error: ' . $e->getMessage());
            $trainingData->markAsFailed();
        }
    }

    /**
     * Check subscription limits for creating chatbot
     *
     * @param User $user
     * @return void
     * @throws Exception
     */
    protected function checkSubscriptionLimits(User $user): void
    {
        $subscription = $user->runningSubscription;

        if (!$subscription) {
            throw new Exception('No active subscription found');
        }

        $package = $subscription->package;
        $currentCount = Chatbot::where('user_id', $user->id)->count();

        if ($package->max_chatbots > 0 && $currentCount >= $package->max_chatbots) {
            throw new Exception("Maximum chatbot limit reached ({$package->max_chatbots})");
        }
    }

    /**
     * Check training data size limits
     *
     * @param Chatbot $chatbot
     * @return void
     * @throws Exception
     */
    protected function checkTrainingDataLimits(Chatbot $chatbot): void
    {
        $subscription = $chatbot->user->runningSubscription;

        if (!$subscription) {
            throw new Exception('No active subscription found');
        }

        $package = $subscription->package;
        $currentSize = $chatbot->trainingData()->sum('file_size') / 1024 / 1024;

        if ($package->training_data_size_mb > 0 && $currentSize >= $package->training_data_size_mb) {
            throw new Exception("Training data size limit reached ({$package->training_data_size_mb} MB)");
        }
    }

    /**
     * Add default agent (chatbot owner) as admin
     *
     * @param Chatbot $chatbot
     * @param User $user
     * @return ChatbotAgent
     */
    protected function addDefaultAgent(Chatbot $chatbot, User $user): ChatbotAgent
    {
        return ChatbotAgent::create([
            'chatbot_id' => $chatbot->id,
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => 'admin',
            'status' => 'offline',
            'can_takeover' => true,
            'auto_assign' => false,
        ]);
    }

    public function processVoiceMessage(Chatbot $chatbot, ChatbotConversation $conversation, UploadedFile $audio): ChatbotMessage
    {
        DB::beginTransaction();

        try {
            // Store the audio file
            $audioPath = $audio->store('chatbot_voice/' . $chatbot->id, 'public');
            $fullPath = Storage::disk('public')->path($audioPath);

            // Transcribe the audio using OpenAI Whisper
            $transcription = $this->transcribeAudio($chatbot, $fullPath);

            // Create visitor message with transcription
            $visitorMessage = $conversation->addMessage([
                'sender_type' => 'visitor',
                'message' => $transcription,
                'message_type' => 'voice',
                'file_path' => $audioPath,
                'file_type' => $audio->getClientMimeType(),
                'file_size' => $audio->getSize(),
            ]);

            // If conversation is with human, don't process with AI
            if ($conversation->status === 'human_active') {
                DB::commit();
                return $visitorMessage;
            }

            // Process the transcribed text as a normal message
            $aiProvider = $this->aiManager->getProvider($chatbot);
            $aiResponse = $aiProvider->chat($this->buildChatHistory($conversation, $transcription));

            if (!$aiResponse['success']) {
                throw new Exception($aiResponse['error'] ?? 'AI response error');
            }

            $aiMessage = $conversation->addMessage([
                'sender_type' => 'ai',
                'message' => $aiResponse['message'],
                'message_type' => 'text',
                'ai_provider' => $aiResponse['provider'],
                'ai_tokens_used' => $aiResponse['tokens_used'],
                'ai_cost' => $aiResponse['cost'],
                'response_time' => $aiResponse['response_time'],
            ]);

            $this->logUsage($chatbot, $conversation, $aiResponse);

            DB::commit();

            return $visitorMessage; // Return visitor message with transcription
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('ChatbotService::processVoiceMessage error: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function transcribeAudio(Chatbot $chatbot, string $audioPath): string
    {
        try {
            // Get API key for transcription (OpenAI Whisper)
            $apiKey = $this->aiManager->getApiKeyForProvider($chatbot, 'openai');

            if (!$apiKey) {
                throw new Exception('No OpenAI API key configured for voice transcription');
            }

            // Use OpenAI Whisper API to transcribe
            $client = new \GuzzleHttp\Client();
            $response = $client->post('https://api.openai.com/v1/audio/transcriptions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiKey,
                ],
                'multipart' => [
                    [
                        'name' => 'file',
                        'contents' => fopen($audioPath, 'r'),
                        'filename' => basename($audioPath),
                    ],
                    [
                        'name' => 'model',
                        'contents' => 'whisper-1',
                    ],
                    [
                        'name' => 'language',
                        'contents' => $chatbot->language ?? 'en',
                    ],
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            return $data['text'] ?? '';

        } catch (Exception $e) {
            Log::error('Voice transcription error: ' . $e->getMessage());
            throw new Exception('Failed to transcribe audio: ' . $e->getMessage());
        }
    }
}
