<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\DripEnrollment;
use App\Models\DripSequence;
use App\Models\DripStep;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DripSequenceController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->user = auth_user('web');
            return $next($request);
        });
    }

    /**
     * List all drip sequences for the user.
     *
     * @return View
     */
    public function index(): View
    {
        $sequences = DripSequence::where('user_id', $this->user->id)
            ->withCount([
                'steps',
                'enrollments',
                'enrollments as active_count' => fn ($q) => $q->where('status', 'active'),
            ])
            ->latest()
            ->paginate(paginateNumber());

        return view('user.drip.index', [
            'meta_data' => $this->metaData(['title' => 'Drip Sequences']),
            'sequences' => $sequences,
        ]);
    }

    /**
     * Show the create sequence form.
     *
     * @return View
     */
    public function create(): View
    {
        return view('user.drip.create', [
            'meta_data' => $this->metaData(['title' => 'Create Sequence']),
            'sequence'  => null,
        ]);
    }

    /**
     * Store a new drip sequence.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'description'   => 'nullable|string',
            'trigger_type'  => 'required|in:manual,lead_created,tag_added,chatbot_end,whatsapp_optin',
            'trigger_value' => 'nullable|string|max:255',
        ]);

        $sequence = DripSequence::create(array_merge($validated, [
            'user_id' => $this->user->id,
            'status'  => 'draft',
        ]));

        return redirect()->route('user.drip.show', $sequence->uid)
            ->with(response_status('Drip sequence created successfully.'));
    }

    /**
     * Show sequence detail with steps and enrollments.
     *
     * @param string $uid
     * @return View
     */
    public function show(string $uid): View
    {
        $sequence = DripSequence::where('uid', $uid)
            ->where('user_id', $this->user->id)
            ->with('steps')
            ->firstOrFail();

        $enrollments = DripEnrollment::where('sequence_id', $sequence->id)
            ->latest()
            ->paginate(20);

        return view('user.drip.show', [
            'meta_data'   => $this->metaData(['title' => $sequence->name]),
            'sequence'    => $sequence,
            'steps'       => $sequence->steps,
            'enrollments' => $enrollments,
        ]);
    }

    /**
     * Show the edit form for a sequence.
     *
     * @param string $uid
     * @return View
     */
    public function edit(string $uid): View
    {
        $sequence = DripSequence::where('uid', $uid)
            ->where('user_id', $this->user->id)
            ->firstOrFail();

        return view('user.drip.create', [
            'meta_data' => $this->metaData(['title' => 'Edit Sequence']),
            'sequence'  => $sequence,
        ]);
    }

    /**
     * Update a drip sequence.
     *
     * @param Request $request
     * @param string  $uid
     * @return RedirectResponse
     */
    public function update(Request $request, string $uid): RedirectResponse
    {
        $sequence = DripSequence::where('uid', $uid)
            ->where('user_id', $this->user->id)
            ->firstOrFail();

        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'description'   => 'nullable|string',
            'trigger_type'  => 'required|in:manual,lead_created,tag_added,chatbot_end,whatsapp_optin',
            'trigger_value' => 'nullable|string|max:255',
        ]);

        $sequence->update($validated);

        return back()->with(response_status('Drip sequence updated successfully.'));
    }

    /**
     * Delete a drip sequence.
     *
     * @param string $uid
     * @return RedirectResponse
     */
    public function destroy(string $uid): RedirectResponse
    {
        $sequence = DripSequence::where('uid', $uid)
            ->where('user_id', $this->user->id)
            ->firstOrFail();

        $sequence->delete();

        return redirect()->route('user.drip.index')
            ->with(response_status('Drip sequence deleted.'));
    }

    /**
     * Activate a sequence.
     *
     * @param string $uid
     * @return RedirectResponse
     */
    public function activate(string $uid): RedirectResponse
    {
        $sequence = DripSequence::where('uid', $uid)
            ->where('user_id', $this->user->id)
            ->firstOrFail();

        if ($sequence->steps()->count() === 0) {
            return back()->with(response_status('Add at least one step first.', 'error'));
        }

        $sequence->update(['status' => 'active']);

        return back()->with(response_status('Sequence activated successfully.'));
    }

    /**
     * Pause a sequence.
     *
     * @param string $uid
     * @return RedirectResponse
     */
    public function pause(string $uid): RedirectResponse
    {
        $sequence = DripSequence::where('uid', $uid)
            ->where('user_id', $this->user->id)
            ->firstOrFail();

        $sequence->update(['status' => 'paused']);

        return back()->with(response_status('Sequence paused.'));
    }

    /**
     * Add a step to a sequence.
     *
     * @param Request $request
     * @param string  $uid
     * @return RedirectResponse
     */
    public function storeStep(Request $request, string $uid): RedirectResponse
    {
        $sequence = DripSequence::where('uid', $uid)
            ->where('user_id', $this->user->id)
            ->firstOrFail();

        $validated = $request->validate([
            'channel'     => 'required|in:email,sms,whatsapp',
            'delay_days'  => 'integer|min:0',
            'delay_hours' => 'integer|min:0',
            'subject'     => 'nullable|string|max:255',
            'message'     => 'required|string',
            'from_name'   => 'nullable|string|max:255',
            'from_email'  => 'nullable|email|max:255',
        ]);

        $maxPosition = $sequence->steps()->max('position') ?? 0;

        DripStep::create(array_merge($validated, [
            'sequence_id' => $sequence->id,
            'position'    => $maxPosition + 1,
        ]));

        return back()->with(response_status('Step added successfully.'));
    }

    /**
     * Delete a step from a sequence.
     *
     * @param string $uid
     * @param int    $stepId
     * @return RedirectResponse
     */
    public function destroyStep(string $uid, int $stepId): RedirectResponse
    {
        $step = DripStep::whereHas('sequence', function ($q) use ($uid) {
            $q->where('uid', $uid)->where('user_id', $this->user->id);
        })->where('id', $stepId)->firstOrFail();

        $step->delete();

        return back()->with(response_status('Step deleted.'));
    }

    /**
     * Reorder steps via AJAX.
     *
     * @param Request $request
     * @param string  $uid
     * @return JsonResponse
     */
    public function reorderSteps(Request $request, string $uid): JsonResponse
    {
        $sequence = DripSequence::where('uid', $uid)
            ->where('user_id', $this->user->id)
            ->firstOrFail();

        $request->validate([
            'order'   => 'required|array',
            'order.*' => 'integer',
        ]);

        foreach ($request->order as $index => $id) {
            DripStep::where('id', $id)
                ->where('sequence_id', $sequence->id)
                ->update(['position' => $index]);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Steps reordered successfully.',
        ]);
    }

    /**
     * Enroll a contact in a sequence.
     *
     * @param Request $request
     * @param string  $uid
     * @return RedirectResponse
     */
    public function enroll(Request $request, string $uid): RedirectResponse
    {
        $sequence = DripSequence::where('uid', $uid)
            ->where('user_id', $this->user->id)
            ->where('status', 'active')
            ->firstOrFail();

        $validated = $request->validate([
            'contact_name'  => 'nullable|string|max:255',
            'contact_email' => 'required_without:contact_phone|nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'crm_lead_id'   => 'nullable|exists:crm_leads,id',
        ]);

        // Check if already enrolled with same email
        if (!empty($validated['contact_email'])) {
            $alreadyEnrolled = DripEnrollment::where('sequence_id', $sequence->id)
                ->where('contact_email', $validated['contact_email'])
                ->where('status', 'active')
                ->exists();

            if ($alreadyEnrolled) {
                return back()->with(response_status('This contact is already enrolled in the sequence.', 'error'));
            }
        }

        $firstStep = $sequence->steps()->orderBy('position')->first();

        if (!$firstStep) {
            return back()->with(response_status('Cannot enroll: the sequence has no steps.', 'error'));
        }

        $nextSendAt = now()
            ->addDays($firstStep->delay_days ?? 0)
            ->addHours($firstStep->delay_hours ?? 0);

        DripEnrollment::create([
            'sequence_id'   => $sequence->id,
            'contact_name'  => $validated['contact_name'] ?? null,
            'contact_email' => $validated['contact_email'] ?? null,
            'contact_phone' => $validated['contact_phone'] ?? null,
            'crm_lead_id'   => $validated['crm_lead_id'] ?? null,
            'current_step'  => $firstStep->id,
            'next_send_at'  => $nextSendAt,
            'status'        => 'active',
        ]);

        $sequence->increment('total_enrolled');

        return back()->with(response_status('Contact enrolled in sequence.'));
    }

    /**
     * Unenroll / unsubscribe a contact.
     *
     * @param int $id
     * @return RedirectResponse
     */
    public function unenroll(int $id): RedirectResponse
    {
        $enrollment = DripEnrollment::whereHas('sequence', fn ($q) => $q->where('user_id', $this->user->id))
            ->where('id', $id)
            ->firstOrFail();

        $enrollment->update(['status' => 'unsubscribed']);

        return back()->with(response_status('Contact unsubscribed from sequence.'));
    }

    /**
     * Return demo sequence JSON for auto-populate UI.
     *
     * @return JsonResponse
     */
    public function demoData(): JsonResponse
    {
        $demoSequence = [
            'name'          => 'Welcome Sequence',
            'description'   => 'Automated onboarding sequence for new leads.',
            'trigger_type'  => 'lead_created',
            'trigger_value' => null,
            'steps'         => [
                [
                    'channel'     => 'email',
                    'delay_days'  => 0,
                    'delay_hours' => 0,
                    'subject'     => 'Welcome! Here is what to expect',
                    'message'     => "Hi {{contact_name}},\n\nWelcome aboard! We're excited to have you.\n\nBest regards,\nThe Team",
                    'from_name'   => 'The Team',
                    'from_email'  => 'hello@example.com',
                ],
                [
                    'channel'     => 'email',
                    'delay_days'  => 2,
                    'delay_hours' => 0,
                    'subject'     => 'Quick tip to get started',
                    'message'     => "Hi {{contact_name}},\n\nHere is a quick tip to help you get the most out of our platform...\n\nBest,\nThe Team",
                    'from_name'   => 'The Team',
                    'from_email'  => 'hello@example.com',
                ],
                [
                    'channel'     => 'whatsapp',
                    'delay_days'  => 5,
                    'delay_hours' => 0,
                    'subject'     => null,
                    'message'     => "Hey {{contact_name}}! Just checking in. Let us know if you have any questions.",
                    'from_name'   => null,
                    'from_email'  => null,
                ],
            ],
        ];

        return response()->json([
            'status'   => true,
            'sequence' => $demoSequence,
        ]);
    }
}
