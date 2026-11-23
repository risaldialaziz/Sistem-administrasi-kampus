<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuditActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        $userId = $request->user()?->id;
        $response = $next($request);

        if (
            $userId
            && in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)
            && $response->getStatusCode() < 400
        ) {
            AuditLog::create([
                'user_id' => $userId,
                'action' => substr($request->route()?->getName() ?? $request->method().' '.$request->path(), 0, 255),
                'ip_address' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 1000),
            ]);
        }

        return $response;
    }
}
