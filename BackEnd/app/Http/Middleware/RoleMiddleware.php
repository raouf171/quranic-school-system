<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    // this is ur security gard , it verifies each time u wanna request an action if ur authorized 
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // verify first i account is authentified
        if (!$request->user()) {
            return response()->json([
                'message' => 'يجب تسجيل الدخول أولاً',
            ], 401);
        }

        // Vérifier que le role est dans la liste autorise
        
        if (!in_array($request->user()->role, $roles)) {
            return response()->json([
                'message' => 'ليس لديك صلاحية للوصول إلى هذا المورد',
                'your_role'     => $request->user()->role,
                'required_roles' => $roles,
            ], 403);
        }

        // Étape 3 — Tout est OK → continuer vers le Controller
        return $next($request);
    }
}