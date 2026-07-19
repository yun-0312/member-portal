<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $permission): Response
    {
        $user = $request->user();

        //role_permissions経由で権限チェック
        $hasPermission = $user->role
            ->permissions()
            ->where('name', $permission)
            ->exists();

        if (! $hasPermission) {
            return response()->json([
                'message' => 'Forbidden',
                'permission' => $permission,
            ], 403);
        }
        return $next($request);
    }
}
