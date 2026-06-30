<?php

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\post;
use function Pest\Laravel\seed;

uses(classAndTraits: RefreshDatabase::class);

beforeEach(closure: function (): void {
    seed(class: DatabaseSeeder::class);
    $this->user = User::first();
});

describe(description: 'Auth Test', tests: function (): void {

    test(description: 'user can login', closure: function (): void {
        post(uri: '/login', data: [
            'email' => $this->user->email,
            'password' => 'password',
        ])->assertRedirect();
    });

    test(description: 'can register a new user', closure: function (): void {
        post(uri: '/register', data: [
            'name' => 'Test User',
            'email' => 'test@example',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect();
    });

    test(description: 'user can logout', closure: function (): void {
        actingAs(user: $this->user)
            ->post(uri: '/logout')
            ->assertRedirect();
    });

    test(description: 'login fails with wrong password', closure: function (): void {
        post(uri: '/login', data: [
            'email' => $this->user->email,
            'password' => 'wrong-password',
        ])->assertSessionHasErrors(keys: 'email');
    });

    test(description: 'login fails with unregistered email', closure: function (): void {
        post(uri: '/login', data: [
            'email' => 'notfound@example.com',
            'password' => 'password',
        ])->assertSessionHasErrors(keys: 'email');
    });

    test(description: 'register fails when password do not match', closure: function (): void {
        post(uri: '/register', data: [
            'name' => 'Test User',
            'email' => 'another@example.com',
            'password' => 'password',
            'password_confirmation' => 'different-password',
        ])->assertSessionHasErrors(keys: 'password');
    });

    test(description: 'register fails when email already taken', closure: function (): void {
        post(uri: '/register', data: [
            'name' => 'Test User',
            'email' => $this->user->email,
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertSessionHasErrors(keys: 'email');
    });

    test(description: 'logout fails when not authenticated', closure: function (): void {
        post(uri: '/logout')->assertRedirect(uri: '/login');
    });
});
