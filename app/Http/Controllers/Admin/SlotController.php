<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Slot;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SlotController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Slots/Index', [
            'slots' => Slot::with('service', 'activeBooking')
                ->orderBy('date')
                ->orderBy('time')
                ->get()
                ->map(fn (Slot $slot) => [
                    'id' => $slot->id,
                    'date' => $slot->date->format('Y-m-d'),
                    'time' => $slot->time,
                    'service' => $slot->service->name,
                    'is_available' => $slot->isAvailable(),
                ]),
            'services' => Service::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'service_id' => ['required', 'exists:services,id'],
            'date' => ['required', 'date', 'after_or_equal:today'],
            'time' => ['required', 'date_format:H:i'],
        ]);

        $exists = Slot::where('service_id', $validated['service_id'])
            ->whereDate('date', $validated['date'])
            ->where('time', $validated['time'])
            ->exists();

        if ($exists) {
            return back()->withErrors(['time' => 'A slot already exists for this service, date, and time.']);
        }

        Slot::create($validated);

        return back();
    }

    public function destroy(Slot $slot): RedirectResponse
    {
        $slot->delete();

        return back();
    }
}
