<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Status;
use Illuminate\Validation\ValidationException;

class CheckUserType
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string|int ...$roles  <-- This captures the parameters
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = Auth::user();

        // Check if the user's type ID is in the allowed roles array
        if ($user && $user->status_id === Status::ACTIVE && in_array($user->user_type_id, $roles)) {
            return $next($request);
        }

        // Log the user out so they can actually stay on the login page
        Auth::logout();

        // Invalidate session to prevent fixation and clear tokens
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        throw ValidationException::withMessages([
            'email' => ['Only administrators are allowed to access this area.'],
        ]);
    }
}