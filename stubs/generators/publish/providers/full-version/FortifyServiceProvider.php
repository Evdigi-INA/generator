<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(callback: CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(callback: UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(callback: UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(callback: ResetUserPassword::class);

        RateLimiter::for(name: 'login', callback: function (Request $request): Limit {
            $throttleKey = Str::transliterate(string: Str::lower(value: $request->input(key: Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(maxAttempts: 5)->by(key: $throttleKey);
        });

        RateLimiter::for(name: 'two-factor', callback: fn (Request $request) => Limit::perMinute(maxAttempts: 5)->by(key: $request->session()->get(key: 'login.id')));

        Fortify::registerView(view: fn () => view(view: 'auth.register'));

        Fortify::loginView(view: fn () => view(view: 'auth.login'));

        Fortify::confirmPasswordView(view: fn () => view(view: 'auth.confirm-password'));

        Fortify::twoFactorChallengeView(view: fn () => view(view: 'auth.two-factor-challenge'));

        Fortify::requestPasswordResetLinkView(view: fn () => view(view: 'auth.forgot-password'));

        Fortify::resetPasswordView(view: fn (Request $request) => view(view: 'auth.reset-password', data: ['request' => $request]));
    }
}
