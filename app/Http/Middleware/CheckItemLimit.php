<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckItemLimit
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $path = $request->path();
        
        $type = '';
        if (str_contains($path, 'jastip/listings')) $type = 'jastip_listing';
        elseif (str_contains($path, 'jastip/requests')) $type = 'jastip_request';
        elseif (str_contains($path, 'preloved/listings')) $type = 'preloved_listing';
        elseif (str_contains($path, 'preloved/requests')) $type = 'preloved_request';

        if ($type && !$request->user()->canAddItem($type)) {
            $maxLimit = $request->user()->getMaxItemLimit();
            $tierName = strtoupper($request->user()->tier->value);
            
            return response()->json([
                'success' => false,
                'message' => "You have reached the maximum active items limit ({$maxLimit}) for your {$tierName} tier in this category."
            ], 403);
        }

        return $next($request);
    }
}