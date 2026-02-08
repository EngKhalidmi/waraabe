<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
class RedirectIfNotAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            // User is not authenticated, redirect to login
            return redirect()->route('login');
        }
    
        // User is authenticated
    
        // Check if the user is trying to access the login route
        if ($request->routeIs('login')) {
            // Redirect to dashboard if already authenticated and trying to access login
            return redirect()->route('dashboard');
        }
        return $next($request);
    }
}
