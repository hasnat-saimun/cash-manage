<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SetBusiness
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            // if session has business_id use it, otherwise set first business
            if (!$request->session()->has('business_id')) {
                $first = $user->businesses()->first();
                if ($first) {
                    $request->session()->put('business_id', $first->id);
                } else {
                    // No business set; enforce creation before accessing other pages
                    // Allow access to business routes themselves
                    $path = $request->path();
                    $isBusinessRoute = str_starts_with($path, 'businesses');
                    if (!$isBusinessRoute) {
                        return redirect()->route('business.index')->with('error', 'Please create a business to continue.');
                    }
                }
            }

            // If flagged as needing a new business and none exists, enforce creation
            if ((bool)($user->need_new_business ?? false)) {
                $hasAny = $user->businesses()->exists();
                if (!$hasAny) {
                    $path = $request->path();
                    $isBusinessRoute = str_starts_with($path, 'businesses');
                    if (!$isBusinessRoute) {
                        return redirect()->route('business.index')->with('error', 'Please create a business to continue.');
                    }
                }
            }
        }
        return $next($request);
    }
}
