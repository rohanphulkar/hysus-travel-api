<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class TokenController extends Controller
{
    //
    public function checkToken(Request $request)
    {
        $token = $request->header('Authorization'); // Get the token from the request header

        try {
            $isValid = JWTAuth::parseToken()->check(); // Check if the token is valid

            if ($isValid) {
                return response()->json(['message' => 'Token is valid'], 200);
            } else {
                return response()->json(['message' => 'Token is not valid'], 401);
            }
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json(['message' => 'Token has expired'], 401);
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(['message' => 'Token is invalid'], 401);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json(['message' => 'Token is absent'], 401);
        }
    }
}
