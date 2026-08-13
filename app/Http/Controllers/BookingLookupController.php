<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BookingLookupController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Bookings/Index', [
            'bookings' => [],
            'phone' => '',
        ]);
    }

    public function show(Request $request): Response
    {
        $validated = $request->validate([
            'phone' => ['required', 'string'],
        ]);

        $bookings = Booking::with('slot.service')
            ->where('customer_phone', $validated['phone'])
            ->latest()
            ->get()
            ->map(fn (Booking $booking) => [
                'id' => $booking->id,
                'customer_name' => $booking->customer_name,
                'status' => $booking->status->value,
                'date' => $booking->slot->date->format('Y-m-d'),
                'time' => $booking->slot->time,
                'service' => $booking->slot->service->name,
            ]);

        return Inertia::render('Bookings/Index', [
            'bookings' => $bookings,
            'phone' => $validated['phone'],
        ]);
    }
}
