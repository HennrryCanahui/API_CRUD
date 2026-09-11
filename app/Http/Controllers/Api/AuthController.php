<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Registrar un nuevo usuario.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $token = $user->createToken('APIToken')->accessToken;

        return response()->json([
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 201);
    }

    /**
     * Validar credenciales e iniciar sesión con Password Grant.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        try {
            $response = Http::post(config('app.url').'/oauth/token', [
                'grant_type'    => 'password',
                'client_id'     => env('PASSPORT_PASSWORD_CLIENT_ID'),
                'client_secret' => env('PASSPORT_PASSWORD_CLIENT_SECRET'),
                'username'      => $request->email,
                'password'      => $request->password,
            ]);

            if ($response->successful()) {
                return response()->json($response->json());
            }
        } catch (\Throwable $e) {
            $tokenRequest = Request::create('/oauth/token', 'POST', [
                'grant_type'    => 'password',
                'client_id'     => env('PASSPORT_PASSWORD_CLIENT_ID'),
                'client_secret' => env('PASSPORT_PASSWORD_CLIENT_SECRET'),
                'username'      => $request->email,
                'password'      => $request->password,
            ]);
            $tokenResponse = app()->handle($tokenRequest);

            if ($tokenResponse->getStatusCode() === 200) {
                return response()->json(json_decode($tokenResponse->getContent(), true));
            }
        }

        return response()->json(['message' => 'Credenciales inválidas'], 401);
    }

    /**
     * Revocar el token actual del usuario (cerrar sesión).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        if ($request->user() && $request->user()->token()) {
            $request->user()->token()->revoke();
        }

        return response()->json([
            'message' => 'Successfully logged out'
        ], 200);
    }
}
