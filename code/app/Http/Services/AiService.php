<?php
namespace App\Http\Services;

use App\Enums\AiModuleType;
use App\Enums\PlanDuration;
use App\Enums\StatusEnum;
use App\Models\AiTemplate;
use App\Models\TemplateUsage;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelAction;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Orhanerday\OpenAi\OpenAi;
use App\Traits\AccountManager;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class AiService
{

    use ModelAction, AccountManager;



    /**
     * store template
     *
     * @param Request $request
     * @return void
     */
    public function saveTemplate(Request $request): array
    {

        $response = response_status('Template created successfully');
        try {
            $template = new AiTemplate();
            $template->name = $request->input("name");
            $template->category_id = $request->input("category_id");
            $template->sub_category_id = $request->input("sub_category_id");
            $template->description = $request->input("description");
            $template->icon = $request->input("icon");
            $template->custom_prompt = $request->input("custom_prompt");
            $template->is_default = $request->input("is_default");
            $template->save();

        } catch (\Exception $ex) {
            $response = response_status(strip_tags($ex->getMessage()), 'error');
        }

        return $response;

    }


    /**
     * update template
     *
     * @param Request $request
     * @return void
     */
    public function updateTemplate(Request $request): array
    {



        $response = response_status('Template updated successfully');

        try {
            $template = AiTemplate::findOrfail($request->input('id'));
            $template->name = $request->input("name");
            $template->category_id = $request->input("category_id");
            $template->sub_category_id = $request->input("sub_category_id");
            $template->description = $request->input("description");
            $template->icon = $request->input("icon");
            $template->custom_prompt = $request->input("custom_prompt");
            $template->is_default = $request->input("is_default") ?? $request->input("is_default");
            $template->prompt_fields = $this->parseManualParameters();
            $template->save();

        } catch (\Exception $ex) {
            $response = response_status($ex->getMessage(), 'error');
        }

        return $response;
    }


    public function setRules(Request $request): array
    {


        $rules = [
            "language" => ['required'],
            "custom_prompt" => ['required', Rule::in(StatusEnum::toArray())],

            "max_result" => [
                Rule::requiredIf(function () use ($request) {
                    return request()->routeIs('user.*');
                }),
                "nullable",
                "numeric",
                'gt:0',
                'max:5000'
            ],
            "ai_creativity" => ['nullable', Rule::in(array_values(Arr::get(config('settings'), 'default_creativity', [])))],
            "content_tone" => ['nullable', Rule::in(Arr::get(config('settings'), 'ai_default_tone', []))],
            "custom" => ['nullable', 'array']
        ];

        if (request()->input('custom_prompt') == StatusEnum::true->status()) {
            $rules['custom_prompt_input'] = ['required'];
        } else {
            $rules['id'] = ['required', "exists:ai_templates,id"];
        }


        $messages = [
            "language.required" => translate('Please select a input & output language'),
            "id.required" => translate('Please select a Template'),
            "max_result.required" => translate('Max result length field is required'),
            "custom_prompt.required" => translate('Prompt field is required'),
        ];

        if (
            request()->input('custom_prompt') == StatusEnum::false->status() &&
            request()->input('id')
        ) {
            $template = AiTemplate::find($request->input('id'));
            if ($template && $template->prompt_fields) {
                foreach ($template->prompt_fields as $key => $input) {
                    if ($input->validation == "required") {
                        $rules['custom.' . $key] = ['required'];
                    }
                }
            }
        }

        return [
            'template' => @$template,
            'rules' => $rules,
            'messages' => $messages,
        ];

    }


    public function setImageRules(Request $request): array
    {
        $rules = [
            "custom_prompt" => ['required', Rule::in(StatusEnum::toArray())],
            "max_result" => [
                Rule::requiredIf(fn() => request()->routeIs('user.*')),
                "nullable",
                "numeric",
                'gt:0',
                'max:10'
            ],
            "image_quality" => [
                'nullable',
                Rule::in(array_keys(Arr::get(config('settings'), 'ai_image_quality', ['standard', 'hd'])))
            ],
            "image_resolution" => [
                'nullable',
                Rule::in(array_keys(Arr::get(config('settings'), 'ai_image_resolution', ['256x256', '512x512', '1024x1024'])))
            ],
            "custom" => ['nullable', 'array']
        ];

        $messages = [
            "custom_prompt.required" => translate('Prompt field is required'),
            "max_result.required" => translate('Maximum number of images field is required'),
            "max_result.max" => translate('Maximum number of images cannot exceed 10'),
            "image_quality.in" => translate('Invalid image quality selected'),
            "image_resolution.in" => translate('Invalid image resolution selected'),
        ];

        if ($request->input('custom_prompt') == StatusEnum::true->status()) {
            $rules['custom_prompt_input'] = ['required'];
            $messages['custom_prompt_input.required'] = translate('Custom prompt input is required');
        } else {
            $rules['id'] = ['required', "exists:ai_templates,id"];
            $messages['id.required'] = translate('Please select a Template');
            $messages['id.exists'] = translate('Selected template does not exist');
        }

        $template = null;
        if ($request->input('custom_prompt') == StatusEnum::false->status() && $request->input('id')) {
            $template = AiTemplate::find($request->input('id'));
            if ($template && $template->prompt_fields) {
                foreach ($template->prompt_fields as $key => $input) {
                    if ($input->validation == "required") {
                        $rules['custom.' . $key] = ['required'];
                        $messages['custom.' . $key . '.required'] = translate('The ' . $key . ' field is required for the selected template');
                    }
                }
            }
        }

        return [
            'template' => $template,
            'rules' => $rules,
            'messages' => $messages,
        ];
    }

    public function setVideoRules(Request $request): array
    {
        $rules = [
            "custom_prompt" => ['required', Rule::in(StatusEnum::toArray())],
            "video_aspect_ratio" => [
                'nullable',
                Rule::in(array_keys(Arr::get(config('settings'), 'ai_video_aspect_ratio', ['9:16', '16:9', '1:1'])))
            ],
            "video_duration" => [
                'nullable',
                Rule::in(array_keys(Arr::get(config('settings'), 'ai_video_duration', ['5', '10'])))
            ],
            "custom" => ['nullable', 'array']
        ];

        $messages = [
            "custom_prompt.required" => translate('Prompt field is required'),
            "video_duration.in" => translate('Invalid video duratoin selected'),
            "video_resolution.in" => translate('Invalid video resolution selected'),
        ];

        if ($request->input('custom_prompt') == StatusEnum::true->status()) {
            $rules['custom_prompt_input'] = ['required'];
            $messages['custom_prompt_input.required'] = translate('Custom prompt input is required');
        } else {
            $rules['id'] = ['required', "exists:ai_templates,id"];
            $messages['id.required'] = translate('Please select a Template');
            $messages['id.exists'] = translate('Selected template does not exist');
        }

        $template = null;
        if ($request->input('custom_prompt') == StatusEnum::false->status() && $request->input('id')) {
            $template = AiTemplate::find($request->input('id'));
            if ($template && $template->prompt_fields) {
                foreach ($template->prompt_fields as $key => $input) {
                    if ($input->validation == "required") {
                        $rules['custom.' . $key] = ['required'];
                        $messages['custom.' . $key . '.required'] = translate('The ' . $key . ' field is required for the selected template');
                    }
                }
            }
        }

        return [
            'template' => $template,
            'rules' => $rules,
            'messages' => $messages,
        ];
    }

    public function generatreContent(Request $request, AiTemplate $template): array
    {


        $logData['template_id'] = $template->id;

        $logData['admin_id'] = request()->routeIs('admin.*')
            ? auth_user('admin')?->id
            : null;

        $logData['user_id'] = request()->routeIs('user.*')
            ? auth_user('web')?->id
            : null;

        $customPrompt = $template->custom_prompt;

        if ($request->input("custom") && $template->prompt_fields) {
            foreach ($template->prompt_fields as $key => $input) {
                $customPrompt = str_replace("{" . $key . "}", Arr::get($request->input("custom"), $key, "", ), $customPrompt);
            }
        }
        $getBadWords = site_settings('ai_bad_words');

        $processBadWords = $getBadWords
            ? explode(",", $getBadWords)
            : [];

        if (is_array($processBadWords)) {
            $customPrompt = str_replace($processBadWords, "", $customPrompt);
        }


        $temperature = (float) ($request->input("ai_creativity")
            ? $request->input("ai_creativity")
            : site_settings("ai_default_creativity"));
        $aiParams = [
            'model' => $this->getAiModel(),
            'temperature' => $temperature,
            'presence_penalty' => 0.6,
            'frequency_penalty' => 0.2,
        ];

        $aiTone = $request->input("content_tone")
            ? $request->input("content_tone")
            : site_settings("ai_default_tone");

        $tokens = (int) ($request->input("max_result")
            ? $request->input("max_result")
            : site_settings("default_max_result", -1));


        $language = $request->input("language");


        $customPrompt .= "\nPlease provide a concise and relevant response based on the following topic. Ensure the content is focused and informative, with no irrelevant information. The response should be in $language and use a $aiTone tone of voice. Focus on providing clarity and actionable insights. Do not include phrases like 'Of course' ,'certainly' or similar unnecessary introductory statements. If the topic is unclear, inform me without using excessive formalities or politeness.";



        if ($tokens != PlanDuration::UNLIMITED->value) {
            $aiParams['max_tokens'] = $tokens;
        }


        $aiParams['messages'] = [
            [
                "role" => "user",
                "content" => $customPrompt
            ]
        ];

        return $this->generateContent($aiParams, $logData);

    }

    public function generateImageContent(Request $request, AiTemplate $template): array
    {
        $logData = [
            'template_id' => $template->id,
            'admin_id' => request()->routeIs('admin.*') ? auth_user('admin')?->id : null,
            'user_id' => request()->routeIs('user.*') ? auth_user('web')?->id : null,
        ];

        $customPrompt = $template->custom_prompt;

        if ($request->input("custom") && $template->prompt_fields) {
            foreach ($template->prompt_fields as $key => $input) {
                $customPrompt = str_replace(
                    "{" . $key . "}",
                    Arr::get($request->input("custom"), $key, ""),
                    $customPrompt
                );
            }
        }

        $getBadWords = site_settings('ai_bad_words');
        $processBadWords = $getBadWords ? explode(",", $getBadWords) : [];
        if (is_array($processBadWords)) {
            $customPrompt = str_replace($processBadWords, "", $customPrompt);
        }

        $imageQuality = $request->input("image_quality") ?: site_settings("default_image_quality", "standard");
        $imageResolution = $request->input("image_resolution") ?: site_settings("default_image_resolution", "512x512");
        $maxResult = (int) ($request->input("max_result") ?: site_settings("default_max_image_result", 1));

        $aiParams = [
            'model' => $this->getImageAiModel(),
            'prompt' => $customPrompt,
            'n' => min($maxResult, 4),
            'size' => $imageResolution,
        ];

        $customPrompt .= 'Generate a clear and relevant image based on the provided description. Ensure the content is safe, appropriate, and visually coherent. Avoid any copyrighted elements or inappropriate content.';

        $aiParams['prompt'] = $customPrompt;

        return $this->generateImage($aiParams, $logData);
    }


    public function generateVideoContent(Request $request, AiTemplate $template): array
    {
        $logData = [
            'template_id' => $template->id,
            'admin_id' => request()->routeIs('admin.*') ? auth_user('admin')?->id : null,
            'user_id' => request()->routeIs('user.*') ? auth_user('web')?->id : null,
        ];

        $customPrompt = $template->custom_prompt;

        if ($request->input("custom") && $template->prompt_fields) {
            foreach ($template->prompt_fields as $key => $input) {
                $customPrompt = str_replace(
                    "{" . $key . "}",
                    Arr::get($request->input("custom"), $key, ""),
                    $customPrompt
                );
            }
        }

        $getBadWords = site_settings('ai_bad_words');
        $processBadWords = $getBadWords ? explode(",", $getBadWords) : [];
        if (is_array($processBadWords)) {
            $customPrompt = str_replace($processBadWords, "", $customPrompt);
        }

        $aiParams = $this->getModelSpecificParam($request, $customPrompt);

        $customPrompt .= 'Generate a clear and relevant video based on the provided description. Ensure the content is safe, appropriate, and visually coherent. Avoid any copyrighted elements or inappropriate content.';

        $aiParams['prompt'] = $customPrompt;

        return $this->generateVideo($aiParams, $logData);
    }






    /**
     * Generate custom prompt content for AI
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function generatreCustomPromptContent(Request $request): array
    {

        $logData = [
            'admin_id' => request()->routeIs('admin.*') ? auth_user('admin')?->id : null,
            'user_id' => request()->routeIs('user.*') ? auth_user('web')?->id : null,
            'template_id' => $request->input('id'),
        ];

        $customPrompt = $request->input('custom_prompt_input') ?? '';
        $badWords = site_settings('ai_bad_words');
        $processBadWords = $badWords ? explode(',', $badWords) : [];

        if (!empty($processBadWords)) {
            $customPrompt = str_replace($processBadWords, '', $customPrompt);
        }

        $temperature = (float) ($request->input('ai_creativity') ?? site_settings('ai_default_creativity', 0.7));
        $aiTone = $request->input('content_tone') ?? site_settings('ai_default_tone', 'neutral');
        $language = $request->input('language') ?? 'English';
        $tokens = (int) ($request->input('max_result') ?? site_settings('default_max_result', -1));



        $customPrompt .= "\nPlease provide a concise and relevant response based on the following topic. Ensure the content is focused and informative, with no irrelevant information. The response should be in $language language and use a $aiTone tone of voice. Focus on providing clarity and actionable insights. Do not include phrases like 'Of course' , 'certainly' or similar unnecessary introductory statements. If the topic is unclear, inform me without using excessive formalities or politeness.";



        $aiParams = [
            'model' => $this->getAiModel(),
            'temperature' => $temperature,
            'presence_penalty' => 0.6,
            'frequency_penalty' => 0.0,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $customPrompt,
                ]
            ],
        ];

        if ($tokens !== PlanDuration::UNLIMITED->value) {
            $aiParams['max_tokens'] = $tokens;
        }

        return $this->generateContent($aiParams, $logData);
    }


    public function generateCustomPromptImageContent(Request $request): array
    {
        $logData = [
            'admin_id' => request()->routeIs('admin.*') ? auth_user('admin')?->id : null,
            'user_id' => request()->routeIs('user.*') ? auth_user('web')?->id : null,
            'template_id' => $request->input('id'),
        ];

        $customPrompt = $request->input('custom_prompt_input') ?? '';

        $badWords = site_settings('ai_bad_words');
        $processBadWords = $badWords ? explode(',', $badWords) : [];
        if (!empty($processBadWords)) {
            $customPrompt = str_replace($processBadWords, '', $customPrompt);
        }


        $imageQuality = $request->input('image_quality') ?? site_settings('default_image_quality', 'standard');
        $imageResolution = $request->input('image_resolution') ?? site_settings('default_image_resolution', '512x512');
        $maxResult = (int) ($request->input('max_result') ?? site_settings('default_max_image_result', 1));

        $aiParams = [
            'model' => $this->getImageAiModel(),
            'prompt' => $customPrompt,
            'n' => min($maxResult, 4),
            'size' => $imageResolution,
        ];

        $customPrompt .= ' Generate a clear and relevant image based on the provided description. Ensure the content is safe, appropriate, and visually coherent. Avoid any copyrighted elements or inappropriate content.';
        $aiParams['prompt'] = $customPrompt;

        return $this->generateImage($aiParams, $logData);
    }


    public function generateCustomPromptVideoContent(Request $request): array
    {
        $logData = [
            'admin_id' => request()->routeIs('admin.*') ? auth_user('admin')?->id : null,
            'user_id' => request()->routeIs('user.*') ? auth_user('web')?->id : null,
            'template_id' => $request->input('id'),
        ];

        $customPrompt = $request->input('custom_prompt_input') ?? '';

        $badWords = site_settings('ai_bad_words');
        $processBadWords = $badWords ? explode(',', $badWords) : [];
        if (!empty($processBadWords)) {
            $customPrompt = str_replace($processBadWords, '', $customPrompt);
        }

        $aiParams = $this->getModelSpecificParam($request, $customPrompt);

        $customPrompt .= ' Generate a clear and relevant video based on the provided description. Ensure the content is safe, appropriate, and visually coherent. Avoid any copyrighted elements or inappropriate content.';
        $aiParams['prompt'] = $customPrompt;

        return $this->generateVideo($aiParams, $logData);
    }



    /**
     * Summary of getAiModel
     * @return string|null
     */
    public function getAiModel(): string|null
    {

        $model = site_settings("open_ai_model");

        if (request()->routeIs("user.*")) {
            $subscription = auth_user('web')->runningSubscription;
            $model = optional(optional($subscription)->package->ai_configuration)->open_ai_model ?? $model;
        }


        return $model;
    }


    /**
     * Summary of getImageAiModel
     */
    public function getImageAiModel(): ?string
    {
        $model = site_settings('image_model');

        if (request()->routeIs('user.*')) {
            $subscription = auth_user('web')?->runningSubscription;
            $model = optional(optional($subscription)->package->ai_configuration)->image_model ?? $model;
        }

        return $model;
    }


    /**
     * Summary of getVideoAiModel
     */
    public function getVideoAiModel(): ?string
    {
        $model = site_settings('video_model');

        if (request()->routeIs('user.*')) {
            $subscription = auth_user('web')?->runningSubscription;
            $model = optional(optional($subscription)->package->ai_configuration)->video_model ?? $model;
        }

        return $model;
    }

    public function getModelSpecificParam($request, $prompt)
    {
        $aiModel = $this->getVideoAiModel();
        $params = [];


        switch ($aiModel) {
            case 'kling-v1':
                $params = [
                    'model_name' => "kling-v1",
                    'prompt' => $prompt,
                    'negative_prompt' => null,
                    'cfg_scale' => 0.5,
                    'mode' => 'std',
                    'aspect_ratio' => '16:9',
                    'duration' => 5,
                ];
                break;

            default:
                throw new Exception("Unsupported AI model: {$aiModel}");
        }

        return $params;
    }




    /**
     * Generate content using open ai
     *
     * @param array $aiParams
     * @param array $logData
     * @return array
     */
    public function generateContent(array $aiParams, array $logData): array
    {

        $status = false;
        $message = translate("Invalid Request");
        $open_ai = new OpenAi(OPENAI_API_KEY: openai_key());
        $chat_results = json_decode($open_ai->chat($aiParams), true);

        if (isset($chat_results['error'])) {
            $message = Arr::get($chat_results['error'], 'message', translate('Invalid Request'));
        } else {

            if (isset($chat_results['choices'][0]['message']['content'])) {

                $realContent = $chat_results['choices'][0]['message']['content'];
                $content = str_replace(["\r\n", "\r", "\n"], "<br>", $realContent);
                $content = preg_replace('/^"(.*)"$/', '$1', $content);
                $usage = $chat_results['usage'];

                $usage['model'] = $chat_results['model'];
                $usage['genarated_tokens'] = count(explode(' ', ($content)));



                DB::transaction(function () use ($logData, $usage, $content) {

                    $templateId = Arr::get($logData, 'template_id', null);

                    if ($templateId) {

                        $templateLog = new TemplateUsage();
                        $templateLog->user_id = Arr::get($logData, 'user_id', null);
                        $templateLog->admin_id = Arr::get($logData, 'admin_id', null);
                        $templateLog->template_id = Arr::get($logData, 'template_id', null);
                        $templateLog->package_id = Arr::get($logData, 'package_id', null);
                        $templateLog->open_ai_usage = $usage;
                        $templateLog->content = $content;
                        $templateLog->total_words = Arr::get($usage, 'genarated_tokens', 0);
                        $templateLog->save();
                    }


                    if (request()->routeIs("user.*")) {
                        $token = (int) Arr::get($usage, "completion_tokens", 0);
                        $user = auth_user('web')->load(['runningSubscription']);

                        $details = $token . " word generated using custom prompt";

                        if ($templateId)
                            $details = $token . " word generated using (" . @$templateLog->template->name . ") Template";

                        $this->generateCreditLog(
                            user: $user,
                            trxType: Transaction::$MINUS,
                            balance: (int) $token,
                            postBalance: (int) $user->runningSubscription->remaining_word_balance,
                            details: $details,
                            remark: t2k("word_credit"),
                        );

                        $userToken = @$user->runningSubscription->remaining_word_balance;

                        if (@$userToken != PlanDuration::UNLIMITED->value && $userToken > 0) {
                            $user->runningSubscription->decrement('remaining_word_balance', $token);
                        }
                    }



                });


                $status = true;
                $message = $realContent;
            }


        }

        return [
            "status" => $status,
            "message" => $message,
        ];

    }


    public function generateImage(array $aiParams, array $logData): array
    {
        $status = false;
        $message = translate("Invalid Request");

        $modelConfig = [
            'dall-e-2' => [
                'provider' => 'openai',
                'api_key_func' => 'openai_Image_key',
                'supported_sizes' => ['256x256', '512x512', '1024x1024'],
            ],
            'dall-e-3' => [
                'provider' => 'openai',
                'api_key_func' => 'openai_Image_key',
                'supported_sizes' => ['1024x1024', '1792x1024', '1024x1792'],
            ],

        ];

        if (!isset($aiParams['model']) || !isset($modelConfig[$aiParams['model']])) {
            return [
                'status' => $status,
                'message' => translate("Invalid or unsupported model. Supported models: " . implode(', ', array_keys($modelConfig))),
            ];
        }

        $config = $modelConfig[$aiParams['model']];
        $provider = $config['provider'];

        if (empty($aiParams['prompt'])) {
            return [
                'status' => $status,
                'message' => translate("Prompt is required"),
                'image_content' => null,
            ];
        }

        $size = $aiParams['size'] ?? '1024x1024';
        if (!in_array($size, $config['supported_sizes'])) {
            return [
                'status' => $status,
                'message' => translate("Invalid size for {$aiParams['model']}. Supported sizes: " . implode(', ', $config['supported_sizes'])),
                'image_content' => null,
            ];
        }


        $open_ai = new OpenAi(OPENAI_API_KEY: openai_Image_key());
        $image_results = json_decode($open_ai->image($aiParams), true);



        try {

            if ($provider === 'openai') {
                $open_ai = new OpenAi(OPENAI_API_KEY: call_user_func($config['api_key_func']));
            } else {
                throw new \Exception("Unsupported provider: {$provider}");
            }

            $params = [
                'prompt' => $aiParams['prompt'],
                'model' => $aiParams['model'],
                'n' => $aiParams['n'] ?? 1,
                'size' => $size,
                'response_format' => $aiParams['response_format'] ?? 'url',
            ];

            $image_results = json_decode($open_ai->image($params), true);

            if (isset($image_results['error'])) {
                $message = Arr::get($image_results['error'], 'message', translate('Invalid Request'));
            } else {

                if (isset($image_results['data']) && is_array($image_results['data'])) {
                    $image_urls = array_map(fn($item) => $item['url'], $image_results['data']);

                    $usage = [
                        'model' => $aiParams['model'],
                        'generated_images' => count($image_urls),
                    ];

                    DB::transaction(function () use ($logData, $usage, $image_urls) {
                        $templateId = Arr::get($logData, 'template_id', null);

                        if ($templateId) {
                            $templateLog = new TemplateUsage();
                            $templateLog->user_id = Arr::get($logData, 'user_id', null);
                            $templateLog->admin_id = Arr::get($logData, 'admin_id', null);
                            $templateLog->template_id = $templateId;
                            $templateLog->package_id = Arr::get($logData, 'package_id', null);
                            $templateLog->open_ai_usage = $usage;
                            $templateLog->images = $image_urls;
                            $templateLog->total_images = $usage['generated_images'];
                            $templateLog->type = AiModuleType::IMAGE->value;
                            $templateLog->save();
                        }

                        if (request()->routeIs("user.*")) {
                            $image_count = $usage['generated_images'];
                            $user = auth_user('web')->load(['runningSubscription']);

                            $details = "{$image_count} image(s) generated using custom prompt";
                            if ($templateId) {
                                $details = "{$image_count} image(s) generated using (" . @$templateLog->template->name . ") Template";
                            }

                            $this->generateCreditLog(
                                user: $user,
                                trxType: Transaction::$MINUS,
                                balance: $image_count,
                                postBalance: (int) $user->runningSubscription->remaining_image_balance,
                                details: $details,
                                remark: t2k("image_credit"),
                            );

                            $userImageBalance = @$user->runningSubscription->remaining_image_balance;
                            if ($userImageBalance != PlanDuration::UNLIMITED->value && $userImageBalance > 0) {
                                $user->runningSubscription->decrement('remaining_image_balance', $image_count);
                            }
                        }
                    });

                    $status = true;
                    $message = translate('Image generated');
                }
            }
        } catch (\Exception $e) {
            $message = translate("Error generating image: ") . $e->getMessage();
        }

        return [
            'status' => $status,
            'message' => $message,
            'image_content' => $image_urls ?? null
        ];
    }


    public function generateVideo(array $aiParams, array $logData): array
    {
        $status     = false;
        $message    = translate("Invalid Request");
        $video_url  = null;

        $modelConfig = [
            'kling-v1' => [
                'provider' => 'kling_ai',
                'api_key_func' => 'kling_ai_video_key',
                'supported_aspect_ratio' => ['9:16', '16:9', '1:1'],
                'task_endpoint' => 'https://api.klingai.com/v1/videos/text2video',
                'status_endpoint' => 'https://api.klingai.com/v1/videos/task',
            ],
            'runway-gen3' => [
                'provider' => 'runway_ml',
                'api_key_func' => 'runway_ml_video_key',
                'supported_aspect_ratio' => ['9:16', '16:9', '1:1'],
                'task_endpoint' => 'https://api.runwayml.com/v1/image_to_video',
                'status_endpoint' => 'https://api.runwayml.com/v1/tasks',
            ],
            'pika-v1' => [
                'provider' => 'pika_labs',
                'api_key_func' => 'pika_labs_video_key',
                'supported_aspect_ratio' => ['9:16', '16:9', '1:1'],
                'task_endpoint' => 'https://api.pika.art/generate/video',
                'status_endpoint' => 'https://api.pika.art/video',
            ],
        ];


        if (!isset($aiParams['model_name']) || !isset($modelConfig[$aiParams['model_name']])) {
            return [
                'status' => $status,
                'message' => translate("Invalid or unsupported model. Supported models: " . implode(', ', array_keys($modelConfig))),
                'video_content' => null,
            ];
        }

        $config = $modelConfig[$aiParams['model_name']];
        $provider = $config['provider'];


        if (empty($aiParams['prompt'])) {
            return [
                'status' => $status,
                'message' => translate("Prompt is required"),
                'video_content' => null,
            ];
        }


        $aspect_ratio = $aiParams['aspect_ratio'] ?? '16:9';
        if (!in_array($aspect_ratio, $config['supported_aspect_ratio'])) {
            return [
                'status' => $status,
                'message' => translate("Invalid aspect ratio for {$aiParams['model_name']}. Supported aspect ratios: " . implode(', ', $config['supported_aspect_ratio'])),
                'video_content' => null,
            ];
        }

        try {
            if ($provider === 'kling_ai') {
                $apiKey = call_user_func($config['api_key_func']);
                if (empty($apiKey)) {
                    throw new \Exception("API key is missing for Kling AI");
                }


                $payload = [
                    'prompt' => $aiParams['prompt'],
                    'aspect_ratio' => $aspect_ratio,
                    'duration' => $aiParams['duration'] ?? 5,
                    'fps' => 30,
                    'cfg_scale' => $aiParams['cfg_scale'] ?? 0.7,
                    'negative_prompt' => $aiParams['negative_prompt'] ?? 'blurry, distorted, unrealistic',
                    'external_task_id' => $aiParams['external_task_id'] ?? Str::uuid()->toString(),
                ];


                //video generation task
                $task_response = $this->makeKlingApiRequest($config['task_endpoint'], $payload, $apiKey, 'POST');
                $task_data = $task_response;

                if ($task_response->failed() || $task_data['code'] !== 0 || !isset($task_data['data']['task_id'])) {
                    throw new \Exception($task_data['message'] ?? translate("Failed to create video task"));
                }

                $task_id = $task_data['data']['task_id'];

                //Poll task status
                $max_attempts = 30;
                $attempt = 0;
                $poll_interval = 10;


                while ($attempt < $max_attempts) {
                    $status_response = $this->makeKlingApiRequest($config['status_endpoint'] . '/' . $task_id, [], $apiKey, 'GET');
                    $status_data = $status_response;

                    if ($status_response->failed() || $status_data['code'] !== 0) {
                        throw new \Exception($status_data['message'] ?? translate("Failed to retrieve task status"));
                    }

                    $task_status = $status_data['data']['task_status'];

                    if ($task_status === 'succeed') {
                        if (isset($status_data['data']['task_result']['videos'][0]['url'])) {
                            $video_url = $status_data['data']['task_result']['videos'][0]['url'];


                            // Log usage to database
                            $usage = [
                                'model' => $aiParams['model_name'],
                                'generated_videos' => 1,
                            ];

                            DB::transaction(function () use ($logData, $usage, $video_url) {
                                $templateId = Arr::get($logData, 'template_id', null);

                                if ($templateId) {
                                    $templateLog = new TemplateUsage();
                                    $templateLog->user_id = Arr::get($logData, 'user_id', null);
                                    $templateLog->admin_id = Arr::get($logData, 'admin_id', null);
                                    $templateLog->template_id = $templateId;
                                    $templateLog->package_id = Arr::get($logData, 'package_id', null);
                                    $templateLog->open_ai_usage = $usage;
                                    $templateLog->videos = [$video_url];
                                    $templateLog->total_videos = $usage['generated_videos'];
                                    $templateLog->type = AiModuleType::VIDEO->value;
                                    $templateLog->save();
                                }

                                if (request()->routeIs("user.*")) {
                                    $video_count = $usage['generated_videos'];
                                    $user = auth_user('web')->load(['runningSubscription']);

                                    $details = "{$video_count} video(s) generated using custom prompt";
                                    if ($templateId) {
                                        $details = "{$video_count} video(s) generated using (" . @$templateLog->template->name . ") Template";
                                    }

                                    $this->generateCreditLog(
                                        user: $user,
                                        trxType: Transaction::$MINUS,
                                        balance: $video_count,
                                        postBalance: (int) $user->runningSubscription->remaining_video_balance,
                                        details: $details,
                                        remark: t2k("video_credit"),
                                    );

                                    $userVideoBalance = @$user->runningSubscription->remaining_video_balance;
                                    if ($userVideoBalance != PlanDuration::UNLIMITED->value && $userVideoBalance > 0) {
                                        $user->runningSubscription->decrement('remaining_video_balance', $video_count);
                                    }
                                }
                            });

                            $status = true;
                            $message = translate('Video generated successfully');
                            break;
                        } else {
                            throw new \Exception("No video URL found in successful task");
                        }
                    } elseif ($task_status === 'failed') {
                        throw new \Exception($status_data['data']['task_status_msg'] ?? translate("Video generation task failed"));
                    }

                    $attempt++;
                    sleep($poll_interval);
                }

                if ($attempt >= $max_attempts) {
                    throw new \Exception(translate("Video generation timed out"));
                }
            } elseif ($provider === 'runway_ml') {
                $apiKey = call_user_func($config['api_key_func']);
                if (empty($apiKey)) {
                    throw new \Exception("API key is missing for Runway ML");
                }

                $payload = [
                    'text_prompt' => $aiParams['prompt'],
                    'model' => 'gen3a_turbo',
                    'duration' => $aiParams['duration'] ?? 5,
                    'ratio' => $aspect_ratio,
                    'watermark' => false,
                ];

                // Create video generation task
                $task_response = $this->makeRunwayApiRequest($config['task_endpoint'], $payload, $apiKey, 'POST');
                $task_data = $task_response->json();

                if ($task_response->failed() || !isset($task_data['id'])) {
                    throw new \Exception($task_data['error'] ?? translate("Failed to create Runway video task"));
                }

                $task_id = $task_data['id'];

                // Poll task status
                $max_attempts = 30;
                $attempt = 0;
                $poll_interval = 10;

                while ($attempt < $max_attempts) {
                    $status_response = $this->makeRunwayApiRequest($config['status_endpoint'] . '/' . $task_id, [], $apiKey, 'GET');
                    $status_data = $status_response->json();

                    if ($status_response->failed()) {
                        throw new \Exception($status_data['error'] ?? translate("Failed to retrieve task status"));
                    }

                    if ($status_data['status'] === 'SUCCEEDED' && isset($status_data['output'][0])) {
                        $video_url = $status_data['output'][0];

                        // Log usage and save
                        $this->logVideoUsage($logData, $aiParams['model_name'], $video_url);

                        $status = true;
                        $message = translate('Video generated successfully with Runway ML');
                        break;
                    } elseif ($status_data['status'] === 'FAILED') {
                        throw new \Exception($status_data['failure'] ?? translate("Runway video generation failed"));
                    }

                    $attempt++;
                    sleep($poll_interval);
                }

                if ($attempt >= $max_attempts) {
                    throw new \Exception(translate("Runway video generation timed out"));
                }
            } elseif ($provider === 'pika_labs') {
                $apiKey = call_user_func($config['api_key_func']);
                if (empty($apiKey)) {
                    throw new \Exception("API key is missing for Pika Labs");
                }

                $payload = [
                    'prompt' => $aiParams['prompt'],
                    'aspectRatio' => $aspect_ratio,
                    'frameRate' => 24,
                    'options' => [
                        'model' => 'pika-1.0',
                        'duration' => $aiParams['duration'] ?? 3,
                    ],
                ];

                // Create video generation task
                $task_response = $this->makePikaApiRequest($config['task_endpoint'], $payload, $apiKey, 'POST');
                $task_data = $task_response->json();

                if ($task_response->failed() || !isset($task_data['id'])) {
                    throw new \Exception($task_data['error'] ?? translate("Failed to create Pika video task"));
                }

                $task_id = $task_data['id'];

                // Poll task status
                $max_attempts = 30;
                $attempt = 0;
                $poll_interval = 10;

                while ($attempt < $max_attempts) {
                    $status_response = $this->makePikaApiRequest($config['status_endpoint'] . '/' . $task_id, [], $apiKey, 'GET');
                    $status_data = $status_response->json();

                    if ($status_response->failed()) {
                        throw new \Exception($status_data['error'] ?? translate("Failed to retrieve Pika task status"));
                    }

                    if ($status_data['status'] === 'finished' && isset($status_data['video']['url'])) {
                        $video_url = $status_data['video']['url'];

                        // Log usage and save
                        $this->logVideoUsage($logData, $aiParams['model_name'], $video_url);

                        $status = true;
                        $message = translate('Video generated successfully with Pika Labs');
                        break;
                    } elseif ($status_data['status'] === 'failed') {
                        throw new \Exception($status_data['error'] ?? translate("Pika video generation failed"));
                    }

                    $attempt++;
                    sleep($poll_interval);
                }

                if ($attempt >= $max_attempts) {
                    throw new \Exception(translate("Pika video generation timed out"));
                }
            } else {
                throw new \Exception("Unsupported provider: {$provider}");
            }
        } catch (\Exception $e) {
            $message = translate("Error generating video: ") . $e->getMessage();
        }

        return [
            'status'        => $status,
            'message'       => $message,
            'video_content' => [$video_url],
        ];
    }



    /**
     * Helper method to make API request to Kling AI using Laravel's HTTP client
     */
    private function makeKlingApiRequest(string $endpoint, array $payload, string $apiKey, string $method = 'POST')
    {

        $headers = [
            'Authorization' => 'Bearer ' . $apiKey,
            'Cache-Control' => 'no-cache',
        ];

        $request = Http::withHeaders($headers);

        if ($method === 'POST') {
            $response = $request->post($endpoint, $payload);
        } else {
            $response = $request->get($endpoint);
        }

        if ($response->failed()) {
            $error_message = $response->json('message', 'HTTP error ' . $response->status());
            throw new \Exception("API request failed with status {$response->status()}: $error_message");
        }

        return $response;
    }

    /**
     * Helper method to make API request to Runway ML
     */
    private function makeRunwayApiRequest(string $endpoint, array $payload, string $apiKey, string $method = 'POST')
    {
        $headers = [
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
            'X-Runway-Version' => '2024-11-06',
        ];

        $request = Http::withHeaders($headers);

        if ($method === 'POST') {
            $response = $request->post($endpoint, $payload);
        } else {
            $response = $request->get($endpoint);
        }

        return $response;
    }

    /**
     * Helper method to make API request to Pika Labs
     */
    private function makePikaApiRequest(string $endpoint, array $payload, string $apiKey, string $method = 'POST')
    {
        $headers = [
            'Authorization' => $apiKey,
            'Content-Type' => 'application/json',
        ];

        $request = Http::withHeaders($headers);

        if ($method === 'POST') {
            $response = $request->post($endpoint, $payload);
        } else {
            $response = $request->get($endpoint);
        }

        return $response;
    }

    /**
     * Helper method to log video usage across all providers
     */
    private function logVideoUsage(array $logData, string $model, string $video_url): void
    {
        $usage = [
            'model' => $model,
            'generated_videos' => 1,
        ];

        DB::transaction(function () use ($logData, $usage, $video_url) {
            $templateId = Arr::get($logData, 'template_id', null);

            if ($templateId) {
                $templateLog = new TemplateUsage();
                $templateLog->user_id = Arr::get($logData, 'user_id', null);
                $templateLog->admin_id = Arr::get($logData, 'admin_id', null);
                $templateLog->template_id = $templateId;
                $templateLog->package_id = Arr::get($logData, 'package_id', null);
                $templateLog->open_ai_usage = $usage;
                $templateLog->videos = [$video_url];
                $templateLog->total_videos = $usage['generated_videos'];
                $templateLog->type = AiModuleType::VIDEO->value;
                $templateLog->save();
            }

            if (request()->routeIs("user.*")) {
                $video_count = $usage['generated_videos'];
                $user = auth_user('web')->load(['runningSubscription']);

                $details = "{$video_count} video(s) generated using custom prompt";
                if ($templateId) {
                    $details = "{$video_count} video(s) generated using (" . @$templateLog->template->name . ") Template";
                }

                $this->generateCreditLog(
                    user: $user,
                    trxType: Transaction::$MINUS,
                    balance: $video_count,
                    credit_type: 'video',
                    details: $details,
                );

                if ($user->runningSubscription) {
                    $user->runningSubscription->decrement('remaining_video_balance', $video_count);
                }
            }
        });
    }

    public function useMock($endpoint)
    {
        $useMock = true;

        if ($useMock) {
            if (str_contains($endpoint, 'https://api.klingai.com/v1/videos/text2video')) {
                return [
                    'code' => 0,
                    'message' => 'Task created successfully',
                    'data' => [
                        'task_id' => '12345-abcde-67890',
                    ],
                ];
            }


            if (str_contains($endpoint, 'https://api.klingai.com/v1/videos/task/12345-abcde-67890')) {
                return [
                    'code' => 0,
                    'message' => 'Task status retrieved successfully',
                    'data' => [
                        'task_status' => 'succeed',
                        'task_result' => [
                            'videos' => [
                                [
                                    'url' => 'https://www.w3schools.com/html/mov_bbb.mp4',
                                    'title' => 'Sample Video 1',
                                    'duration' => 120,
                                ],
                            ],
                        ],
                    ],
                ];
            }
        }
    }


    public function generatePrompt(Request $request)
    {
        $request->validate([
            'post_content' => 'required|string',
        ]);


        $logData = [
            'admin_id' => $request->routeIs('admin.*') ? auth_user('admin')?->id : null,
            'user_id' => $request->routeIs('user.*') ? auth_user('web')?->id : null,
            'template_id' => null,
        ];


        $rawContent = $request->input('post_content');


        $badWords = explode(',', site_settings('ai_bad_words') ?? '');
        if (!empty($badWords)) {
            $rawContent = str_ireplace($badWords, '', $rawContent);
        }


        $aiParams = [
            'model' => $this->getAiModel(),
            'temperature' => (float) ($request->input('ai_creativity') ?? site_settings('ai_default_creativity')),
            'presence_penalty' => 0.6,
            'frequency_penalty' => 0.2,
            'max_tokens' => 200,
        ];

        $language = $request->input('language') ?? 'English';


        $promptInstruction = <<<EOT
                                Based on the following post content, generate a creative and detailed text prompt that can be used to generate an AI-generated image.
                                Focus on extracting the visual themes, objects, settings, emotions, and key elements that would represent the content well as an image.
                                Use descriptive language (e.g., colors, lighting, scenery) but keep it concise and usable as a prompt.
                                Do not include irrelevant commentary or text instructions.

                                Respond in $language only.

                                Post Content:
                                "$rawContent"
                                EOT;


        $aiParams['messages'] = [
            [
                'role' => 'user',
                'content' => $promptInstruction,
            ]
        ];


        return $this->generateContent($aiParams, $logData);

    }





}
