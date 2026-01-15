<?php

namespace App\Http\Middleware;

use App\Services\AuthService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiAuthMiddleware
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$this->authService->isAuthenticated()) {
            return redirect()->route('login')->with('error', 'Please login to continue.');
        }

        // Make user available globally via view share
        view()->share('user', $this->authService->getUser());

        return $next($request);
    }
}
