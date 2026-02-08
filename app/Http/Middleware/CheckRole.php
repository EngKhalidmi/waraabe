<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string[]  ...$roles
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles): Response {

        // If the user is not authenticated, redirect to the login page
        if (!Auth::check()) {
            return redirect('login');
        }

        $user = Auth::user();

        // Check if user role exists and matches allowed roles
        $roleHierarchy = ['acc' => 1, 'sales' => 2, 'admin' => 3]; // Adjust as needed

        foreach ($roles as $role) {
            if (!in_array($user->role, $roles))  {
                // Log unauthorized access attempt
                Log::warning('Unauthorized access attempt', [
                    'user_id' => $user->id,
                    'role' => $user->role,
                    'attempted_role' => $role,
                    'url' => URL::current(),
                    'route' => $request->path(),
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'route' => $request->path()
                ]);
                abort(403, 'Unauthorized action.');
            }
        }

        // Check if the user's account is activated
        if ($user->status == 0) {
            Auth::logout();
            return redirect('login')->with('error', 'Your account is not activated yet!');
        }

        $system = env('SYSTEM_MAINTENANCE');

        if ($system === true) {
            Auth::logout();
            return redirect('login')->with('error', 'System is under maintenance. Please wait a moment!');
        }
        


        // Verify signed URL for extra-sensitive routes
        if ($request->route()->named('report.show') && !$request->hasValidSignature()) {
            abort(403, 'Invalid or expired URL.');
        }

        // // Implement a Content Security Policy (CSP) for enhanced XSS protection
        $response = $next($request);
        // $response->headers->set('Content-Security-Policy', "default-src 'self'; script-src 'self'; object-src 'none';");

        return $response;
    }
}
