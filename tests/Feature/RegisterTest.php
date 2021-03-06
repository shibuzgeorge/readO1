<?php

namespace Tests\Feature;

use App\Notifications\VerifyEmail;
use App\User;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
    }
    /** @test */
    public function can_register()
    {
        Notification::fake();

        $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'test@test.app',
            'password' => 'secret',
            'password_confirmation' => 'secret',
        ])
        ->assertSuccessful()
        ->assertJsonFragment(['status' => trans('verification.sent')]);

        $user = User::where('email', 'test@test.app')->first();

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    /** @test */
    public function can_not_register_with_existing_email()
    {
        factory(User::class)->create(['email' => 'test@test.app']);

        $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'test@test.app',
            'password' => 'secret',
            'password_confirmation' => 'secret',
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
    }
}
