<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    /**
     * @OA\Post(
     * path="/api/v1/login",
     * summary="Sign in",
     * description="Login by nik, password",
     * operationId="authLogin",
     * tags={"Auth"},
     * @OA\RequestBody(
     *    required=true,
     *    description="Pass user credentials",
     *    @OA\JsonContent(
     *       required={"nik","password"},
     *       @OA\Property(property="nik", type="integer", format="int64", example="1234567890123456"),
     *       @OA\Property(property="password", type="string", format="password", example="P@ssw0rd")
     *    ),
     * ),
     * @OA\Response(
     *     response=200,
     *     description="Success",
     *     @OA\JsonContent(
     *        @OA\Property(property="access_token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9sb2NhbGhvc3RcL2Rldi1qZHNcL2F1dGgtYXBwXC9hcGlcL3YxXC9sb2dpbiIsImlhdCI6MTY0NDMxNjMyMiwiZXhwIjoxNjQ0MzE5OTIyLCJuYmYiOjE2NDQzMTYzMjIsImp0aSI6ImM3ejlSbGI1cGI0ZWl4UUciLCJzdWIiOjEsInBydiI6IjIzYmQ1Yzg5NDlmNjAwYWRiMzllNzAxYzQwMDg3MmRiN2E1OTc2ZjcifQ.97Jgzp8u_EuUSTRRZFL4TKQBBUgwCbbjgDh221IZOLY"),
     *        @OA\Property(property="token_type", type="string", example="bearer"),
     *        @OA\Property(property="expires_in", type="string", example="3600"),
     *        @OA\Property(
     *           property="user",
     *           type="object",
     *           @OA\Property(property="id", type="string", example="1"),
     *           @OA\Property(property="nik", type="string", example="1234567890123456"),
     *           @OA\Property(property="role", type="string", example="admin"),
     *           @OA\Property(property="created_at", type="string", example="2022-02-07T04:08:53.000000Z"),
     *           @OA\Property(property="updated_at", type="string", example="2022-02-07T04:08:53.000000Z"),
     *        )
     *     )
     * ),
     * @OA\Response(
     *    response=401,
     *    description="Wrong credentials response",
     *    @OA\JsonContent(
     *       @OA\Property(property="error", type="string", example="Unauthorized")
     *        )
     *     )
     * )
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nik' => 'required',
            'password' => 'required|string|min:6',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        if (!$token = auth()->attempt($validator->validated())) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        return $this->createNewToken($token);
    }
    /**
     * @OA\Post(
     * path="/api/v1/register",
     * summary="Register",
     * description="Registration user",
     * operationId="Registration",
     * tags={"Auth"},
     * @OA\RequestBody(
     *    required=true,
     *    description="Registration user credentials",
     *    @OA\JsonContent(
     *       required={"nik","role","password"},
     *       @OA\Property(property="nik", type="integer", format="int64", example="1234567890123456"),
     *       @OA\Property(property="role", type="string", format="", example="user"),
     *       @OA\Property(property="password", type="string", format="password", example="P@ssw0rd")
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Success"
     *     ),
     * @OA\Response(
     *    response=401,
     *    description="Returns when user is not authenticated",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Not authorized"),
     *    )
     * )
     * )
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nik' => 'required|numeric|digits:16',
            'password' => 'required|string|min:6',
            'role' => 'required|',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }
        $user = User::create(array_merge(
            $validator->validated(),
            ['password' => bcrypt($request->password)]
        ));
        return response()->json([
            'message' => 'User successfully registered',
            'user' => $user
        ], 201);
    }
    /**
     * @OA\Post(
     * path="/api/v1/logout",
     * summary="Logout",
     * description="Logout user and invalidate token",
     * operationId="authLogout",
     * tags={"Auth"},
     * security={ {"bearer": {} }},
     * @OA\Response(
     *    response=200,
     *    description="Success"
     *     ),
     * @OA\Response(
     *    response=401,
     *    description="Returns when user is not authenticated",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Not authorized"),
     *    )
     * )
     * )
     */
    public function logout()
    {
        auth()->logout();
        return response()->json(['message' => 'User successfully signed out']);
    }
    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->createNewToken(auth()->refresh());
    }
    /**
     * @OA\Get(
     * path="/api/v1/user-profile",
     * summary="User Profile",
     * description="user Profile",
     * operationId="user-profile",
     * tags={"User Profile"},
     * security={ {"bearer": {} }},
     * @OA\Response(
     *    response=200,
     *    description="Success"
     *     ),
     * @OA\Response(
     *    response=401,
     *    description="Returns when user is not authenticated",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Not authorized"),
     *    )
     * )
     * )
     */
    public function userProfile()
    {
        try {
            $user = auth()->user();
            // attempt to verify the credentials and create a token for the user
            $token = JWTAuth::getToken();

            $apy = JWTAuth::getPayload($token)->toArray();
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json(['token_expired'], 500);
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(['token_invalid'], 500);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {

            return response()->json(['token_absent' => $e->getMessage()], 500);
        }
        return response()->json([
            'user' => $user,
            'decode' => $apy
        ]);
    }
    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function createNewToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth()->factory()->getTTL() * 60,
            'user' => auth()->user()
        ]);
    }

    public function token()
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'not_logged_in'], 401);
        }
        return response()->json(['loggin' => 'true']);
    }
}
