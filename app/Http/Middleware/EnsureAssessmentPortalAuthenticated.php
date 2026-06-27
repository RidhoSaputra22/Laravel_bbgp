<?php

namespace App\Http\Middleware;

use App\Services\Assesment\AssessmentPortalAuthService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAssessmentPortalAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (app(AssessmentPortalAuthService::class)->isAuthenticated()) {
            return $next($request);
        }

        return redirect()
            ->route('assesment.auth.login')
            ->with('assessment_portal_notice', 'Silakan login terlebih dahulu untuk mengakses portal assessment.');
    }
}
