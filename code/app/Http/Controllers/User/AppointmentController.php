<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Jobs\SendAppointmentConfirmationJob;
use App\Models\Appointment;
use App\Models\AppointmentService;
use App\Models\AppointmentWorkingHours;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Str;

class AppointmentController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->user = auth_user('web');
            return $next($request);
        });
    }

    // ── Appointments list ─────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $query = Appointment::where('user_id', $this->user->id)->with('service');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date')) {
            $query->whereDate('appointment_at', $request->date);
        }

        $appointments = $query->orderBy('appointment_at', 'desc')
            ->paginate(paginateNumber())
            ->appends($request->all());

        $stats = [
            'total'     => Appointment::where('user_id', $this->user->id)->count(),
            'today'     => Appointment::where('user_id', $this->user->id)->whereDate('appointment_at', today())->count(),
            'upcoming'  => Appointment::where('user_id', $this->user->id)->where('appointment_at', '>', now())->where('status', '!=', 'cancelled')->count(),
            'confirmed' => Appointment::where('user_id', $this->user->id)->where('status', 'confirmed')->count(),
        ];

        return view('user.appointments.index', [
            'meta_data'    => $this->metaData(['title' => translate('Appointments')]),
            'appointments' => $appointments,
            'stats'        => $stats,
        ]);
    }

    public function create(): View
    {
        $services = AppointmentService::where('user_id', $this->user->id)->where('is_active', 1)->get();

        return view('user.appointments.create', [
            'meta_data' => $this->metaData(['title' => translate('New Appointment')]),
            'services'  => $services,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'contact_name'   => 'required|string|max:255',
            'contact_email'  => 'nullable|email|max:255',
            'contact_phone'  => 'nullable|string|max:30',
            'service_id'     => 'nullable|exists:appointment_services,id',
            'appointment_at' => 'required|date|after:now',
            'notes'          => 'nullable|string|max:1000',
        ]);

        $appointment = Appointment::create([
            'user_id'        => $this->user->id,
            'service_id'     => $request->service_id,
            'contact_name'   => $request->contact_name,
            'contact_email'  => $request->contact_email,
            'contact_phone'  => $request->contact_phone,
            'appointment_at' => $request->appointment_at,
            'notes'          => $request->notes,
            'status'         => 'confirmed',
            'booked_via'     => 'dashboard',
        ]);

        SendAppointmentConfirmationJob::dispatch($appointment->id);

        return redirect()->route('user.appointments.index')
            ->with(response_status('Appointment created and confirmation sent.'));
    }

    public function show(string $uid): View
    {
        $appointment = Appointment::where('uid', $uid)
            ->where('user_id', $this->user->id)
            ->with('service')
            ->firstOrFail();

        return view('user.appointments.show', [
            'meta_data'   => $this->metaData(['title' => translate('Appointment Details')]),
            'appointment' => $appointment,
        ]);
    }

    public function updateStatus(Request $request, string $uid): RedirectResponse
    {
        $request->validate(['status' => 'required|in:pending,confirmed,cancelled,completed,no_show']);

        $appointment = Appointment::where('uid', $uid)
            ->where('user_id', $this->user->id)
            ->firstOrFail();

        $appointment->update(['status' => $request->status]);

        return back()->with(response_status('Status updated.'));
    }

    public function destroy(string $uid): RedirectResponse
    {
        Appointment::where('uid', $uid)->where('user_id', $this->user->id)->delete();

        return redirect()->route('user.appointments.index')
            ->with(response_status('Appointment deleted.'));
    }

    // ── Services ──────────────────────────────────────────────────────────────

    public function services(): View
    {
        $services = AppointmentService::where('user_id', $this->user->id)->latest()->get();

        return view('user.appointments.services', [
            'meta_data' => $this->metaData(['title' => translate('Appointment Services')]),
            'services'  => $services,
        ]);
    }

    public function storeService(Request $request): RedirectResponse
    {
        $request->validate([
            'name'             => 'required|string|max:255',
            'description'      => 'nullable|string|max:500',
            'duration_minutes' => 'required|integer|min:5|max:480',
            'price'            => 'nullable|numeric|min:0',
            'currency'         => 'nullable|string|max:10',
        ]);

        AppointmentService::create([
            'user_id'          => $this->user->id,
            'name'             => $request->name,
            'description'      => $request->description,
            'duration_minutes' => $request->duration_minutes,
            'price'            => $request->price,
            'currency'         => $request->currency ?? 'USD',
            'is_active'        => 1,
        ]);

        return back()->with(response_status('Service added.'));
    }

    public function destroyService(int $id): RedirectResponse
    {
        AppointmentService::where('id', $id)->where('user_id', $this->user->id)->delete();

        return back()->with(response_status('Service removed.'));
    }

    public function toggleService(int $id): RedirectResponse
    {
        $service = AppointmentService::where('id', $id)->where('user_id', $this->user->id)->firstOrFail();
        $service->update(['is_active' => !$service->is_active]);

        return back()->with(response_status('Service ' . ($service->is_active ? 'enabled' : 'disabled') . '.'));
    }
}
