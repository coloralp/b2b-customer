<?php

namespace App\Http\Middleware;

use App\Enums\RoleEnum;
use App\Models\User;
use App\Traits\ApiTrait;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response as Response1;
use Symfony\Component\HttpFoundation\Response;

class ForbiddenCustomerMiddleware
{
    use ApiTrait;

    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() and auth()->user()->hasRole(RoleEnum::CUSTOMER->value)) {
            return $this->returnWithMessage('you are a customer.This page forbidden for you', 1, Response1::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
