<?php

use App\Models\Service;
use App\Models\Slot;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create(['email' => config('app.admin_email')]);
    $this->service = Service::factory()->create(['name' => 'Haircut']);
});

test('admin can view slots page', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.slots.index'))
        ->assertOk();
});

test('non-admin cannot view slots page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.slots.index'))
        ->assertForbidden();
});

test('admin can create a slot', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.slots.store'), [
            'service_id' => $this->service->id,
            'date' => now()->addDay()->format('Y-m-d'),
            'time' => '09:00',
        ])
        ->assertRedirect();

    $this->assertDatabaseCount('slots', 1);
});

test('slot requires service, date, and time', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.slots.store'), [])
        ->assertSessionHasErrors(['service_id', 'date', 'time']);
});

test('slot date must be today or later', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.slots.store'), [
            'service_id' => $this->service->id,
            'date' => now()->subDay()->format('Y-m-d'),
            'time' => '09:00',
        ])
        ->assertSessionHasErrors('date');
});

test('duplicate slot for same service, date, time is rejected', function () {
    $date = now()->addDay()->format('Y-m-d');

    Slot::factory()->create([
        'service_id' => $this->service->id,
        'date' => $date,
        'time' => '09:00',
    ]);

    $this->actingAs($this->admin)
        ->post(route('admin.slots.store'), [
            'service_id' => $this->service->id,
            'date' => $date,
            'time' => '09:00',
        ])
        ->assertSessionHasErrors('time');
});

test('admin can delete a slot', function () {
    $slot = Slot::factory()->create(['service_id' => $this->service->id]);

    $this->actingAs($this->admin)
        ->delete(route('admin.slots.destroy', $slot))
        ->assertRedirect();

    $this->assertDatabaseMissing('slots', ['id' => $slot->id]);
});
