<?php

namespace App\Http\Middleware\Api;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserType
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = Auth::user();

        if ($user && $user->is_active && in_array((string) $user->user_type_id, $roles)) {
            return $next($request);
        }

        return response()->json([
            'message' => 'Unauthorized.',
        ], 403);
    }
}
