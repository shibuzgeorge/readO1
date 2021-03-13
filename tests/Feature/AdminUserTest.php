<?php

namespace Tests\Feature;

use App\Notifications\AdminUserCreate;
use App\Role;
use App\User;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AdminUserTest extends TestCase
{
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

    /**
     * A test to view all users data authorised
     *
     * @test
     * @return void
     */
    public function test_index_view_all_users_authorised()
    {
        $users = User::with('role')->get()->toArray();
        $roles = Role::all()->toArray();

        $this->actingAs($this->adminUser)
            ->getJson('/api/admin/users')
            ->assertJsonFragment([
             'users' => $users,
             'roles' => $roles
             ])->assertSuccessful()
             ->assertStatus(200);
    }

    /**
     * A test to view all users data unauthorised by logging in as a Student or Module Tutor.
     *
     * @test
     * @return void
     */
    public function test_index_view_all_users_unauthorised()
    {
        $this->actingAs($this->studentUser)
            ->getJson('/api/admin/users')
            ->assertJsonFragment(['error' => 'Unauthorized'])
            ->assertStatus(403);

        $this->actingAs($this->moduleTutorUser)
            ->getJson('/api/admin/users')
            ->assertJsonFragment(['error' => 'Unauthorized'])
            ->assertStatus(403);

    }

    /**
     * A test to check the data for the create page for an admin authorised
     *
     * @test
     * @return void
     */
    public function test_create_users_page_form_authorised()
    {
        $roles = Role::all()->toArray();

        $this->actingAs($this->adminUser)
            ->getJson('/api/admin/users/create')
            ->assertJson($roles)
            ->assertStatus(200);
    }

    /**
     * A test to check the data for the create page unauthorised by logging in as a Student or Module Tutor.
     *
     * @test
     * @return void
     */
    public function test_create_users_page_form_unauthorised()
    {
        $this->actingAs($this->moduleTutorUser)
            ->getJson('/api/admin/users/create')
            ->assertJsonFragment(['error' => 'Unauthorized'])
            ->assertStatus(403);

        $this->actingAs($this->studentUser)
            ->getJson('/api/admin/users/create')
            ->assertJsonFragment(['error' => 'Unauthorized'])
            ->assertStatus(403);
    }

    /**
     * A test to create new users for an admin with valid information.
     *
     * @test
     * @return void
     */
    public function test_store_create_a_user_authorised_and_valid()
    {
        Notification::fake();

        //Create a new Student
        $newStudentUser = [
            'name' => 'New Student User',
            'email' => 'newstudentuser@newstudentuser.com',
            'role' => Role::where('name', 'Student')->first()->id,
        ];

        $this->actingAs($this->adminUser)
        ->postJson('/api/admin/users', $newStudentUser)
        ->assertSuccessful();

        Notification::assertSentTo(User::where('email', $newStudentUser['email'])->first(), AdminUserCreate::class);

        $this->assertDatabaseHas('users', [
            'name' => $newStudentUser['name'],
            'email' => $newStudentUser['email'],
            'role_id' => $newStudentUser['role']
        ]);

        //Create a new Module Tutor
        $newModuleTutor = [
            'name' => 'New Module Tutor',
            'email' => 'newmoduletutor@newmoduletutor.com',
            'role' => Role::where('name', 'Module Tutor')->first()->id,
        ];

        $this->actingAs($this->adminUser)
            ->postJson('/api/admin/users', $newModuleTutor)
            ->assertSuccessful();

        Notification::assertSentTo(User::where('email', $newModuleTutor['email'])->first(), AdminUserCreate::class);

        $this->assertDatabaseHas('users', [
            'name' => $newModuleTutor['name'],
            'email' => $newModuleTutor['email'],
            'role_id' => $newModuleTutor['role']
        ]);

        //Create a new Admin
        $newAdmin = [
            'name' => 'New Admin',
            'email' => 'newadmin@newadmin.com',
            'role' => Role::where('name', 'Admin')->first()->id,
        ];

        $this->actingAs($this->adminUser)
            ->postJson('/api/admin/users', $newAdmin)
            ->assertSuccessful();

        Notification::assertSentTo(User::where('email', $newAdmin['email'])->first(), AdminUserCreate::class);

        $this->assertDatabaseHas('users', [
            'name' => $newAdmin['name'],
            'email' => $newAdmin['email'],
            'role_id' => $newAdmin['role']
        ]);

    }

    /**
     * A test to create new users new unauthorised by logging in as a Student or Module Tutor.
     *
     * @test
     * @return void
     */
    public function test_store_create_a_user_unauthorised()
    {
        $newStudentUser = [
            'name' => 'New Student User',
            'email' => 'newstudentuser@newstudentuser.com',
            'role' => Role::where('name', 'Student')->first()->id,
        ];

        $this->actingAs($this->moduleTutorUser)
            ->postJson('/api/admin/users', $newStudentUser)
            ->assertJsonFragment(['error' => 'Unauthorized'])
            ->assertStatus(403);

        $this->assertDatabaseMissing('users', [
            'name' => $newStudentUser['name'],
            'email' => $newStudentUser['email'],
            'role_id' => $newStudentUser['role']
        ]);

        $this->actingAs($this->studentUser)
            ->postJson('/api/admin/users', $newStudentUser)
            ->assertJsonFragment(['error' => 'Unauthorized'])
            ->assertStatus(403);

        $this->assertDatabaseMissing('users', [
            'name' => $newStudentUser['name'],
            'email' => $newStudentUser['email'],
            'role_id' => $newStudentUser['role']
        ]);
    }

    /**
     * A test to create new users for an admin with invalid information.
     *
     * @test
     * @return void
     */
    public function test_store_create_a_user_authorised_and_invalid()
    {
        //Create a new Student with invalid email
        $newUserInvalidEmail = [
            'name' => 'New Student User',
            'email' => 'newstudentuser.com',
            'role' => Role::where('name', 'Student')->first()->id,
        ];

        $this->actingAs($this->adminUser)
            ->postJson('/api/admin/users', $newUserInvalidEmail)
            ->assertJsonFragment(['email' => array('The email must be a valid email address.')])
            ->assertStatus(422);

        //Create a new Student with email already used
        $newUserEmailAlreadyUsed = [
            'name' => 'New Student User',
            'email' => User::inRandomOrder()->first()->email,
            'role' => Role::where('name', 'Student')->first()->id,
        ];

        $this->actingAs($this->adminUser)
            ->postJson('/api/admin/users', $newUserEmailAlreadyUsed)
            ->assertJsonFragment(['email' => array('The email has already been taken.')])
            ->assertStatus(422);

        //Create a new Student with name over 255 characters
        $newUserNameTooLong = [
            'name' => str_repeat('a', 256),
            'email' => 'newstudent@newstudent.com',
            'role' => Role::where('name', 'Student')->first()->id,
        ];

        $this->actingAs($this->adminUser)
            ->postJson('/api/admin/users', $newUserNameTooLong)
            ->assertJsonFragment(['name' => array('The name may not be greater than 255 characters.')])
            ->assertStatus(422);

    }

    /**
     * A test to check the data for the edit page for an admin authorised
     *
     * @test
     * @return void
     */
    public function test_edit_users_page_form_authorised()
    {
        $studentUser = User::whereHas('role', function($q){
            $q->where('name', 'Student');
        })->inRandomOrder()->first();

        $moduleTutorUser = User::whereHas('role', function($q){
            $q->where('name', 'Module Tutor');
        })->inRandomOrder()->first();

        $adminUser = User::whereHas('role', function($q){
            $q->where('name', 'Admin');
        })->inRandomOrder()->first();

        $roles = Role::all()->toArray();
        $student_user_role = $studentUser->role;
        $module_tutor_user_role = $moduleTutorUser->role;
        $admin_user_role = $adminUser->role;

        //Edit a student
        $this->actingAs($this->adminUser)
            ->getJson('/api/admin/users/'.$studentUser->id.'/edit')
            ->assertJsonFragment([
            'user' => $studentUser->toArray(),
            'user_role' => $student_user_role->toArray(),
            'roles' => $roles
            ])
            ->assertStatus(200);

        //Edit a module tutor
        $this->actingAs($this->adminUser)
            ->getJson('/api/admin/users/'.$moduleTutorUser->id.'/edit')
            ->assertJsonFragment([
                'user' => $moduleTutorUser->toArray(),
                'user_role' => $module_tutor_user_role->toArray(),
                'roles' => $roles
            ])
            ->assertStatus(200);

        //Edit a admin tutor
        $this->actingAs($this->adminUser)
            ->getJson('/api/admin/users/'.$adminUser->id.'/edit')
            ->assertJsonFragment([
                'user' => $adminUser->toArray(),
                'user_role' => $admin_user_role->toArray(),
                'roles' => $roles
            ])
            ->assertStatus(200);
    }

    /**
     * A test to check the data for the edit page unauthorised by logging in as a Student or Module Tutor.
     *
     * @test
     * @return void
     */
    public function test_edit_users_page_form_unauthorised()
    {
        $studentUser = User::whereHas('role', function($q){
            $q->where('name', 'Student');
        })->inRandomOrder()->first();

        $this->actingAs($this->moduleTutorUser)
            ->getJson('/api/admin/users/'.$studentUser->id.'/edit')
            ->assertJsonFragment(['error' => 'Unauthorized'])
            ->assertStatus(403);

        $this->actingAs($this->studentUser)
            ->getJson('/api/admin/users/'.$studentUser->id.'/edit')
            ->assertJsonFragment(['error' => 'Unauthorized'])
            ->assertStatus(403);
    }

    /**
     * A test for the edit page user not found.
     *
     * @test
     * @return void
     */
    public function test_edit_users_page_user_not_found()
    {
        $this->actingAs($this->adminUser)
            ->getJson('/api/admin/users/'.rand(10000,99999).'/edit')
            ->assertStatus(404);
    }

    /**
     * A test to update the users role for an admin authorised
     *
     * @test
     * @return void
     */
    public function test_update_user_role_authorised()
    {
        $studentUser = User::whereHas('role', function($q){
            $q->where('name', 'Student');
        })->inRandomOrder()->first();

        $moduleTutorUser = User::whereHas('role', function($q){
            $q->where('name', 'Module Tutor');
        })->inRandomOrder()->first();

        $adminUser = User::whereHas('role', function($q){
            $q->where('name', 'Admin');
        })->inRandomOrder()->first();

        //update a student to a module tutor
        $this->actingAs($this->adminUser)
            ->patchJson('/api/admin/users/'.$studentUser->id, ['checked' => $moduleTutorUser->role_id])
            ->assertJsonFragment([$moduleTutorUser->role_id])
            ->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'name' => $studentUser->name,
            'email' => $studentUser->email,
            'role_id' => $moduleTutorUser->role_id
        ]);

        //update a student to a admin
        $this->actingAs($this->adminUser)
            ->patchJson('/api/admin/users/'.$studentUser->id, ['checked' => $adminUser->role_id])
            ->assertJsonFragment([$adminUser->role_id])
            ->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'name' => $studentUser->name,
            'email' => $studentUser->email,
            'role_id' => $adminUser->role_id
        ]);

        //update a module tutor to a student
        $this->actingAs($this->adminUser)
            ->patchJson('/api/admin/users/'.$moduleTutorUser->id, ['checked' => $studentUser->role_id])
            ->assertJsonFragment([$studentUser->role_id])
            ->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'name' => $moduleTutorUser->name,
            'email' => $moduleTutorUser->email,
            'role_id' => $studentUser->role_id
        ]);

        //update a module tutor to a admin
        $this->actingAs($this->adminUser)
            ->patchJson('/api/admin/users/'.$moduleTutorUser->id, ['checked' => $adminUser->role_id])
            ->assertJsonFragment([$adminUser->role_id])
            ->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'name' => $moduleTutorUser->name,
            'email' => $moduleTutorUser->email,
            'role_id' => $adminUser->role_id
        ]);

        //update a admin to a student
        $this->actingAs($this->adminUser)
            ->patchJson('/api/admin/users/'.$adminUser->id, ['checked' => $studentUser->role_id])
            ->assertJsonFragment([$studentUser->role_id])
            ->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'name' => $adminUser->name,
            'email' => $adminUser->email,
            'role_id' => $studentUser->role_id
        ]);

        //update a admin to a module tutor
        $this->actingAs($this->adminUser)
            ->patchJson('/api/admin/users/'.$adminUser->id, ['checked' => $moduleTutorUser->role_id])
            ->assertJsonFragment([$moduleTutorUser->role_id])
            ->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'name' => $adminUser->name,
            'email' => $adminUser->email,
            'role_id' => $moduleTutorUser->role_id
        ]);
    }

    /**
     * A test to update the users role unauthorised by logging in as a Student or Module Tutor
     *
     * @test
     * @return void
     */
    public function test_update_user_role_unauthorised()
    {
        $studentUser = User::whereHas('role', function ($q) {
            $q->where('name', 'Student');
        })->inRandomOrder()->first();

        $moduleTutorUser = User::whereHas('role', function ($q) {
            $q->where('name', 'Module Tutor');
        })->inRandomOrder()->first();

        $adminUser = User::whereHas('role', function ($q) {
            $q->where('name', 'Admin');
        })->inRandomOrder()->first();

        $this->actingAs($this->studentUser)
            ->patchJson('/api/admin/users/'.$studentUser->id, ['checked' => $moduleTutorUser->role_id])
            ->assertJsonFragment(['error' => 'Unauthorized'])
            ->assertStatus(403);

        $this->assertDatabaseMissing('users', [
            'name' => $studentUser->name,
            'email' => $studentUser->email,
            'role_id' => $moduleTutorUser->role_id
        ]);

        $this->actingAs($this->moduleTutorUser)
            ->patchJson('/api/admin/users/'.$moduleTutorUser->id, ['checked' => $adminUser->role_id])
            ->assertJsonFragment(['error' => 'Unauthorized'])
            ->assertStatus(403);

        $this->assertDatabaseMissing('users', [
            'name' => $moduleTutorUser->name,
            'email' => $moduleTutorUser->email,
            'role_id' => $adminUser->role_id
        ]);
    }

    /**
     * A test to delete a user authorised by logging in as a Admin
     *
     * @test
     * @return void
     */
    public function test_delete_user_authorised()
    {
        $studentUser = User::whereHas('role', function ($q) {
            $q->where('name', 'Student');
        })->inRandomOrder()->first();

        $this->actingAs($this->adminUser)
            ->deleteJson('/api/admin/users/'.$studentUser->id)
            ->assertSuccessful();

        $this->assertDatabaseMissing('users', [
            'name' => $studentUser->name,
            'email' => $studentUser->email,
        ]);

    }

    /**
     * A test to delete a user authorised by logging in as a Admin
     *
     * @test
     * @return void
     */
    public function test_delete_user_unauthorised()
    {
        $studentUser = User::whereHas('role', function ($q) {
            $q->where('name', 'Student');
        })->inRandomOrder()->first();

        $this->actingAs($this->moduleTutorUser)
            ->deleteJson('/api/admin/users/'.$studentUser->id)
            ->assertJsonFragment(['error' => 'Unauthorized'])
            ->assertStatus(403);

        $this->assertDatabaseHas('users', [
            'name' => $studentUser->name,
            'email' => $studentUser->email,
        ]);

        $this->actingAs($this->studentUser)
            ->deleteJson('/api/admin/users/'.$studentUser->id)
            ->assertJsonFragment(['error' => 'Unauthorized'])
            ->assertStatus(403);

        $this->assertDatabaseHas('users', [
            'name' => $studentUser->name,
            'email' => $studentUser->email,
        ]);

    }
}
