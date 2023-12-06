<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class HorizonAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $allowedIPs = [
            '10.10.10.7',
            '10.10.10.13',
            '178.233.176.238', // ofis ip
//            "31.223.75.12",//fron
//            "195.175.202.174"//front
        ];

        $userIP = $request->ip();


        if (in_array($userIP, $allowedIPs)) {
            return $next($request);
        }

        Log::channel('horizon_log')->warning('Horizon içi engele takıldı ip adresi : ' . $userIP);

        abort(Response::HTTP_FORBIDDEN);
    }
}
