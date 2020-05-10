<?php

namespace App\Http\Controllers\Api\Auth;

use App\Entities\User;
use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Psr\Http\Message\ResponseInterface;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response([
                'errors' => $validator->errors()->all()
            ], 422);
        }

        $user = User::where('email', $request->get('email'))->first();
        if ($user && Hash::check(request('password'), $user->password)) {
            return $this->token('password', [
                'username' => $request->input('email'),
                'password' => $request->input('password'),
            ]);
        }

        return response()->json([
            'code' => '404',
            'error' => '',
        ], 404);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|confirmed',
        ]);

        if ($validator->fails()) {
            return response([
                'errors' => $validator->errors()->all()
            ], 422);
        }

        $user = User::create($request->all());

        return response()->json([
            'code' => '200',
            'error' => '',
            'data' => $user
        ], 200);
    }

    private function token($grantType, $data): ResponseInterface
    {
        $data = array_merge($data, [
            'client_id' => (string)env('PASSWORD_CLIENT_ID'),
            'client_secret' => (string)env('PASSWORD_CLIENT_SECRET'),
            'grant_type' => $grantType,
            'scope' => '*',
        ]);

        $client = new Client([
            // Base URI is used with relative requests
            'base_uri' => env('API_HOST'),
            // You can set any number of default request options.
            'timeout' => 5.0,
            'cookies' => true,
            'verify' => false,
            'http_errors' => false,
        ]);

        // POST with basic auth
        $headers = [
            'Content-type' => 'application/json; charset=utf-8',
            'Accept' => 'application/json',
            'form_params' => $data,
        ];

        return $client->post('/oauth/token', $headers);
    }

    public function logout(Request $request)
    {
        $accessToken = $request->user()->token();

        DB::table('oauth_refresh_tokens')
            ->where('access_token_id', $accessToken->id)
            ->update([
                'revoked' => true,
            ]);

        $accessToken->revoke();

        return response()->json([
            'code' => '200',
            'error' => '',
        ], 200);
    }

    public function user()
    {
        $user = Auth::user();

        return response()->json([
            'code' => '200',
            'error' => '',
            'data' => $user,
        ], 200);
    }
}
