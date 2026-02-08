<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Models\AnalyticsLogs;

class AnalyticsLogger
{
    public function handle($request, Closure $next) {
        $response = $next($request);

        if (Auth::check() && $request->method() !== 'GET') {
            // Log the analytics
            AnalyticsLogs::create([
                'user_id' => Auth::id(),
                'module_name' => $request->route()->getName(),
                'action' => $request->method(),
                'details' => json_encode($request->except('_token')),
                'timestamp' => now(),
            ]);
        
            // Check for excessive DELETE actions
            if ($request->method() == 'DELETE') {
                $recentDeletes = AnalyticsLogs::where('user_id', Auth::id())
                    ->where('action', 'DELETE')
                    ->where('action', 'PUT')
                    ->where('created_at', '>=', now()->subMinutes(2))
                    ->count();
        
                if ($recentDeletes > 15) {
                    // Update user's status to 0 (inactive)
                    User::where('id', Auth::id())->update(['status' => 0]);
        
                    // Log out the user
                    Auth::logout();
        
                    // Redirect or return response
                    return redirect('/login')->withErrors('You have been logged out due to excessive deletions.');
                }
            }
        }
        

        return $response;
    }
}
