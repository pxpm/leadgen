<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Session;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\FacebookProvider;
use Laravel\Socialite\Two\GoogleProvider;

class SocialAuthController extends Controller
{
    public function redirect(string $provider): RedirectResponse
    {
        abort_if(! in_array($provider, ['google', 'facebook']), 404);

        Session::put('social_auth_intent', 'trial');

        return $this->driver($provider)->redirect();
    }

    public function callback(string $provider): RedirectResponse
    {
        abort_if(! in_array($provider, ['google', 'facebook']), 404);

        try {
            $socialUser = $this->driver($provider)->user();
        } catch (\Exception) {
            return redirect('/')->with('error', 'Falha na autenticação.');
        }

        Session::put('social_auth', [
            'provider' => $provider,
            'name' => $socialUser->getName(),
            'email' => $socialUser->getEmail(),
        ]);

        return redirect()->route('trial.complete');
    }

    /**
     * Social login uses google_auth config (separate from email-sending google config).
     */
    private function driver(string $provider): GoogleProvider|FacebookProvider
    {
        if ($provider === 'google') {
            return Socialite::buildProvider(
                GoogleProvider::class,
                config('services.google_auth')
            );
        }

        return Socialite::driver($provider);
    }
}
