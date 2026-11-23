<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Refusals that concern the whole attempt rather than one field.
     *
     * They are reported under their own key so the login page can show them in
     * a single alert above the form, and keep per field messages under the
     * field they belong to.
     */
    public const REFUSAL_KEY = 'auth';

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['boolean'],
        ];
    }

    /**
     * Authenticate the request, refusing deactivated accounts and suspended
     * hotels with their own message.
     *
     * Credentials are checked before those two cases, so a wrong password never
     * reveals whether an account exists or what state it is in.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $user = User::where('email', (string) $this->string('email'))->first();

        if (! $user || ! Hash::check((string) $this->string('password'), $user->password)) {
            // Only a failed credential check counts towards the rate limit: the
            // other refusals are not brute force attempts.
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                self::REFUSAL_KEY => trans('auth.failed'),
            ]);
        }

        if (! $user->canSignIn()) {
            throw ValidationException::withMessages([
                self::REFUSAL_KEY => trans('auth.inactive'),
            ]);
        }

        if ($user->hotelIsSuspended()) {
            throw ValidationException::withMessages([
                self::REFUSAL_KEY => trans('auth.hotel_suspended'),
            ]);
        }

        Auth::login($user, $this->boolean('remember'));

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Five attempts per minute, per email address and IP address pair.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            self::REFUSAL_KEY => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower((string) $this->string('email')).'|'.$this->ip());
    }
}
