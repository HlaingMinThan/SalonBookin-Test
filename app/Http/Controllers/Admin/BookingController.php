<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class BookingController extends Controller
{
    public function index(): Response
    {
        $bookings = Booking::with('slot.service')
            ->latest()
            ->get()
            ->map(fn (Booking $booking) => [
                'id' => $booking->id,
                'customer_name' => $booking->customer_name,
                'customer_phone' => $booking->customer_phone,
                'status' => $booking->status->value,
                'date' => $booking->slot->date->format('Y-m-d'),
                'time' => $booking->slot->time,
                'service' => $booking->slot->service->name,
                'created_at' => $booking->created_at->format('Y-m-d\TH:i:s'),
            ]);

        return Inertia::render('Admin/Bookings/Index', [
            'bookings' => $bookings,
        ]);
    }

    public function approve(Booking $booking): RedirectResponse
    {
        $booking->update(['status' => BookingStatus::Approved]);

        return back();
    }

    public function reject(Booking $booking): RedirectResponse
    {
        $booking->update(['status' => BookingStatus::Rejected]);

        return back();
    }
}
