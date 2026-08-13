<?php

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create(['email' => config('app.admin_email')]);
});

test('admin can view bookings page', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.bookings.index'))
        ->assertOk();
});

test('non-admin cannot view bookings page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.bookings.index'))
        ->assertForbidden();
});

test('admin can approve a pending booking', function () {
    $booking = Booking::factory()->create(['status' => BookingStatus::Pending]);

    $this->actingAs($this->admin)
        ->put(route('admin.bookings.approve', $booking))
        ->assertRedirect();

    expect($booking->fresh()->status)->toBe(BookingStatus::Approved);
});

test('admin can reject a pending booking', function () {
    $booking = Booking::factory()->create(['status' => BookingStatus::Pending]);

    $this->actingAs($this->admin)
        ->put(route('admin.bookings.reject', $booking))
        ->assertRedirect();

    expect($booking->fresh()->status)->toBe(BookingStatus::Rejected);
});
