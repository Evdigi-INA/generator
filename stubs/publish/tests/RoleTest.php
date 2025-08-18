<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\post;
use function Pest\Laravel\seed;

uses(RefreshDatabase::class);

beforeEach(function () {
    seed();

    /** @var \Illuminate\Contracts\Auth\Authenticatable */
    $this->user = User::first();

    actingAs($this->user);
});

describe('Role Test', function () {
    test('can create a role successfully', function () {
        $payload = [
            'name' => 'Admin',
            'permissions' => ['user create', 'user delete'],
        ];

        post('/roles', $payload)->assertRedirect();

        assertDatabaseHas('roles', [
            'name' => 'Admin',
        ]);
    });

    test('fails when name is missing', function () {
        $payload = [
            'permissions' => ['user create'],
        ];

        post('/roles', $payload)->assertSessionHasErrors(['name']);
    });

    test('fails when name is too short', function () {
        $payload = [
            'name' => 'A',
            'permissions' => ['user create'],
        ];

        post('/roles', $payload)->assertSessionHasErrors(['name']);
    });

    test('fails when name is too long', function () {
        $payload = [
            'name' => str_repeat('a', 31),
            'permissions' => ['user create'],
        ];

        post('/roles', $payload)->assertSessionHasErrors(['name']);
    });

    test('fails when name is already taken', function () {
        Role::create(['name' => 'Admin2']);

        $payload = [
            'name' => 'Admin2',
            'permissions' => ['user create'],
        ];

        post('/roles', $payload)->assertSessionHasErrors(['name']);
    });

    test('fails when permissions is missing', function () {
        $payload = [
            'name' => 'Manager',
        ];

        post('/roles', $payload)->assertSessionHasErrors(['permissions']);
    });
});
