<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Priority: 1. User preference, 2. Session, 3. Browser, 4. Default
        $locale = null;

        // Check logged-in user preference
        if ($request->user() && $request->user()->locale) {
            $locale = $request->user()->locale;
        }
        
        // Check session
        if (!$locale && session()->has('locale')) {
            $locale = session('locale');
        }
        
        // Check browser language
        if (!$locale) {
            $browserLang = $request->getPreferredLanguage(['sw', 'en']);
            if ($browserLang) {
                $locale = $browserLang;
            }
        }

        // Default to Swahili
        $locale = $locale ?? 'sw';

        // Validate locale
        if (!in_array($locale, ['sw', 'en'])) {
            $locale = 'sw';
        }

        // Set application locale
        App::setLocale($locale);
        
        // Store in session for future requests
        session(['locale' => $locale]);

        return $next($request);
    }
}
