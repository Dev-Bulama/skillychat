<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Enums\StatusEnum;
use App\Http\Requests\Admin\LogoSettingRequest;
use App\Http\Requests\Admin\CustomInputRequest;
use App\Http\Services\SettingService;
use App\Models\Core\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
class SettingController extends Controller
{

    protected $settingService;

    /**
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['permissions:view_settings'])->only(['list','systemConfiguration']);
        $this->middleware(['permissions:update_settings'])->only(['pluginSetting','systemInfo','logoSetting','store','webhook','kycConfig']);

        $this->settingService = new SettingService();
    }

    /**
     * get all system settings
     * @return View
     */
    public function list() :View
    {
        return view('admin.setting.list',[
            'title'       => 'Settings',
            'breadcrumbs' => ['home'=>'admin.home','Settings'=> null],
            'timeZones'   => timezone_identifiers_list(),
            'countries'   => get_countries()
        ]);
    }

    /**
     * get all system systemConfiguration
     * @return View
     */
    public function systemConfiguration() :View
    {
        return view('admin.setting.system_configuration',[
            'title'       => 'System Configuration',
            'breadcrumbs' =>  ['home'=>'admin.home','Configuration'=> null],
        ]);
    }


    /**
     * update plugin settings
     * @return RedirectResponse
     */
    public function pluginSetting(Request $request) :string{

        $status = true;
        $message = translate('Updated Successfully');
        try {
            $this->settingService->updateSettings($request->input('site_settings'));

            if(isset($request->site_settings['google_recaptcha'])){
                if($request->site_settings['google_recaptcha']['status'] == (StatusEnum::true)->status()){
                    Setting::where('key','default_recaptcha')->update(
                        [
                            'value' => (StatusEnum::false)->status()
                        ]
                    );

                }
            }
        }catch (\Exception $exception) {
            $status  = false;
            $message = $exception->getMessage();
        }
        Cache::forget('site_settings');

        return json_encode([
            'status'  => $status,
            'message' => $message
        ]);

    }





     /**
      * update logo settings
      *
      * @param LogoSettingRequest $request
      * @return string
      */
    public function logoSetting(LogoSettingRequest $request) :string{

        $this->settingService->logoSettings($request->except('_token'));

        return json_encode([
            'status'=> true,
            'message'=> translate('Updated Successfully')
        ]);


    }

    /**
     * update  settings
     * @return RedirectResponse
     */
    public function store(Request $request) :string
    {

        $status = true;
        $message = translate('Updated Successfully');
        if($request->site_settings){
            try {
                $validations = $this->settingService->validationRules($request->site_settings);
                $request->validate( $validations['rules'],$validations['message']);
                if(isset($request->site_settings['time_zone'])){
                    $timeLocationFile = config_path('timesetup.php');
                    $time = '<?php $timelog = '.$request->site_settings['time_zone'].' ?>';
                    file_put_contents($timeLocationFile, $time);
                }
                $this->settingService->updateSettings($request->site_settings);

            } catch (\Exception $exception) {
               $status = false;
               $message = $exception->getMessage();
            }
        }


        return json_encode([
            'status' => $status,
            'message' => $message
        ]);

    }



    /**
     * ticket settings
     *
     * @param CustomInputRequest $request
     * @return string
     */
    public function ticketSetting(CustomInputRequest $request) :string{

        $response = $this->settingService->customPrompt($request);
        optimize_clear();
        return json_encode([
            'status'   => Arr::get($response,'status',false),
            'message'  => Arr::get($response,'message',Arr::get(config('server_error'),'server_error',''))
        ]);

    }



    /**
     * clear cache
     * @return RedirectResponse
     */
    public function cacheClear() :RedirectResponse
    {
        optimize_clear();
        return back()->with(response_status('Cache Cleared Successfully'));
    }

    /**
     * clear cache
     * @return View
     */
    public function serverInfo() :View
    {
        $systemInfo = [
            'laravel_version' => app()->version(),
            'server_detail'   => $_SERVER,
        ];
        return view('admin.server_info',[
            'breadcrumbs'     =>  ['home'=>'admin.home','Server Information'=> null],
            'title'           => "Server Information",
            'systemInfo'      =>  $systemInfo
        ]);
    }


    /**
     * update setting status
     * @param Request $request
     * @return JsonResponse
     */
    public function updateStatus(Request $request) : JsonResponse{
        $response = $this->settingService->statusUpdate($request);
        return response()->json($response);
    }



    /**
     * ai Configuration
     * @return View
     */
    public function openAi() :View
    {
        return view('admin.setting.open_ai_settings',[
            'breadcrumbs'     =>  ['home'=>'admin.home','Open Ai'=> null],
            'title'           => "AI Configuration",
        ]);
    }


    /**
     * get webhook settings
     *
     * @return View
     */
    public function webhook() :View
    {
        return view('admin.setting.webhook',[
            'title'       => 'Webhook Settings',
            'breadcrumbs' => ['home'=>'admin.home','Webhook'=> null],
        ]);
    }

