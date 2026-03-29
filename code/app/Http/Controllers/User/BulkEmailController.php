<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessEmailCampaignJob;
use App\Models\EmailCampaign;
use App\Models\EmailProvider;
use App\Models\SmsContactGroup;
use App\Services\Email\EmailManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BulkEmailController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->user = auth_user('web');
            return $next($request);
        });
    }

    // ─── Campaigns ───────────────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $campaigns = EmailCampaign::where('user_id', $this->user->id)
            ->with('provider')
            ->latest()
            ->paginate(paginateNumber())
            ->appends($request->all());

        return view('user.bulk_email.campaigns.index', [
            'meta_data' => $this->metaData(['title' => translate('Bulk Email Campaigns')]),
            'campaigns' => $campaigns,
        ]);
    }

    public function create(): View
    {
        $providers = EmailProvider::where('user_id', $this->user->id)->where('is_active', true)->get();
        $groups    = SmsContactGroup::where('user_id', $this->user->id)->get();

        return view('user.bulk_email.campaigns.create', [
            'meta_data' => $this->metaData(['title' => translate('New Email Campaign')]),
            'providers' => $providers,
            'groups'    => $groups,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'              => 'required|string|max:255',
            'email_provider_id' => 'required|integer|exists:email_providers,id',
            'subject'           => 'required|string|max:255',
            'from_name'         => 'nullable|string|max:255',
            'from_email'        => 'nullable|email|max:255',
            'reply_to'          => 'nullable|email|max:255',
            'body'              => 'required|string',
            'content_type'      => 'required|in:html,plain',
            'group_ids'         => 'required|array|min:1',
            'group_ids.*'       => 'integer|exists:sms_contact_groups,id',
            'scheduled_at'      => 'nullable|date|after:now',
        ]);

        $provider = EmailProvider::where('id', $request->email_provider_id)
            ->where('user_id', $this->user->id)
            ->firstOrFail();

        $groupIds = SmsContactGroup::where('user_id', $this->user->id)
            ->whereIn('id', $request->group_ids)
            ->pluck('id');

        DB::transaction(function () use ($request, $provider, $groupIds) {
            $campaign = EmailCampaign::create([
                'user_id'           => $this->user->id,
                'email_provider_id' => $provider->id,
                'name'              => $request->name,
                'subject'           => $request->subject,
                'from_name'         => $request->from_name,
                'from_email'        => $request->from_email,
                'reply_to'          => $request->reply_to,
                'body'              => $request->body,
                'content_type'      => $request->content_type,
                'status'            => 'draft',
                'scheduled_at'      => $request->scheduled_at,
            ]);

            $campaign->groups()->attach($groupIds);
        });

        return redirect()->route('user.bulk-email.index')
            ->with(response_status('Email campaign created.'));
    }

    public function show(string $uid): View
    {
        $campaign = EmailCampaign::where('uid', $uid)
            ->where('user_id', $this->user->id)
            ->with(['provider', 'groups', 'logs' => fn ($q) => $q->latest()->limit(100)])
            ->firstOrFail();

        return view('user.bulk_email.campaigns.show', [
            'meta_data' => $this->metaData(['title' => translate("Campaign: {$campaign->name}")]),
            'campaign'  => $campaign,
        ]);
    }

    public function launch(string $uid): RedirectResponse
    {
        $campaign = EmailCampaign::where('uid', $uid)
            ->where('user_id', $this->user->id)
            ->firstOrFail();

        if (!$campaign->isDraft()) {
            return back()->with(response_status('Campaign is not in draft status.', 'error'));
        }

        $campaign->update(['status' => 'queued']);
        ProcessEmailCampaignJob::dispatch($campaign->id);

        return back()->with(response_status('Campaign queued for sending.'));
    }

    public function destroy(string $uid): RedirectResponse
    {
        $campaign = EmailCampaign::where('uid', $uid)
            ->where('user_id', $this->user->id)
            ->where('status', 'draft')
            ->firstOrFail();

        $campaign->delete();

        return redirect()->route('user.bulk-email.index')
            ->with(response_status('Campaign deleted.'));
    }

    // ─── Provider Management ─────────────────────────────────────────────────────

    public function provider(): View
    {
        $providers        = EmailProvider::where('user_id', $this->user->id)->latest()->get();
        $supportedDrivers = EmailManager::supportedProviders();

        return view('user.bulk_email.provider.index', [
            'meta_data'        => $this->metaData(['title' => translate('Email Provider')]),
            'providers'        => $providers,
            'supportedDrivers' => $supportedDrivers,
        ]);
    }

    public function storeProvider(Request $request): RedirectResponse
    {
        $supported = EmailManager::supportedProviders();

        $request->validate([
            'name'     => 'required|string|max:255',
            'provider' => 'required|in:' . implode(',', array_keys($supported)),
        ]);

        $providerKey = $request->provider;
        $fields      = $supported[$providerKey]['fields'];
        $credentials = [];

        foreach ($fields as $field) {
            $val = $request->input("credentials.{$field['key']}");
            if ($field['required'] && empty($val)) {
                return back()->with(response_status("Field '{$field['label']}' is required.", 'error'))->withInput();
            }
            $credentials[$field['key']] = $val;
        }

        if ($request->boolean('is_default')) {
            EmailProvider::where('user_id', $this->user->id)->update(['is_default' => false]);
        }

        EmailProvider::create([
            'user_id'     => $this->user->id,
            'name'        => $request->name,
            'provider'    => $providerKey,
            'credentials' => $credentials,
            'is_default'  => $request->boolean('is_default'),
        ]);

        return back()->with(response_status('Email provider added.'));
    }

    public function destroyProvider(int $id): RedirectResponse
    {
        EmailProvider::where('id', $id)->where('user_id', $this->user->id)->delete();

        return back()->with(response_status('Provider removed.'));
    }

    public function testProvider(int $id, Request $request): RedirectResponse
    {
        $request->validate(['test_email' => 'required|email']);

        $provider = EmailProvider::where('id', $id)->where('user_id', $this->user->id)->firstOrFail();
        $manager  = new EmailManager();
        $driver   = $manager->driver($provider);

        $result = $driver->send(
            $request->test_email,
            'Test Email from ' . config('app.name'),
            '<p>This is a test email from <strong>' . config('app.name') . '</strong>.</p>',
            'html'
        );

        if ($result['success']) {
            return back()->with(response_status('Test email sent successfully!'));
        }

        return back()->with(response_status('Test failed: ' . ($result['error'] ?? 'Unknown error'), 'error'));
    }
}
