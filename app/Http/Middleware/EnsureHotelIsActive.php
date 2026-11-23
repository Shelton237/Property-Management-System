<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Requests\Auth\LoginRequest;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Signing in is not the only moment access can be revoked: an account may be
 * deactivated, or its hotel suspended, while a session is still open. This
 * middleware ends such sessions on the next request.
 */
class EnsureHotelIsActive
{
    public function __invoke(Request $request, Closure $next): Response
    {
        return $this->handle($request, $next);
    }

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            return $next($request);
        }

        if (! $user->canSignIn()) {
            return $this->reject($request, 'auth.inactive');
        }

        if ($user->hotelIsSuspended()) {
            return $this->reject($request, 'auth.hotel_suspended');
        }

        return $next($request);
    }

    private function reject(Request $request, string $messageKey): Response
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->withErrors([LoginRequest::REFUSAL_KEY => trans($messageKey)]);
    }
}