    /**
     * get affiliate settings
     *
     * @return View
     */
    public function affiliate() :View
    {
        return view('admin.setting.affiliate',[
            'title'       => 'Affiliate Settings',
            'breadcrumbs' => ['home'=>'admin.home','Affiliate Settings'=> null],
        ]);
    }

    /**
     * get kyc settings
     * @return View
     */
    public function kycConfig() :View
    {
        return view('admin.setting.kyc_settings',[
            'title'       => 'KYC Configuration',
            'breadcrumbs' => ['home'=>'admin.home','KYC Settings'=> null],
        ]);
    }


    /**
     * KYC settings
     *
     * @param CustomInputRequest $request
     * @return string
     */
    public function kycSetting(CustomInputRequest $request) :string {

        $response = $this->settingService->customPrompt($request,'kyc_settings');
        optimize_clear();
        return json_encode([
            'status'      => Arr::get($response,'status',false),
            'message'     => Arr::get($response,'message',Arr::get(config('server_error'),'server_error',''))
        ]);

    }

    /**
     * User documentation management page
     */
    public function userDocumentation(): View
    {
        return view('admin.setting.user_documentation', [
            'title'       => translate('User Documentation'),
            'breadcrumbs' => ['Home' => 'admin.home', 'User Documentation' => null],
        ]);
    }

    /**
     * Frontend mode editor
     */
    public function frontendMode(): View
    {
        return view('admin.setting.frontend_mode', [
            'title'        => 'Frontend Mode',
            'current_mode' => site_settings('frontend_mode') ?? 'default',
            'custom_html'  => site_settings('custom_frontend_html') ?? $this->defaultCustomHtml(),
        ]);
    }

    public function saveFrontendMode(Request $request): RedirectResponse
    {
        $request->validate([
            'frontend_mode'       => ['required', 'in:default,custom,disabled'],
            'custom_frontend_html'=> ['nullable', 'string'],
        ]);

        // HTML is base64-encoded by the editor JS to prevent the Sanitization middleware
        // from stripping <script> tags. Decode it back before persisting.
        $rawHtml = $request->input('custom_frontend_html', '');
        if ($rawHtml !== '' && base64_encode(base64_decode($rawHtml, true)) === $rawHtml) {
            $rawHtml = base64_decode($rawHtml);
        }

        $this->settingService->updateSettings([
            'frontend_mode'        => $request->input('frontend_mode'),
            'custom_frontend_html' => $rawHtml,
        ]);

        Cache::forget('site_settings');

        return back()->with(response_status('Frontend mode updated.'));
    }

    private function defaultCustomHtml(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-indigo-50 to-purple-50 flex items-center justify-center">
    <div class="text-center px-6">
        <h1 class="text-5xl font-bold text-indigo-700 mb-4">Welcome</h1>
        <p class="text-xl text-gray-500 mb-8">Your platform is live and ready.</p>
        <a href="/user/login"
            class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-8 py-3 rounded-full transition">
            Get Started
        </a>
    </div>
</body>
</html>
HTML;
    }

    /**
     * Save user documentation content
     */
    public function saveUserDocumentation(\Illuminate\Http\Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'user_docs_label'   => 'nullable|string|max:100',
            'user_docs_content' => 'nullable|string',
        ]);

        \Illuminate\Support\Facades\DB::table('settings')->upsert([
            ['key' => 'user_docs_label',   'value' => $request->input('user_docs_label',   'Documentation')],
            ['key' => 'user_docs_content', 'value' => $request->input('user_docs_content', '')],
        ], ['key'], ['value']);

        optimize_clear();
        return redirect()->back()->with('success', translate('Documentation saved successfully.'));
    }

    /**
     * Admin WhatsApp API global settings (one-time setup).
     */
    public function whatsappSettings(): \Illuminate\View\View
    {
        return view('admin.setting.whatsapp', [
            'title' => 'WhatsApp API Settings',
        ]);
    }

    public function saveWhatsappSettings(\Illuminate\Http\Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'whatsapp_default_app_id'           => 'nullable|string|max:255',
            'whatsapp_default_waba_id'          => 'nullable|string|max:255',
            'whatsapp_default_access_token'     => 'nullable|string',
            'whatsapp_default_phone_number_id'  => 'nullable|string|max:255',
            'whatsapp_default_welcome_message'  => 'nullable|string',
            'whatsapp_default_fallback_message' => 'nullable|string',
            'whatsapp_platform_verify_token'    => 'nullable|string|max:255',
        ]);

        $keys = [
            'whatsapp_default_app_id',
            'whatsapp_default_waba_id',
            'whatsapp_default_access_token',
            'whatsapp_default_phone_number_id',
            'whatsapp_default_welcome_message',
            'whatsapp_default_fallback_message',
            'whatsapp_platform_verify_token',
        ];

        foreach ($keys as $key) {
            \Illuminate\Support\Facades\DB::table('settings')->upsert(
                [['key' => $key, 'value' => $request->input($key, '')]],
                ['key'],
                ['value']
            );
        }

        optimize_clear();
        return back()->with(response_status('WhatsApp API settings saved.'));
    }
}
