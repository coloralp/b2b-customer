<?php

namespace App\Http\Middleware;

use App\Traits\ApiTrait;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckIsApiMiddleware
{
    use ApiTrait;

    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        if (!$request->is('api/*') && !$request->is('docs/*') ) {
            return $this->returnWithMessage('This used for api services!Make sure to send a api request for this service');
        }

        return $next($request);
    }
}
