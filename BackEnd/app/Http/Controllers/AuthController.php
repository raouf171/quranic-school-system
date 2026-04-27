<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    //log in function 
    public function login(Request $request): JsonResponse
    {
        
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

//chercher
        $account = Account::where('email', $request->email)->first()    ;

//Verifier si le compte existe 
        if (!$account || !Hash::check($request->password, $account->password)) {
            return response()->json([
                'message' => 'البريد الإلكتروني أو كلمة المرور غير صحيحة',
            ], 401); 
        }

//  compte est actif
        if (!$account->is_active) {
            return response()->json([
                'message' => 'هذا الحساب غير نشط. تواصل مع المسؤول لكي تفعله ',
            ], 403);
        }


        $account->tokens()->delete();

        $token = $account->createToken('auth_token')->plainTextToken;

        $profile = $account->getProfile();

        // L'app mobile stocke token + role pour navigation
        return $this->apiSuccess([
            'token'   => $token,
            'role'    => $account->role,
            'profile' => $profile,
        ]);
    }

 



    ///LOG OUT FUNCNTON
    public function logout(Request $request): JsonResponse
    {
      
        if ($token = $request->user()->currentAccessToken()) {
            $token->delete();
        }
        return $this->apiSuccess(
            null,
            'تم تسجيل الخروج بنجاح'
        );
    }

    // GET /api/me 
        public function me(Request $request): JsonResponse
    {
        
        $account = $request->user();

        $profile = $account->getProfile();

        return $this->apiSuccess([
            'role'    => $account->role,
            'email'   => $account->email,
            'profile' => $profile,
        ]);
    }
}