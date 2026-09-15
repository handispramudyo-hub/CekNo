<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuditLogger
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->method() !== 'GET' && auth()->check()) {
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => $request->method().' '.$request->path(),
                'ip_address' => $request->ip(),
                'user_agent' => substr($request->userAgent() ?? '', 0, 254),
                'metadata' => [
                    'status' => $response->getStatusCode(),
                    'input' => collect($request->all())->keys()->all(),
                ],
            ]);
        }

        return $response;
    }
}