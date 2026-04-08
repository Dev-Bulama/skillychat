<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\CrmLead;
use App\Models\CrmLeadActivity;
use App\Models\CrmPipeline;
use App\Models\CrmStage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CrmController extends Controller
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
     * Kanban board — show the default pipeline.
     *
     * @return View
     */
    public function index(): View
    {
        $pipeline = CrmPipeline::where('user_id', $this->user->id)
            ->where('is_default', 1)
            ->with(['stages.leads' => fn ($q) => $q->where('status', 'active')])
            ->first();

        if (!$pipeline) {
            $pipeline = CrmPipeline::where('user_id', $this->user->id)
                ->with(['stages.leads' => fn ($q) => $q->where('status', 'active')])
                ->first();
        }

        $pipelines = CrmPipeline::where('user_id', $this->user->id)->get();

        return view('user.crm.index', [
            'meta_data' => $this->metaData(['title' => 'CRM Board']),
            'pipeline'  => $pipeline,
            'pipelines' => $pipelines,
        ]);
    }

    /**
     * List all leads with filters.
     *
     * @return View
     */
    public function leads(): View
    {
        $leads = CrmLead::where('user_id', $this->user->id)
            ->with(['stage', 'pipeline'])
            ->when(request('stage_id'), fn ($q) => $q->where('stage_id', request('stage_id')))
            ->when(request('status'),   fn ($q) => $q->where('status', request('status')))
            ->when(request('search'),   fn ($q) => $q->where(fn ($q2) =>
                $q2->where('name',  'like', '%' . request('search') . '%')
                   ->orWhere('email', 'like', '%' . request('search') . '%')
                   ->orWhere('phone', 'like', '%' . request('search') . '%')
            ))
            ->latest()
            ->paginate(paginateNumber());

        $stages = CrmStage::whereHas('pipeline', fn ($q) => $q->where('user_id', $this->user->id))->get();

        return view('user.crm.leads', [
            'meta_data' => $this->metaData(['title' => 'Leads']),
            'leads'     => $leads,
            'stages'    => $stages,
        ]);
    }

    /**
     * Show the create-lead form.
     *
     * @return View
     */
    public function create(): View
    {
        $pipelines = CrmPipeline::where('user_id', $this->user->id)->with('stages')->get();

        return view('user.crm.form', [
            'meta_data' => $this->metaData(['title' => 'Add Lead']),
            'lead'      => null,
            'pipelines' => $pipelines,
        ]);
    }

    /**
     * Store a new lead.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'nullable|email|max:255',
            'phone'       => 'nullable|string|max:50',
            'company'     => 'nullable|string|max:255',
            'pipeline_id' => 'required|exists:crm_pipelines,id',
            'stage_id'    => 'required|exists:crm_stages,id',
            'deal_value'  => 'nullable|numeric|min:0',
            'source'      => 'nullable|in:manual,chatbot,whatsapp,form,import',
            'priority'    => 'nullable|in:low,medium,high',
            'notes'       => 'nullable|string',
            'tags'        => 'nullable|string',
            'follow_up_at' => 'nullable|date',
        ]);

        // Ensure the pipeline belongs to this user
        $pipeline = CrmPipeline::where('id', $validated['pipeline_id'])
            ->where('user_id', $this->user->id)
            ->firstOrFail();

        $lead = CrmLead::create(array_merge($validated, [
            'user_id' => $this->user->id,
        ]));

        CrmLeadActivity::create([
            'lead_id'     => $lead->id,
            'user_id'     => $this->user->id,
            'type'        => 'note',
            'title'       => 'Lead Created',
            'description' => 'Lead created manually',
        ]);

        return redirect()->route('user.crm.show', $lead->uid)
            ->with(response_status('Lead created successfully.'));
    }

    /**
     * Show lead detail.
     *
     * @param string $uid
     * @return View
     */
    public function show(string $uid): View
    {
        $lead = CrmLead::where('uid', $uid)
            ->where('user_id', $this->user->id)
            ->with(['stage', 'pipeline', 'activities.user', 'enrollments.sequence', 'messages'])
            ->firstOrFail();

        $pipelines = CrmPipeline::where('user_id', $this->user->id)->with('stages')->get();

        return view('user.crm.show', [
            'meta_data' => $this->metaData(['title' => $lead->name]),
            'lead'      => $lead,
            'pipelines' => $pipelines,
        ]);
    }

    /**
     * Show the edit form for a lead.
     *
     * @param string $uid
     * @return View
     */
    public function edit(string $uid): View
    {
        $lead = CrmLead::where('uid', $uid)
            ->where('user_id', $this->user->id)
            ->firstOrFail();

        $pipelines = CrmPipeline::where('user_id', $this->user->id)->with('stages')->get();

        return view('user.crm.form', [
            'meta_data' => $this->metaData(['title' => 'Edit Lead']),
            'lead'      => $lead,
            'pipelines' => $pipelines,
        ]);
    }

    /**
     * Update a lead.
     *
     * @param Request $request
     * @param string  $uid
     * @return RedirectResponse
     */
    public function update(Request $request, string $uid): RedirectResponse
    {
        $lead = CrmLead::where('uid', $uid)
            ->where('user_id', $this->user->id)
            ->firstOrFail();

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'nullable|email|max:255',
            'phone'       => 'nullable|string|max:50',
            'company'     => 'nullable|string|max:255',
            'pipeline_id' => 'required|exists:crm_pipelines,id',
            'stage_id'    => 'required|exists:crm_stages,id',
            'deal_value'  => 'nullable|numeric|min:0',
            'source'      => 'nullable|in:manual,chatbot,whatsapp,form,import',
            'priority'    => 'nullable|in:low,medium,high',
            'notes'       => 'nullable|string',
            'tags'        => 'nullable|string',
            'follow_up_at' => 'nullable|date',
        ]);

        $lead->update($validated);

        CrmLeadActivity::create([
            'lead_id'     => $lead->id,
            'user_id'     => $this->user->id,
            'type'        => 'note',
            'title'       => 'Lead Updated',
            'description' => 'Lead details were updated',
        ]);

        return back()->with(response_status('Lead updated successfully.'));
    }

    /**
     * Delete a lead.
     *
     * @param string $uid
     * @return RedirectResponse
     */
    public function destroy(string $uid): RedirectResponse
    {
        $lead = CrmLead::where('uid', $uid)
            ->where('user_id', $this->user->id)
            ->firstOrFail();

        $lead->delete();

        return redirect()->route('user.crm.leads')
            ->with(response_status('Lead deleted.'));
    }

    /**
     * AJAX: update a lead's stage (Kanban drag-drop).
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateStage(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'uid'      => 'required|string',
            'stage_id' => 'required|exists:crm_stages,id',
        ]);

        $lead = CrmLead::where('uid', $validated['uid'])
            ->where('user_id', $this->user->id)
            ->with('stage')
            ->firstOrFail();

        $oldStageName = $lead->stage ? $lead->stage->name : 'Unknown';

        $lead->update(['stage_id' => $validated['stage_id']]);

        $newStage = CrmStage::find($validated['stage_id']);
        $newStageName = $newStage ? $newStage->name : 'Unknown';

        CrmLeadActivity::create([
            'lead_id'     => $lead->id,
            'user_id'     => $this->user->id,
            'type'        => 'stage_change',
            'title'       => 'Stage Changed',
            'description' => "Moved from {$oldStageName} to {$newStageName}",
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Stage updated successfully.',
        ]);
    }

    /**
     * AJAX: add a note to a lead.
     *
     * @param Request $request
     * @param string  $uid
     * @return JsonResponse
     */
    public function addNote(Request $request, string $uid): JsonResponse
    {
        $lead = CrmLead::where('uid', $uid)
            ->where('user_id', $this->user->id)
            ->firstOrFail();

        $validated = $request->validate([
            'note' => 'required|string|max:1000',
        ]);

        $activity = CrmLeadActivity::create([
            'lead_id'     => $lead->id,
            'user_id'     => $this->user->id,
            'type'        => 'note',
            'title'       => 'Note Added',
            'description' => $validated['note'],
        ]);

        return response()->json([
            'status'   => true,
            'message'  => 'Note added successfully.',
            'activity' => $activity,
        ]);
    }

    /**
     * Manage pipelines and stages.
     *
     * @return View
     */
    public function pipelineSetup(): View
    {
        $pipelines = CrmPipeline::where('user_id', $this->user->id)->with('stages')->get();

        return view('user.crm.pipeline_setup', [
            'meta_data' => $this->metaData(['title' => 'Pipeline Setup']),
            'pipelines' => $pipelines,
        ]);
    }

    /**
     * Store a new pipeline with default stages.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function storePipeline(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $hasDefault = CrmPipeline::where('user_id', $this->user->id)
            ->where('is_default', 1)
            ->exists();

        $pipeline = CrmPipeline::create([
            'user_id'    => $this->user->id,
            'name'       => $validated['name'],
            'is_default' => $hasDefault ? 0 : 1,
        ]);

        // Create default stages
        $defaultStages = [
            ['name' => 'New Lead',       'color' => '#3b82f6', 'is_won' => false, 'is_lost' => false],
            ['name' => 'Contacted',      'color' => '#f59e0b', 'is_won' => false, 'is_lost' => false],
            ['name' => 'Qualified',      'color' => '#8b5cf6', 'is_won' => false, 'is_lost' => false],
            ['name' => 'Proposal Sent',  'color' => '#06b6d4', 'is_won' => false, 'is_lost' => false],
            ['name' => 'Won',            'color' => '#10b981', 'is_won' => true,  'is_lost' => false],
            ['name' => 'Lost',           'color' => '#ef4444', 'is_won' => false, 'is_lost' => true],
        ];

        foreach ($defaultStages as $position => $stageData) {
            CrmStage::create([
                'pipeline_id' => $pipeline->id,
                'name'        => $stageData['name'],
                'color'       => $stageData['color'],
                'is_won'      => $stageData['is_won'],
                'is_lost'     => $stageData['is_lost'],
                'position'    => $position + 1,
            ]);
        }

        return back()->with(response_status('Pipeline created successfully.'));
    }

    /**
     * Delete a pipeline.
     *
     * @param string $uid
     * @return RedirectResponse
     */
    public function destroyPipeline(string $uid): RedirectResponse
    {
        $pipeline = CrmPipeline::where('uid', $uid)
            ->where('user_id', $this->user->id)
            ->firstOrFail();

        $pipeline->delete();

        return back()->with(response_status('Pipeline deleted.'));
    }

    /**
     * Add a custom stage to a pipeline.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function storeStage(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'pipeline_id' => 'required|integer',
            'name'        => 'required|string|max:255',
            'color'       => 'nullable|string|max:20',
            'is_won'      => 'boolean',
            'is_lost'     => 'boolean',
        ]);

        // Verify the pipeline belongs to this user
        $pipeline = CrmPipeline::where('id', $validated['pipeline_id'])
            ->where('user_id', $this->user->id)
            ->firstOrFail();

        $maxPosition = CrmStage::where('pipeline_id', $pipeline->id)->max('position') ?? 0;

        CrmStage::create([
            'pipeline_id' => $pipeline->id,
            'name'        => $validated['name'],
            'color'       => $validated['color'] ?? '#6c757d',
            'is_won'      => $validated['is_won'] ?? false,
            'is_lost'     => $validated['is_lost'] ?? false,
            'position'    => $maxPosition + 1,
        ]);

        return back()->with(response_status('Stage added successfully.'));
    }

    /**
     * Delete a stage.
     *
     * @param int $id
     * @return RedirectResponse
     */
    public function destroyStage(int $id): RedirectResponse
    {
        $stage = CrmStage::whereHas('pipeline', fn ($q) => $q->where('user_id', $this->user->id))
            ->where('id', $id)
            ->firstOrFail();

        $stage->delete();

        return back()->with(response_status('Stage deleted.'));
    }

    /**
     * Return demo pipeline + leads JSON for auto-populate UI.
     *
     * @return JsonResponse
     */
    public function demoData(): JsonResponse
    {
        $demoPipeline = [
            'name'   => 'Sales Pipeline',
            'stages' => [
                ['name' => 'New Lead',      'color' => '#3b82f6'],
                ['name' => 'Contacted',     'color' => '#f59e0b'],
                ['name' => 'Qualified',     'color' => '#8b5cf6'],
                ['name' => 'Proposal Sent', 'color' => '#06b6d4'],
                ['name' => 'Won',           'color' => '#10b981'],
                ['name' => 'Lost',          'color' => '#ef4444'],
            ],
        ];

        $demoLeads = [
            ['name' => 'Alice Johnson', 'email' => 'alice@example.com', 'phone' => '+1-555-0101', 'company' => 'Acme Corp',    'deal_value' => 5000,  'priority' => 'high',   'source' => 'manual'],
            ['name' => 'Bob Smith',     'email' => 'bob@example.com',   'phone' => '+1-555-0102', 'company' => 'Beta Ltd',     'deal_value' => 2500,  'priority' => 'medium', 'source' => 'chatbot'],
            ['name' => 'Carol White',   'email' => 'carol@example.com', 'phone' => '+1-555-0103', 'company' => 'Gamma Inc',    'deal_value' => 12000, 'priority' => 'high',   'source' => 'whatsapp'],
            ['name' => 'David Lee',     'email' => 'david@example.com', 'phone' => '+1-555-0104', 'company' => 'Delta Co',     'deal_value' => 800,   'priority' => 'low',    'source' => 'form'],
            ['name' => 'Eva Brown',     'email' => 'eva@example.com',   'phone' => '+1-555-0105', 'company' => 'Epsilon LLC',  'deal_value' => 3200,  'priority' => 'medium', 'source' => 'import'],
        ];

        return response()->json([
            'status'   => true,
            'pipeline' => $demoPipeline,
            'leads'    => $demoLeads,
        ]);
    }
}
