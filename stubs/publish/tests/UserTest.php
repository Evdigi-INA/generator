<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

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

describe('User Test', function () {
    test('can create a user successfully', function () {
        post('/users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'role' => 1,
            'avatar' => UploadedFile::fake()->image('avatar.jpg'),
        ])->assertRedirect();

        assertDatabaseHas('users', [
            'email' => 'john@example.com',
        ]);
    });

    test('fails when name is missing', function () {
        post('/users', [
            'email' => 'john@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ])->assertSessionHasErrors(['name']);
    });

    test('fails when email is invalid', function () {
        post('/users', [
            'name' => 'John Doe',
            'email' => 'not-an-email',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ])->assertSessionHasErrors(['email']);
    });

    test('fails when email is already taken', function () {
        User::factory()->create(['email' => 'john@example.com']);

        post('/users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ])->assertSessionHasErrors(['email']);
    });

    test('fails when password confirmation does not match', function () {
        post('/users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'different',
        ])->assertSessionHasErrors(['password']);
    });

    test('fails when avatar is not an image', function () {
        post('/users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'avatar' => UploadedFile::fake()->create('document.pdf', 100),
        ])->assertSessionHasErrors(['avatar']);
    });
});
