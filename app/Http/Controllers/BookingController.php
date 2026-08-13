<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Slot;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class BookingController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'slot_id' => ['required', 'exists:slots,id'],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:50'],
        ]);

        $booking = DB::transaction(function () use ($validated) {
            $slot = Slot::lockForUpdate()->findOrFail($validated['slot_id']);

            if (! $slot->isAvailable()) {
                return null;
            }

            return Booking::create($validated);
        });

        if (! $booking) {
            return back()->withErrors(['slot_id' => 'This slot is no longer available.']);
        }

        return redirect()->route('book.show', $booking);
    }

    public function show(Booking $booking): Response
    {
        $booking->load('slot.service');

        return Inertia::render('Book/Show', [
            'booking' => [
                'id' => $booking->id,
                'customer_name' => $booking->customer_name,
                'customer_phone' => $booking->customer_phone,
                'status' => $booking->status->value,
                'date' => $booking->slot->date->format('Y-m-d'),
                'time' => $booking->slot->time,
                'service' => $booking->slot->service->name,
            ],
        ]);
    }
}
