<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;

class LoginTest extends TestCase
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
        $this->adminUser = User::whereHas('role', function($q){
            $q->where('name', 'Admin');
        })->first();
        $this->moduleTutorUser = User::whereHas('role', function($q){
            $q->where('name', 'Module Tutor');
        })->first();
        $this->studentUser = User::whereHas('role', function($q){
            $q->where('name', 'Student');
        })->first();
    }

    /** @test */
    public function authenticate()
    {
        $this->postJson('/api/login', [
            'email' => $this->user->email,
            'password' => 'password',
        ])
        ->assertSuccessful()
        ->assertJsonStructure(['token', 'expires_in'])
        ->assertJson(['token_type' => 'bearer']);
    }

    /** @test */
    public function fetch_the_current_user()
    {
        $this->actingAs($this->user)
            ->getJson('/api/user')
            ->assertSuccessful()
            ->assertJsonStructure(['id', 'name', 'email']);
    }

    /** @test */
    public function log_out()
    {
        $token = $this->postJson('/api/login', [
            'email' => $this->user->email,
            'password' => 'password',
        ])->json()['token'];

        $this->postJson("/api/logout?token=$token")
            ->assertSuccessful();

        $this->getJson("/api/user?token=$token")
            ->assertStatus(401);
    }

    /**
     * A test to check the current role of the user when logged in.
     * @test
     */
    public function test_check_role_when_logged_in(){

        $this->actingAs($this->adminUser)
            ->getJson('/api/user/role')
            ->assertSuccessful()
            ->assertJsonFragment(['Admin']);

        $this->actingAs($this->moduleTutorUser)
            ->getJson('/api/user/role')
            ->assertSuccessful()
            ->assertJsonFragment(['Module Tutor']);

        $this->actingAs($this->studentUser)
            ->getJson('/api/user/role')
            ->assertSuccessful()
            ->assertJsonFragment(['Student']);
    }
}
