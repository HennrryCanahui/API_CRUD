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
            'scopes'   => 'sometimes|array',
        ]);

        // Scopes solicitados por el cliente, o el scope por defecto
        $scopes = $request->input('scopes', ['productos.read']);

        $clientId = env('PASSPORT_PASSWORD_CLIENT_ID');
        $clientSecret = env('PASSPORT_PASSWORD_CLIENT_SECRET');

        if (!$clientId) {
            $passwordClient = \Laravel\Passport\Client::where('password_client', 1)->first();
            if ($passwordClient) {
                $clientId = $passwordClient->id;
                $clientSecret = $passwordClient->secret;
            }
        }

        try {
            $response = Http::post(config('app.url').'/oauth/token', [
                'grant_type'    => 'password',
                'client_id'     => $clientId,
                'client_secret' => $clientSecret,
                'username'      => $request->email,
                'password'      => $request->password,
                'scope'         => implode(' ', (array) $scopes),
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $user = User::where('email', $request->email)->first();
                if ($user) {
                    $data['user'] = $user;
                }
                return response()->json($data, 200);
            }
        } catch (\Throwable $e) {
            $tokenRequest = Request::create('/oauth/token', 'POST', [
                'grant_type'    => 'password',
                'client_id'     => $clientId,
                'client_secret' => $clientSecret,
                'username'      => $request->email,
                'password'      => $request->password,
                'scope'         => implode(' ', (array) $scopes),
            ]);
            $tokenResponse = app()->handle($tokenRequest);

            if ($tokenResponse->getStatusCode() === 200) {
                $data = json_decode($tokenResponse->getContent(), true);
                $user = User::where('email', $request->email)->first();
                if ($user) {
                    $data['user'] = $user;
                }
                return response()->json($data, 200);
            }
        }

        return response()->json(['message' => 'Credenciales inválidas'], 401);
    }

    /**
     * Revocar el token actual del usuario y su refresh token (cerrar sesión).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        if ($request->user() && $request->user()->token()) {
            // Revocar el access token actual
            $token = $request->user()->token();
            $token->revoke();

            // Revocar también el refresh token
            if (isset($token->id)) {
                if (class_exists('Laravel\Passport\RefreshTokenRepository')) {
                    $refreshTokenRepository = app(\Laravel\Passport\RefreshTokenRepository::class);
                    $refreshTokenRepository->revokeRefreshTokensByAccessTokenId($token->id);
                } else {
                    \Laravel\Passport\RefreshToken::where('access_token_id', $token->id)->update(['revoked' => true]);
                }
            }
        }

        return response()->json([
            'message' => 'Sesión cerrada correctamente'
        ], 200);
    }

    /**
     * Obtener los datos del usuario autenticado.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function me(Request $request)
    {
        return response()->json([
            'user' => $request->user(),
        ]);
    }
}
