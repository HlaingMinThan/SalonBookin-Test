<?php

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Service;
use App\Models\Slot;

test('customer can view the landing page', function () {
    $this->get(route('home'))->assertOk();
});

test('services api returns all services', function () {
    Service::factory()->create(['name' => 'Haircut']);
    Service::factory()->create(['name' => 'Manicure']);

    $this->getJson(route('api.services.index'))
        ->assertOk()
        ->assertJsonCount(2);
});

test('slots api returns available slots for a service', function () {
    $service = Service::factory()->create();
    $slot = Slot::factory()->create([
        'service_id' => $service->id,
        'date' => now()->addDay()->format('Y-m-d'),
        'time' => '09:00',
    ]);

    $this->getJson(route('api.slots.index', ['service_id' => $service->id]))
        ->assertOk()
        ->assertJsonCount(1);
});

test('slots api excludes booked slots', function () {
    $service = Service::factory()->create();
    $slot = Slot::factory()->create([
        'service_id' => $service->id,
        'date' => now()->addDay()->format('Y-m-d'),
        'time' => '09:00',
    ]);

    Booking::factory()->create(['slot_id' => $slot->id, 'status' => BookingStatus::Pending]);

    $this->getJson(route('api.slots.index', ['service_id' => $service->id]))
        ->assertOk()
        ->assertJsonCount(0);
});

test('customer can book an available slot', function () {
    $slot = Slot::factory()->create();

    $this->post(route('book.store'), [
        'slot_id' => $slot->id,
        'customer_name' => 'John Doe',
        'customer_phone' => '09123456789',
    ])->assertRedirect();

    $this->assertDatabaseHas('bookings', [
        'slot_id' => $slot->id,
        'customer_name' => 'John Doe',
        'customer_phone' => '09123456789',
        'status' => BookingStatus::Pending->value,
    ]);
});

test('customer cannot book an already booked slot', function () {
    $slot = Slot::factory()->create();
    Booking::factory()->create(['slot_id' => $slot->id]);

    $this->post(route('book.store'), [
        'slot_id' => $slot->id,
        'customer_name' => 'Jane Doe',
        'customer_phone' => '09987654321',
    ])->assertSessionHasErrors('slot_id');
});

test('booking requires name and phone', function () {
    $slot = Slot::factory()->create();

    $this->post(route('book.store'), [
        'slot_id' => $slot->id,
    ])->assertSessionHasErrors(['customer_name', 'customer_phone']);
});

test('customer can view booking confirmation', function () {
    $booking = Booking::factory()->create();

    $this->get(route('book.show', $booking))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Book/Show'));
});
