<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Авторизация
     * @param \App\Http\Requests\LoginRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $request->validated();
       try {
           $user = User::where('email', $request->email)->first();
           if(!$user || Hash::check($request->password, $user->password)){
               return response()->json([
                   'message' => 'данные не верны'
               ], 401);
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
               'message' => 'успешно',
               'token' => $token,
           ], 200);
       } catch (\Throwable $th) {
        Log::error($th);
        return response()->json([
            'message' => 'ошибка',
        ], 400);
       }
    }

    /**
     * Регистрация
     * @param \App\Http\Requests\RegisterRequest $request
     * @return JsonResponse
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $request->validated();
        try {

            User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'gender' => $request->gender,
            ]);
            return response()->json([
                'message' => 'успешно',
            ], 200);

        } catch (\Throwable $th) {
            Log::error($th);
            return response()->json([
                'message' => 'ошибка',
            ], 400);
        }
    }

    /**
     * Профиль
     * @return JsonResponse
     */
    public function profile(): JsonResponse
    {
        return response()->json([
            'message' => 'успешно',
            'user' => auth()->user(),
        ], 200);
    }
    /**
     * Выход
     * @return JsonResponse
     */
    public function logout(): JsonResponse
    {
        auth()->logout();
        return response()->json([
            'message' => 'успешно',
        ], 200);
    }

    /**
     * Обновление токена
     * @return JsonResponse
     */
    public function refresh(): JsonResponse
    {
        return response()->json([
            'message' => 'успешно',
            'token' => auth()->refresh(),
        ], 200);
    }
}
