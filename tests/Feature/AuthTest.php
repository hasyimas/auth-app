<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use SebastianBergmann\Environment\Console;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserLoginTest extends TestCase
{
    use WithFaker;

    /**
     * A basic feature test example.
     *
     * @return void
     */


    public function testRegistrasiIsSuccess()
    {

        $userData = [
            'nik' => $this->faker->numerify('################'),
            'password' => 'P@ssw0rd',
            'role' => $this->faker->randomElement(['admin', 'user']),
        ];

        $this->json('POST', 'api/v1/register', $userData, ['Accept' => 'application/json'])
            ->assertStatus(201)
            ->assertJsonStructure([
                "message",
                "user" => [
                    'id',
                    'nik',
                    'role',
                    'created_at',
                    'updated_at',
                ],
            ]);
    }
    public function testLoginIsSuccess()
    {
        $loginData = ['nik' => '1234567890123456', 'password' => 'P@ssw0rd'];
        $this->json('POST', 'api/v1/login', $loginData, ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJsonStructure([
                'access_token',
                'token_type',
                'expires_in',
                "user" => [
                    'id',
                    'nik',
                    'role',
                    'created_at',
                    'updated_at',
                ],
            ]);

        $this->assertAuthenticated();
    }
    protected function getJwtToken()
    {

        return is_null($this->loginData) ? null : JWTAuth::fromUser($this->loginData);
    }
    public function testUserProfileSuccess()
    {
        $loginData = ['nik' => '1234567890123456', 'password' => 'P@ssw0rd'];
        $token = 'Bearer ' .  Auth()->attempt($loginData);
        $this->json('GET', 'api/v1/user-profile', [],  ['Accept' => 'application/json', 'Authorization' => $token])
            ->assertStatus(200)
            ->assertJsonStructure([
                "user" => [
                    "id",
                    "nik",
                    "role",
                    "created_at",
                    "updated_at",
                ],
                "decode" => [
                    "iss",
                    "iat",
                    "exp",
                    "nbf",
                    "jti",
                    "sub",
                    "prv",
                ]
            ]);
    }
}
