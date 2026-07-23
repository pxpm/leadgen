<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\FacebookProvider;
use Laravel\Socialite\Two\GoogleProvider;

class SocialAuthController extends Controller
{
    public function redirect(string $provider): RedirectResponse
    {
        abort_if(! in_array($provider, ['google', 'facebook']), 404);

        $intent = request()->query('intent', 'trial');

        Session::put('social_auth_intent', $intent);

        return $this->driver($provider)->redirect();
    }

    public function callback(string $provider): RedirectResponse
    {
        abort_if(! in_array($provider, ['google', 'facebook']), 404);

        try {
            $socialUser = $this->driver($provider)->user();
        } catch (\Exception) {
            return redirect('/manage-backoffice/login')->with('error', 'Falha na autenticação.');
        }

        $intent = Session::pull('social_auth_intent', 'trial');

        if ($intent === 'login') {
            return $this->handleLoginIntent($socialUser->getEmail(), $provider);
        }

        // Trial intent — existing flow
        Session::put('social_auth', [
            'provider' => $provider,
            'name' => $socialUser->getName(),
            'email' => $socialUser->getEmail(),
        ]);

        return redirect()->route('trial.complete');
    }

    /**
     * Handle social login for existing users.
     */
    private function handleLoginIntent(string $email, string $provider): RedirectResponse
    {
        $user = User::where('email', $email)->first();

        if (! $user) {
            return redirect('/manage-backoffice/login')
                ->with('error', __('auth.login.social_no_account'));
        }

        Auth::login($user);

        session()->regenerate();

        return redirect()->intended('/manage-backoffice');
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
