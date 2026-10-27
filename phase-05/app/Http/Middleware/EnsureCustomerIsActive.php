<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCustomerIsActive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'Account is deactivated. Please contact support.',
                'errors' => [
                    'account' => ['Your account has been deactivated. Please contact customer support.'],
                ],
            ], 403);
        }

        return $next($request);
    }
}
