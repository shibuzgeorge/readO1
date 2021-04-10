<?php

namespace Tests\Feature;

use App\Notifications\ResetPassword;
use App\User;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ForgotPasswordTest extends TestCase
{
    /** @var \App\User */
    protected $user;

    /** @var \App\User */
    protected $adminUser;

    /** @var \App\User */
    protected $moduleTutorUser;

    /** @var \App\User */
    protected $studentUser;

    public function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
        $this->user = factory(User::class)->create();
        $this->studentUser = User::whereHas('role', function($q){
            $q->where('name', 'Student');
        })->first();
    }

    /**
     *
     * A test to check the status of response when a user enters a valid email address.
     *
     * @test
     */
      public function test_sendEmailLinkResponse()
      {
           Notification::fake();

           $this->postJson('/api/password/email', [
               'email' =>  $this->studentUser->email,
           ])
               ->assertSuccessful()
               ->assertJsonFragment(['status' => 'We have emailed your password reset link!']);

           Notification::assertSentTo($this->studentUser, ResetPassword::class);
      }

    /**
     *
     * A test to check the status of response when a user enters an invalid email address.
     *
     * @test
     */
    public function test_sendEmailLinkResponse_failed()
    {
        $this->postJson('/api/password/email', [
            'email' =>  'random@email.com',
        ])
            ->assertStatus(400)
            ->assertJsonFragment(['email' => 'We can\'t find a user with that email address.']);
    }
}
