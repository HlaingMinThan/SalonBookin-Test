<?php

namespace App\Http\Controllers;

use App\Models\Slot;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SlotApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'date' => ['sometimes', 'date'],
            'service_id' => ['sometimes', 'exists:services,id'],
        ]);

        $query = Slot::with('service')
            ->whereDate('date', '>=', now()->toDateString())
            ->available()
            ->orderBy('date')
            ->orderBy('time');

        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }

        if ($request->filled('service_id')) {
            $query->where('service_id', $request->service_id);
        }

        $slots = $query->get()->map(fn (Slot $slot) => [
            'id' => $slot->id,
            'date' => $slot->date->format('Y-m-d'),
            'time' => $slot->time,
            'service_id' => $slot->service_id,
            'service_name' => $slot->service->name,
        ]);

        return response()->json($slots);
    }
}
