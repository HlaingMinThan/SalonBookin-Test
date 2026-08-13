<?php

use App\Models\Service;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create(['email' => config('app.admin_email')]);
    $this->user = User::factory()->create();
});

test('admin can view services page', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.services.index'))
        ->assertOk();
});

test('non-admin cannot view services page', function () {
    $this->actingAs($this->user)
        ->get(route('admin.services.index'))
        ->assertForbidden();
});

test('guest cannot view services page', function () {
    $this->get(route('admin.services.index'))
        ->assertRedirect(route('login'));
});

test('admin can create a service', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.services.store'), ['name' => 'Haircut'])
        ->assertRedirect();

    $this->assertDatabaseHas('services', ['name' => 'Haircut']);
});

test('service name is required', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.services.store'), ['name' => ''])
        ->assertSessionHasErrors('name');
});

test('service name must be unique', function () {
    Service::factory()->create(['name' => 'Haircut']);

    $this->actingAs($this->admin)
        ->post(route('admin.services.store'), ['name' => 'Haircut'])
        ->assertSessionHasErrors('name');
});

test('admin can update a service', function () {
    $service = Service::factory()->create(['name' => 'Haircut']);

    $this->actingAs($this->admin)
        ->put(route('admin.services.update', $service), ['name' => 'Premium Haircut'])
        ->assertRedirect();

    expect($service->fresh()->name)->toBe('Premium Haircut');
});

test('admin can delete a service', function () {
    $service = Service::factory()->create();

    $this->actingAs($this->admin)
        ->delete(route('admin.services.destroy', $service))
        ->assertRedirect();

    $this->assertDatabaseMissing('services', ['id' => $service->id]);
});
