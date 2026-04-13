<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessSmsCampaignJob;
use App\Models\SmsCampaign;
use App\Models\SmsContact;
use App\Models\SmsContactGroup;
use App\Models\SmsProvider;
use App\Services\SMS\SmsManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BulkSmsController extends Controller
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
        $campaigns = SmsCampaign::where('user_id', $this->user->id)
            ->with('provider')
            ->latest()
            ->paginate(paginateNumber())
            ->appends($request->all());

        return view('user.bulk_sms.campaigns.index', [
            'meta_data'  => $this->metaData(['title' => translate('Bulk SMS Campaigns')]),
            'campaigns'  => $campaigns,
        ]);
    }

    public function create(): View
    {
        $providers = SmsProvider::where('user_id', $this->user->id)->where('is_active', true)->get();
        $groups    = SmsContactGroup::where('user_id', $this->user->id)->get();

        return view('user.bulk_sms.campaigns.create', [
            'meta_data' => $this->metaData(['title' => translate('New SMS Campaign')]),
            'providers' => $providers,
            'groups'    => $groups,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'name'            => 'required|string|max:255',
            'sms_provider_id' => 'required|integer|exists:sms_providers,id',
            'sender_id'       => 'nullable|string|max:15',
            'message'         => 'required|string|max:1600',
            'group_ids'       => 'required|array|min:1',
            'group_ids.*'     => 'integer|exists:sms_contact_groups,id',
            'scheduled_at'    => 'nullable|date|after:now',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Verify provider belongs to user
        $provider = SmsProvider::where('id', $request->sms_provider_id)
            ->where('user_id', $this->user->id)
            ->firstOrFail();

        // Verify all groups belong to user
        $groupIds = SmsContactGroup::where('user_id', $this->user->id)
            ->whereIn('id', $request->group_ids)
            ->pluck('id');

        DB::transaction(function () use ($request, $provider, $groupIds) {
            $campaign = SmsCampaign::create([
                'user_id'         => $this->user->id,
                'sms_provider_id' => $provider->id,
                'name'            => $request->name,
                'sender_id'       => $request->sender_id,
                'message'         => $request->message,
                'status'          => 'draft',
                'scheduled_at'    => $request->scheduled_at,
            ]);

            $campaign->groups()->attach($groupIds);
        });

        return redirect()->route('user.bulk-sms.index')
            ->with(response_status('Campaign created successfully.'));
    }

    public function show(string $uid): View
    {
        $campaign = SmsCampaign::where('uid', $uid)
            ->where('user_id', $this->user->id)
            ->with(['provider', 'groups', 'logs' => fn ($q) => $q->latest()->limit(100)])
            ->firstOrFail();

        return view('user.bulk_sms.campaigns.show', [
            'meta_data' => $this->metaData(['title' => translate("Campaign: {$campaign->name}")]),
            'campaign'  => $campaign,
        ]);
    }

    public function launch(string $uid): RedirectResponse
    {
        $campaign = SmsCampaign::where('uid', $uid)
            ->where('user_id', $this->user->id)
            ->firstOrFail();

        if (!$campaign->isDraft()) {
            return back()->with(response_status('Campaign is not in draft status.', 'error'));
        }

        if (!$campaign->sms_provider_id) {
            return back()->with(response_status('Please assign an SMS provider before launching.', 'error'));
        }

        $campaign->update(['status' => 'queued']);
        ProcessSmsCampaignJob::dispatch($campaign->id);

        return back()->with(response_status('Campaign launched and queued for processing.'));
    }

    public function destroy(string $uid): RedirectResponse
    {
        $campaign = SmsCampaign::where('uid', $uid)
            ->where('user_id', $this->user->id)
            ->firstOrFail();

        if ($campaign->status === 'processing') {
            return back()->with(response_status('Cannot delete a campaign that is currently sending.', 'error'));
        }

        $campaign->delete();

        return redirect()->route('user.bulk-sms.index')
            ->with(response_status('Campaign deleted.'));
    }

    // ─── Contacts ────────────────────────────────────────────────────────────────

    public function contacts(Request $request): View
    {
        $groups = SmsContactGroup::where('user_id', $this->user->id)
            ->withCount('contacts')
            ->latest()
            ->get();

        $selectedGroup = $request->input('group_id');
        $contacts = SmsContact::where('user_id', $this->user->id)
            ->when($selectedGroup, fn ($q) => $q->where('sms_contact_group_id', $selectedGroup))
            ->latest()
            ->paginate(paginateNumber())
            ->appends($request->all());

        return view('user.bulk_sms.contacts.index', [
            'meta_data'     => $this->metaData(['title' => translate('SMS Contacts')]),
            'groups'        => $groups,
            'contacts'      => $contacts,
            'selectedGroup' => $selectedGroup,
        ]);
    }

    public function storeContact(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'name'                 => 'nullable|string|max:255',
            'phone'                => 'required|string|max:20',
            'email'                => 'nullable|email|max:255',
            'sms_contact_group_id' => 'nullable|integer|exists:sms_contact_groups,id',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $existing = SmsContact::where('user_id', $this->user->id)
            ->where('phone', $request->phone)
            ->first();

        if ($existing) {
            return back()->with(response_status('A contact with this phone number already exists.', 'error'));
        }

        SmsContact::create([
            'user_id'              => $this->user->id,
            'sms_contact_group_id' => $request->sms_contact_group_id,
            'name'                 => $request->name,
            'phone'                => $request->phone,
            'email'                => $request->email,
        ]);

        // Update group count
        if ($request->sms_contact_group_id) {
            SmsContactGroup::where('id', $request->sms_contact_group_id)
                ->increment('contact_count');
        }

        return back()->with(response_status('Contact added.'));
    }

    public function importContacts(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'csv_file'             => 'required|file|mimes:csv,txt|max:5120',
            'sms_contact_group_id' => 'nullable|integer|exists:sms_contact_groups,id',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $file = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');
        $header = fgetcsv($handle); // first row = header
        $imported = 0;
        $skipped  = 0;

        DB::transaction(function () use ($handle, $header, $request, &$imported, &$skipped) {
            while (($row = fgetcsv($handle)) !== false) {
                $data = array_combine($header, $row);
                $phone = trim($data['phone'] ?? $data['Phone'] ?? $data['PHONE'] ?? '');
                if (!$phone) { $skipped++; continue; }

                $exists = SmsContact::where('user_id', $this->user->id)->where('phone', $phone)->exists();
                if ($exists) { $skipped++; continue; }

                SmsContact::create([
                    'user_id'              => $this->user->id,
                    'sms_contact_group_id' => $request->sms_contact_group_id,
                    'name'                 => trim($data['name'] ?? $data['Name'] ?? ''),
                    'phone'                => $phone,
                    'email'                => trim($data['email'] ?? $data['Email'] ?? '') ?: null,
                ]);
                $imported++;
            }

            // Update group count
            if ($request->sms_contact_group_id) {
                SmsContactGroup::where('id', $request->sms_contact_group_id)
                    ->increment('contact_count', $imported);
            }
        });

        fclose($handle);

        return back()->with(response_status("Imported {$imported} contacts. Skipped {$skipped} duplicates."));
    }

    public function destroyContact(int $id): RedirectResponse
    {
        $contact = SmsContact::where('id', $id)->where('user_id', $this->user->id)->firstOrFail();

        if ($contact->sms_contact_group_id) {
            SmsContactGroup::where('id', $contact->sms_contact_group_id)->decrement('contact_count');
        }

        $contact->delete();

        return back()->with(response_status('Contact deleted.'));
    }

    public function storeGroup(Request $request): RedirectResponse
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        SmsContactGroup::create([
            'user_id'     => $this->user->id,
            'name'        => $request->name,
            'description' => $request->description,
        ]);

        return back()->with(response_status('Group created.'));
    }

    public function destroyGroup(int $id): RedirectResponse
    {
        $group = SmsContactGroup::where('id', $id)->where('user_id', $this->user->id)->firstOrFail();
        $group->contacts()->update(['sms_contact_group_id' => null]);
        $group->delete();

        return back()->with(response_status('Group deleted.'));
    }

    // ─── Provider Management ─────────────────────────────────────────────────────

    public function provider(): View
    {
        $providers        = SmsProvider::where('user_id', $this->user->id)->latest()->get();
        $supportedDrivers = SmsManager::supportedProviders();

        return view('user.bulk_sms.provider.index', [
            'meta_data'        => $this->metaData(['title' => translate('SMS Provider')]),
            'providers'        => $providers,
            'supportedDrivers' => $supportedDrivers,
        ]);
    }

    public function storeProvider(Request $request): RedirectResponse
    {
        $supported = SmsManager::supportedProviders();

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
            SmsProvider::where('user_id', $this->user->id)->update(['is_default' => false]);
        }

        SmsProvider::create([
            'user_id'    => $this->user->id,
            'name'       => $request->name,
            'provider'   => $providerKey,
            'credentials'=> $credentials,
            'is_default' => $request->boolean('is_default'),
        ]);

        return back()->with(response_status('SMS provider added.'));
    }

    public function destroyProvider(int $id): RedirectResponse
    {
        SmsProvider::where('id', $id)->where('user_id', $this->user->id)->delete();

        return back()->with(response_status('Provider removed.'));
    }

    public function testProvider(int $id, Request $request): RedirectResponse
    {
        $request->validate(['test_phone' => 'required|string|max:20']);

        $provider = SmsProvider::where('id', $id)->where('user_id', $this->user->id)->firstOrFail();
        $manager  = new SmsManager();
        $driver   = $manager->driver($provider);

        $result = $driver->send($request->test_phone, 'This is a test message from ' . config('app.name') . '.', null);

        if ($result['success']) {
            return back()->with(response_status('Test SMS sent successfully!'));
        }

        return back()->with(response_status('Test failed: ' . ($result['error'] ?? 'Unknown error'), 'error'));
    }
}
