<?php

namespace App\Livewire\Auth;

use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Attributes\Title;
use Livewire\Component;
use Modules\AdminUser\Models\User;

#[Title('Log in')]
class LoginPage extends Component
{
    public string $identifier = '';

    public string $password = '';

    public bool $remember = false;

    public string $captchaToken = '';

    public function mount(): void
    {
        request()->session()->forget('url.intended');
    }

    public function login()
    {
        $validated = $this->validate([
            'identifier' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if ($this->tooManyAttempts()) {
            $seconds = RateLimiter::availableIn($this->throttleKey());

                $this->addError('identifier', trans('auth.throttle', [
                    'seconds' => $seconds,
                    'minutes' => (int) ceil($seconds / 60),
                ]));

            return null;
        }

        $login = Str::lower(trim($validated['identifier']));

        $user = User::query()
            ->whereRaw('LOWER(email) = ?', [$login])
            ->orWhereRaw('LOWER(username) = ?', [$login])
            ->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            RateLimiter::hit($this->throttleKey(), 60);
            $this->addError('identifier', trans('auth.failed'));

            return null;
        }

        if (function_exists('captcha_enabled') && captcha_enabled()) {
            if (! captcha_verify_token(
                token: $this->captchaToken,
                host: request()->getHost(),
                ip: request()->ip(),
            )) {
                $this->addError('captchaToken', captcha_error_message());
                $this->captchaToken = '';
                $this->dispatch('captcha-reset');

                return null;
            }
        }

        $guard = app(StatefulGuard::class);
        $provider = $guard->getProvider();

        if (config('hashing.rehash_on_login', true) && method_exists($provider, 'rehashPasswordIfRequired')) {
            $provider->rehashPasswordIfRequired($user, ['password' => $validated['password']]);
        }

        if ($user->isSuperAdmin() && ! $user->email_verified_at) {
            $user->forceFill([
                'email_verified_at' => now(),
            ])->save();
        }

        RateLimiter::clear($this->throttleKey());

        if ($this->requiresTwoFactorChallenge($user)) {
            request()->session()->put([
                'login.id' => $user->getKey(),
                'login.remember' => $this->remember,
                'url.intended' => \Laravel\Fortify\Fortify::redirects('login') ?? config('fortify.home'),
            ]);

            \Laravel\Fortify\Events\TwoFactorAuthenticationChallenged::dispatch($user);

            return redirect()->route('two-factor.login');
        }

        $guard->login($user, $this->remember);
        request()->session()->regenerate();

        return redirect()->to(\Laravel\Fortify\Fortify::redirects('login') ?? config('fortify.home'));
    }

    protected function requiresTwoFactorChallenge(User $user): bool
    {
        if (! $user->two_factor_secret) {
            return false;
        }

        if (! in_array(\Laravel\Fortify\TwoFactorAuthenticatable::class, class_uses_recursive($user))) {
            return false;
        }

        if (! \Laravel\Fortify\Fortify::confirmsTwoFactorAuthentication()) {
            return true;
        }

        return ! is_null($user->two_factor_confirmed_at);
    }

    protected function tooManyAttempts(): bool
    {
        return RateLimiter::tooManyAttempts($this->throttleKey(), 5);
    }

    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->identifier).'|'.request()->ip());
    }

    public function render(): View
    {
        return view(theme_view('livewire.auth.login-page', 'guest'));
    }
}
