<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PermissionOr
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$permissions): Response
    {
        $user = $request->user();

        foreach ($permissions as $permission) {
            if ($user->role->permissions()->where('name', $permission)->exists()) {
                return $next($request);
            }
        }

        return response()->json([
            'message' =>'Forbidden',
            'required_permissions' => $permissions,
        ], 403);
    }
}
