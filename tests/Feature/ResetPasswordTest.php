<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Auth\Passwords\PasswordBroker;
use Tests\TestCase;

class ResetPasswordTest extends TestCase
{
    /** @var \App\User */
    protected $user;

    /** @var \App\User */
    protected $studentUser;

    /** @var \App\User */
    protected $moduleTutorUser;

    public function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');

        $this->studentUser = User::whereHas('role', function($q){
            $q->where('name', 'Student');
        })->first();

        $this->moduleTutorUser = User::whereHas('role', function($q){
            $q->where('name', 'Module Tutor');
        })->first();
    }

    /**
     *
     * A test to check the status of response when a user has a valid token.
     *
     * @test
     */
    public function test_sendResetResponse_valid()
    {
        $password_broker = app(PasswordBroker::class);
        $token = $password_broker->createToken($this->studentUser); //create reset password token

        $this->postJson('/api/password/reset', [
            'token' => $token,
            'email' =>  $this->studentUser->email,
            'password' => 'Test123456789',
            'password_confirmation' => 'Test123456789'
        ])
            ->assertSuccessful()
            ->assertJsonFragment(['status' => 'Your password has been reset!']);
    }

    /**
     *
     * A test to check the status of response when a user has an invalid token.
     *
     * @test
     */
    public function test_sendResetResponse_invalid_token()
    {
        $this->postJson('/api/password/reset', [
            'token' => rand(9999,10000),
            'email' =>  $this->studentUser->email,
            'password' => 'Test123456789',
            'password_confirmation' => 'Test123456789'
        ])
            ->assertStatus(400)
            ->assertJsonFragment(['email' => 'This password reset token is invalid.']);
    }

    /**
     *
     * A test to check the status of response when a user has an invalid email for the token given.
     *
     * @test
     */
    public function test_sendResetResponse_invalid_email_token()
    {
        $password_broker = app(PasswordBroker::class);
        $token = $password_broker->createToken($this->studentUser); //create reset password token

        $this->postJson('/api/password/reset', [
            'token' => $token,
            'email' =>  $this->moduleTutorUser->email,
            'password' => 'Test123456789',
            'password_confirmation' => 'Test123456789'
        ])
            ->assertStatus(400)
            ->assertJsonFragment(['email' => 'This password reset token is invalid.']);
    }
}
