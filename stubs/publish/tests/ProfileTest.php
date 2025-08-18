<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Fortify\Features;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\delete;
use function Pest\Laravel\post;
use function Pest\Laravel\put;
use function Pest\Laravel\seed;

uses(RefreshDatabase::class);

beforeEach(function () {
    seed();

    config(['fortify.features' => [
        Features::updateProfileInformation(),
        Features::updatePasswords(),
        Features::twoFactorAuthentication(),
    ]]);

    /** @var \Illuminate\Contracts\Auth\Authenticatable */
    $this->user = User::first();

    actingAs($this->user);
});

describe('Profile Test', function () {

    test('user can update profile information', function () {
        put('/user/profile-information', [
            'name' => 'Updated Name',
            'email' => 'newemail@example.com',
        ])->assertRedirect();

        $this->user->refresh();

        expect($this->user->name)->toBe('Updated Name');
        expect($this->user->email)->toBe('newemail@example.com');
    });

    test('user can update password', function () {
        put('/user/password', [
            'current_password' => 'password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertRedirect();
    });

    test('user cannot update password with wrong current password', function () {
        put('/user/password', [
            'current_password' => 'wrong-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertRedirect();
    });

    test('user can enable two factor authentication', function () {
        if (! Features::enabled(Features::twoFactorAuthentication())) {
            $this->markTestSkipped('Two factor authentication not enabled.');
        }

        post('/user/two-factor-authentication')->assertRedirect();

        $this->user->refresh();
    });

    test('user can disable two factor authentication', function () {
        if (! Features::enabled(Features::twoFactorAuthentication())) {
            test()->markTestSkipped('2FA not enabled.');
        }

        post('/user/two-factor-authentication');

        delete('/user/two-factor-authentication')->assertRedirect();

        $this->user->refresh();
    });

    test('user can regenerate two factor recovery codes', function () {
        if (! Features::enabled(Features::twoFactorAuthentication())) {
            test()->markTestSkipped('2FA not enabled.');
        }

        post('/user/two-factor-authentication');
        post('/user/two-factor-recovery-codes')->assertRedirect();
    });
});
