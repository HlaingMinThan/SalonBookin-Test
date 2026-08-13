<?php

use App\Models\Booking;

test('customer can view the lookup page', function () {
    $this->get(route('bookings.index'))->assertOk();
});

test('customer can look up bookings by phone', function () {
    Booking::factory()->create(['customer_phone' => '09123456789']);
    Booking::factory()->create(['customer_phone' => '09987654321']);

    $this->post(route('bookings.show'), ['phone' => '09123456789'])
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Bookings/Index')
            ->has('bookings', 1)
        );
});

test('lookup with no matching phone returns empty', function () {
    $this->post(route('bookings.show'), ['phone' => '00000000000'])
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('bookings', 0)
        );
});

test('phone is required for lookup', function () {
    $this->post(route('bookings.show'), [])
        ->assertSessionHasErrors('phone');
});
