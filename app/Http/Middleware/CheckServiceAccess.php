<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Service;
use Symfony\Component\HttpFoundation\Response;

class CheckServiceAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  string  $serviceSlug
     */
    public function handle(Request $request, Closure $next, string $serviceSlug): Response
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        // 1. Find the service by the slug passed from the route
        // Note: You could cache this query for performance since services rarely change
        $service = Service::where('slug', $serviceSlug)->where('is_active', true)->firstOrFail();

        // 2. Check if the user has access using your Model logic
        if (!$user || !$user->managesService($service->id)) {
            abort(403, 'You do not have permission to access the ' . $service->name . ' module.');
        }

        return $next($request);
    }
}