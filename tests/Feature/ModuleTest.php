<?php

namespace Tests\Feature;

use App\Role;
use App\Textbook;
use App\YearGroup;
use App\Module;
use App\User;
use Tests\TestCase;

class ModuleTest extends TestCase
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
     * A test to create a module authorised by logging in as an Admin
     *
     * @test
     * @return void
     */
    public function test_create_a_module_authorised()
    {
        $module = [
            'module_name' => 'testing1234',
            'module_code' => 'CS37788',
            'module_year' => YearGroup::find(1)->id
        ];

        $this->actingAs($this->adminUser)
        ->postJson('/api/module', $module)
        ->assertSuccessful()
        ->assertStatus(200);

        $this->assertDatabaseHas('modules', [
            'name' => $module['module_name'],
            'module_code' => $module['module_code'],
            'year_group_id' => $module['module_year']
        ]);
    }

    /**
     * A test to create a module with 2 unassigned textbooks checked and authorised by logging in as an Admin
     *
     * @test
     * @return void
     */
    public function test_create_a_module_checking_unassigned_textbooks_authorised()
    {
        $unassignedTextbookIds = Textbook::doesntHave('modules')->doesntHave('extensiveReadingCategories')
            ->inRandomOrder()->take(2)->pluck('id')->toArray();

        $module = [
            'module_name' => 'testing1234',
            'module_code' => 'CS37788',
            'module_year' => YearGroup::find(1)->id,
            'checked' => $unassignedTextbookIds
        ];

        $this->actingAs($this->adminUser)
            ->postJson('/api/module', $module)
            ->assertSuccessful()
            ->assertStatus(200);

        $this->assertDatabaseHas('modules', [
            'name' => $module['module_name'],
            'module_code' => $module['module_code'],
            'year_group_id' => $module['module_year']
        ]);

        $getModule = Module::where('name', $module['module_name'])->first();

        foreach ($unassignedTextbookIds as $unassignedTextbookId){
            $this->assertDatabaseHas('module_textbook', [
                'module_id' => $getModule->id,
                'textbook_id' => $unassignedTextbookId
            ]);
        }
    }

    /**
     * A test to create a module unauthorised by logging in as a Student or Module Tutor.
     *
     * @test
     * @return void
     */
    public function test_create_a_module_unauthorised()
    {
        $module = [
            'module_name' => 'test module',
            'module_code' => 'CS567532',
            'module_year' => YearGroup::find(1)->id
        ];

        $this->actingAs($this->studentUser)
            ->postJson('/api/module', $module)
            ->assertJsonFragment(['error' => 'Unauthorized'])
            ->assertStatus(403);

        $this->assertDatabaseMissing('modules',[
            'name' => $module['module_name'],
            'module_code' => $module['module_code'],
            'year_group_id' => $module['module_year']
        ]);

        $this->actingAs($this->moduleTutorUser)
            ->postJson('/api/module', $module)
            ->assertJsonFragment(['error' => 'Unauthorized'])
            ->assertStatus(403);

        $this->assertDatabaseMissing('modules', [
            'name' => $module['module_name'],
            'module_code' => $module['module_code'],
            'year_group_id' => $module['module_year']
        ]);
    }

    /**
     * A test to delete a module authorised by logging in as an Admin.
     *
     * @test
     * @return void
     */
    public function test_delete_a_module_authorised()
    {
        $module = Module::find(1);
        $this->actingAs($this->adminUser)
            ->deleteJson('/api/module/'.$module->id)
            ->assertSuccessful()
            ->assertStatus(200);

        $this->assertDatabaseMissing('modules', [
            'name' => $module->name,
            'module_code' => $module->module_code,
            'year_group_id' => $module->year_group_id
        ]);
    }

    /**
     * A test to delete a module unauthorised by logging in as a Student or Module Tutor
     *
     * @test
     * @return void
     */
    public function test_delete_a_module_unauthorised()
    {
        $module = Module::find(1);

        $this->actingAs($this->studentUser)
            ->deleteJson('/api/module/'.$module->id)
            ->assertJsonFragment(['error' => 'Unauthorized'])
            ->assertStatus(403);

        $this->assertDatabaseHas('modules', [
            'name' => $module->name,
            'module_code' => $module->module_code,
            'year_group_id' => $module->year_group_id
        ]);

        $this->actingAs($this->moduleTutorUser)
            ->deleteJson('/api/module/'.$module->id)
            ->assertJsonFragment(['error' => 'Unauthorized'])
            ->assertStatus(403);

        $this->assertDatabaseHas('modules', [
            'name' => $module->name,
            'module_code' => $module->module_code,
            'year_group_id' => $module->year_group_id
        ]);
    }

    /**
     * A test to update a module authorised by logging in as a Admin or Module tutor
     *
     * @return void
     */
    public function test_update_a_module_authorised()
    {
        $year_group = YearGroup::find(1);

        $module = Module::create([
            'name' => 'original module',
            'module_code' => 'TS357755',
            'year_group_id' => $year_group->id]);

        $this->actingAs($this->adminUser)
            ->patchJson('/api/module/'.$module->id, [
                'module_name' => 'updated module 1',
                'module_code' => 'CS377887',
                'module_year' => YearGroup::find(2)->id
            ])->assertSuccessful()
            ->assertJsonFragment(['Success' => 'Successfully Updated!'])
            ->assertStatus(200);

        $this->assertDatabaseHas('modules', [
            'id' => $module->id,
            'name' => 'updated module 1',
            'module_code' => 'CS377887',
            'year_group_id' => YearGroup::find(2)->id
        ]);

        $this->actingAs($this->moduleTutorUser)
            ->patchJson('/api/module/'.$module->id, [
                'module_name' => 'updated module 2',
                'module_code' => 'CS3778878',
                'module_year' => YearGroup::find(3)->id
            ])->assertSuccessful()
            ->assertJsonFragment(['Success' => 'Successfully Updated!'])
            ->assertStatus(200);

        $this->assertDatabaseHas('modules', [
            'id' => $module->id,
            'name' => 'updated module 2',
            'module_code' => 'CS3778878',
            'year_group_id' => YearGroup::find(3)->id
        ]);
    }

    /**
     * A test to update a module with checked unassigned textbook authorised
     * Also test to uncheck a current textbook in the module
     * by logging in as a Admin or Module tutor
     *
     * @return void
     */
    public function test_update_a_module_checked_unassigned_textbooks_authorised()
    {
        $year_group = YearGroup::find(1);

        $module = Module::create([
            'name' => 'original module',
            'module_code' => 'TS357755',
            'year_group_id' => $year_group->id]);

        $unassignedTextbookIds = Textbook::doesntHave('modules')->doesntHave('extensiveReadingCategories')
            ->inRandomOrder()->take(2)->pluck('id')->toArray();

        $this->actingAs($this->adminUser)
            ->patchJson('/api/module/'.$module->id, [
                'module_name' => 'updated module 1',
                'module_code' => 'CS377887',
                'module_year' => YearGroup::find(2)->id,
                'checked' => $unassignedTextbookIds
            ])->assertSuccessful()
            ->assertJsonFragment(['Success' => 'Successfully Updated!'])
            ->assertStatus(200);

        $this->assertDatabaseHas('modules', [
            'id' => $module->id,
            'name' => 'updated module 1',
            'module_code' => 'CS377887',
            'year_group_id' => YearGroup::find(2)->id
        ]);

        foreach ($unassignedTextbookIds as $unassignedTextbookId){
            $this->assertDatabaseHas('module_textbook', [
                'module_id' => $module->id,
                'textbook_id' => $unassignedTextbookId
            ]);
        }

        $getTextbooks = $module->textbooks()->pluck('textbooks.id')->toArray();

        $this->actingAs($this->moduleTutorUser)
            ->patchJson('/api/module/'.$module->id, [
                'module_name' => 'updated module 2',
                'module_code' => 'CS3778878',
                'module_year' => YearGroup::find(3)->id,
                'checked' => $getTextbooks[0]
            ])->assertSuccessful()
            ->assertJsonFragment(['Success' => 'Successfully Updated!'])
            ->assertStatus(200);

        $this->assertDatabaseHas('modules', [
            'id' => $module->id,
            'name' => 'updated module 2',
            'module_code' => 'CS3778878',
            'year_group_id' => YearGroup::find(3)->id
        ]);

        $this->assertDatabaseHas('module_textbook', [
            'module_id' => $module->id,
            'textbook_id' => $getTextbooks[0]
        ]);

        $this->assertDatabaseMissing('module_textbook', [
            'module_id' => $module->id,
            'textbook_id' => $getTextbooks[1]
        ]);
    }

    /**
     * A test to update a module unauthorised by logging in as a Student
     *
     * @return void
     */
    public function test_update_a_module_unauthorised()
    {
        $year_group = YearGroup::find(1);

        $module = Module::create([
            'name' => 'original module',
            'module_code' => 'TS357755',
            'year_group_id' => $year_group->id]);

        $this->actingAs($this->studentUser)
            ->patchJson('/api/module/'.$module->id, [
                'module_name' => 'updated module 1',
                'module_code' => 'CS377887',
                'module_year' => YearGroup::find(2)->id
            ])->assertJsonFragment(['error' => 'Unauthorized'])
            ->assertStatus(403);

        $this->assertDatabaseMissing('modules', [
            'id' => $module->id,
            'name' => 'updated module 1',
            'module_code' => 'CS377887',
            'year_group_id' => YearGroup::find(2)->id
        ]);
    }

    /**
     * A test to assign students to a module authorised by logging in as a Module Tutor or Admin
     *
     * @return void
     */
    public function test_assign_students_to_a_module_authorised()
    {
        $year_group = YearGroup::find(1);

        $module = Module::create([
            'name' => 'original module',
            'module_code' => 'TS357755',
            'year_group_id' => $year_group->id]);

        $student1 = User::whereHas('role', function($q){
            $q->where('name', 'Student');
        })->inRandomOrder()->first();

        $student2 = User::whereHas('role', function($q){
            $q->where('name', 'Student');
        })->inRandomOrder()->first();

        $student3 = User::whereHas('role', function($q){
            $q->where('name', 'Student');
        })->inRandomOrder()->first();

        $this->actingAs($this->adminUser)
            ->postJson('/api/module/assignStudents/'.$module->id, [$student1->id,$student2->id,$student3->id])
            ->assertJsonFragment(['Success' => 'Successfully assigned'])
            ->assertStatus(200);

        $this->assertDatabaseHas('module_user', [
            'module_id' => $module->id,
            'user_id' => $student1->id
        ])->assertDatabaseHas('module_user', [
            'module_id' => $module->id,
            'user_id' => $student2->id
        ])->assertDatabaseHas('module_user', [
            'module_id' => $module->id,
            'user_id' => $student3->id
        ]);

        $module2 = Module::create([
            'name' => 'original module 2',
            'module_code' => 'TS357754',
            'year_group_id' => $year_group->id]);

        $this->actingAs($this->moduleTutorUser)
            ->postJson('/api/module/assignStudents/'.$module2->id, [$student1->id,$student2->id,$student3->id])
            ->assertJsonFragment(['Success' => 'Successfully assigned'])
            ->assertStatus(200);

        $this->assertDatabaseHas('module_user', [
            'module_id' => $module2->id,
            'user_id' => $student1->id
        ])->assertDatabaseHas('module_user', [
            'module_id' => $module2->id,
            'user_id' => $student2->id
        ])->assertDatabaseHas('module_user', [
            'module_id' => $module2->id,
            'user_id' => $student3->id
        ]);
    }

    /**
     * A test to de assign all students to a module authorised by logging in as a Module Tutor or Admin
     *
     * @return void
     */
    public function test_de_assign_students_to_a_module_authorised()
    {
        $year_group = YearGroup::find(1);

        $module = Module::create([
            'name' => 'original module',
            'module_code' => 'TS357755',
            'year_group_id' => $year_group->id]);

        $student1 = User::whereHas('role', function($q){
            $q->where('name', 'Student');
        })->inRandomOrder()->first();

        $student2 = User::whereHas('role', function($q){
            $q->where('name', 'Student');
        })->inRandomOrder()->first();

        $student3 = User::whereHas('role', function($q){
            $q->where('name', 'Student');
        })->inRandomOrder()->first();

        $this->actingAs($this->adminUser)
            ->postJson('/api/module/assignStudents/'.$module->id, [$student1->id,$student2->id,$student3->id])
            ->assertJsonFragment(['Success' => 'Successfully assigned'])
            ->assertStatus(200);

        $this->actingAs($this->adminUser)
            ->postJson('/api/module/assignStudents/'.$module->id, [])
            ->assertJsonFragment(['Success' => 'Successfully designed'])
            ->assertStatus(200);

        $this->assertDatabaseMissing('module_user', [
            'module_id' => $module->id,
            'user_id' => $student1->id
        ])->assertDatabaseMissing('module_user', [
            'module_id' => $module->id,
            'user_id' => $student2->id
        ])->assertDatabaseMissing('module_user', [
            'module_id' => $module->id,
            'user_id' => $student3->id
        ]);

        $module2 = Module::create([
            'name' => 'original module 2',
            'module_code' => 'TS357754',
            'year_group_id' => $year_group->id]);

        $this->actingAs($this->moduleTutorUser)
            ->postJson('/api/module/assignStudents/'.$module2->id, [$student1->id,$student2->id,$student3->id])
            ->assertJsonFragment(['Success' => 'Successfully assigned'])
            ->assertStatus(200);

        $this->actingAs($this->moduleTutorUser)
            ->postJson('/api/module/assignStudents/'.$module2->id, [])
            ->assertJsonFragment(['Success' => 'Successfully designed'])
            ->assertStatus(200);

        $this->assertDatabaseMissing('module_user', [
            'module_id' => $module2->id,
            'user_id' => $student1->id
        ])->assertDatabaseMissing('module_user', [
            'module_id' => $module2->id,
            'user_id' => $student2->id
        ])->assertDatabaseMissing('module_user', [
            'module_id' => $module2->id,
            'user_id' => $student3->id
        ]);
    }

    /**
     * A test to assign students to a module unauthorised by logging in as a Student
     *
     * @return void
     */
    public function test_assign_students_to_a_module_unauthorised()
    {
        $year_group = YearGroup::find(1);

        $module = Module::create([
            'name' => 'original module',
            'module_code' => 'TS357755',
            'year_group_id' => $year_group->id]);

        $student1 = User::whereHas('role', function($q){
            $q->where('name', 'Student');
        })->inRandomOrder()->first();

        $student2 = User::whereHas('role', function($q){
            $q->where('name', 'Student');
        })->inRandomOrder()->first();

        $student3 = User::whereHas('role', function($q){
            $q->where('name', 'Student');
        })->inRandomOrder()->first();

        $this->actingAs($this->studentUser)
            ->postJson('/api/module/assignStudents/'.$module->id, [$student1->id,$student2->id,$student3->id])
            ->assertJsonFragment(['error' => 'Unauthorized'])
            ->assertStatus(403);

        $this->assertDatabaseMissing('module_user', [
            'module_id' => $module->id,
            'user_id' => $student1->id
        ])->assertDatabaseMissing('module_user', [
            'module_id' => $module->id,
            'user_id' => $student2->id
        ])->assertDatabaseMissing('module_user', [
            'module_id' => $module->id,
            'user_id' => $student3->id
        ]);
    }

    /**
     * A test to assign students to a module authorised by logging in as a Module Tutor or Admin
     *
     * @return void
     */
    public function test_assign_module_tutors_to_a_module_authorised()
    {
        $year_group = YearGroup::find(1);

        $module = Module::create([
            'name' => 'original module',
            'module_code' => 'TS357755',
            'year_group_id' => $year_group->id]);

        $module_tutor1 = User::whereHas('role', function($q){
            $q->where('name', 'Module Tutor');
        })->inRandomOrder()->first();

        $module_tutor2 = User::whereHas('role', function($q){
            $q->where('name', 'Module Tutor');
        })->inRandomOrder()->first();

        $module_tutor3 = User::whereHas('role', function($q){
            $q->where('name', 'Module Tutor');
        })->inRandomOrder()->first();

        $this->actingAs($this->adminUser)
            ->postJson('/api/module/assignModuleTutors/'.$module->id, [$module_tutor1->id,$module_tutor2->id,$module_tutor3->id])
            ->assertJsonFragment(['Success' => 'Successfully assigned'])
            ->assertStatus(200);

        $this->assertDatabaseHas('module_user', [
            'module_id' => $module->id,
            'user_id' => $module_tutor1->id
        ])->assertDatabaseHas('module_user', [
            'module_id' => $module->id,
            'user_id' => $module_tutor2->id
        ])->assertDatabaseHas('module_user', [
            'module_id' => $module->id,
            'user_id' => $module_tutor3->id
        ]);

    }

    /**
     * A test to de assign all module tutors to a module authorised by logging in as a Module Tutor or Admin
     *
     * @return void
     */
    public function test_de_assign_module_tutor_to_a_module_authorised()
    {
        $year_group = YearGroup::find(1);

        $module = Module::create([
            'name' => 'original module',
            'module_code' => 'TS357755',
            'year_group_id' => $year_group->id]);

        $module_tutor1 = User::whereHas('role', function($q){
            $q->where('name', 'Module Tutor');
        })->inRandomOrder()->first();

        $module_tutor2 = User::whereHas('role', function($q){
            $q->where('name', 'Module Tutor');
        })->inRandomOrder()->first();

        $module_tutor3 = User::whereHas('role', function($q){
            $q->where('name', 'Module Tutor');
        })->inRandomOrder()->first();

        $this->actingAs($this->adminUser)
            ->postJson('/api/module/assignModuleTutors/'.$module->id, [$module_tutor1->id,$module_tutor2->id,$module_tutor3->id])
            ->assertJsonFragment(['Success' => 'Successfully assigned'])
            ->assertStatus(200);

        $this->actingAs($this->adminUser)
            ->postJson('/api/module/assignModuleTutors/'.$module->id, [])
            ->assertJsonFragment(['Success' => 'Successfully designed'])
            ->assertStatus(200);

        $this->assertDatabaseMissing('module_user', [
            'module_id' => $module->id,
            'user_id' => $module_tutor1->id
        ])->assertDatabaseMissing('module_user', [
            'module_id' => $module->id,
            'user_id' => $module_tutor2->id
        ])->assertDatabaseMissing('module_user', [
            'module_id' => $module->id,
            'user_id' => $module_tutor3->id
        ]);

    }

    /**
     * A test to assign students to a module unauthorised by logging in as a Student
     *
     * @return void
     */
    public function test_assign_module_tutors_to_a_module_unauthorised()
    {
        $year_group = YearGroup::find(1);

        $module = Module::create([
            'name' => 'original module',
            'module_code' => 'TS357755',
            'year_group_id' => $year_group->id]);

        $module_tutor1 = User::whereHas('role', function($q){
            $q->where('name', 'Module Tutor');
        })->inRandomOrder()->first();

        $module_tutor2 = User::whereHas('role', function($q){
            $q->where('name', 'Module Tutor');
        })->inRandomOrder()->first();

        $module_tutor3 = User::whereHas('role', function($q){
            $q->where('name', 'Module Tutor');
        })->inRandomOrder()->first();

        $this->actingAs($this->studentUser)
            ->postJson('/api/module/assignStudents/'.$module->id, [$module_tutor1->id,$module_tutor2->id,$module_tutor3->id])
            ->assertJsonFragment(['error' => 'Unauthorized'])
            ->assertStatus(403);

        $this->assertDatabaseMissing('module_user', [
            'module_id' => $module->id,
            'user_id' => $module_tutor1->id
        ])->assertDatabaseMissing('module_user', [
            'module_id' => $module->id,
            'user_id' => $module_tutor2->id
        ])->assertDatabaseMissing('module_user', [
            'module_id' => $module->id,
            'user_id' => $module_tutor3->id
        ]);
    }

    /**
     * A test to check if Admin can retrieve all modules with year groups.
     *
     * @return void
     */
    public function test_index_module_as_admin()
    {
        $allModules = Module::with('yearGroup')->get()->toArray();
        $this->actingAs($this->adminUser)
            ->getJson('/api/module')
            ->assertJson($allModules)
            ->assertStatus(200);
    }

    /**
     * A test to check if Students and Modules Tutors only retrieve modules they are assigned to.
     *
     * @return void
     */
    public function test_index_module_as_student_or_module_tutor()
    {
        $assignedStudentModules = $this->studentUser->modules()->with('yearGroup')->get()->toArray();
        $this->actingAs($this->studentUser)
            ->getJson('/api/module')
            ->assertJson($assignedStudentModules)
            ->assertStatus(200);

        $assignedModuleTutorModules = $this->moduleTutorUser->modules()->with('yearGroup')->get()->toArray();
        $this->actingAs($this->moduleTutorUser)
            ->getJson('/api/module')
            ->assertJson($assignedModuleTutorModules)
            ->assertStatus(200);
    }

    /**
     * A test to check data for create page as an Admin.
     *
     * @return void
     */
    public function test_create_page_module_as_admin_authorised()
    {
        $modules = Module::with('yearGroup')->get()->toArray();
        $unassigned = Textbook::doesntHave('modules')->doesntHave('extensiveReadingCategories')->get()->toArray();
        $year_groups = YearGroup::all()->toArray();

        $this->actingAs($this->adminUser)
            ->getJson('/api/module/create')
            ->assertJsonFragment([
                'modules' => $modules,
                'year_groups' => $year_groups,
                'unassigned' => $unassigned
            ])
            ->assertStatus(200);
    }

    /**
     * A test for unauthorised access create page.
     *
     * @return void
     */
    public function test_create_page_module_as_student_or_module_tutor_unauthorised()
    {
        $this->actingAs($this->studentUser)
            ->getJson('/api/module/create')
            ->assertJsonFragment(['error' => 'Unauthorized'])
            ->assertStatus(403);

        $this->actingAs($this->moduleTutorUser)
            ->getJson('/api/module/create')
            ->assertJsonFragment(['error' => 'Unauthorized'])
            ->assertStatus(403);
    }

    /**
     * A test to get all users for a module logged in as an Admin or Module Tutor.
     *
     * @return void
     */
    public function test_getUsersForModule_as_admin_or_module_tutor_authorised()
    {
        $randomModule1 = Module::inRandomOrder()->first();
        $users1 = $randomModule1->users()->get()->toArray();
        $this->actingAs($this->adminUser)
            ->getJson('/api/module/getUsersForModule/'.$randomModule1->id)
            ->assertJson($users1)
            ->assertStatus(200);

        $randomModule2 = $this->moduleTutorUser->modules()->inRandomOrder()->first();
        $users2 = $randomModule2->users()->get()->toArray();
        $this->actingAs($this->moduleTutorUser)
            ->getJson('/api/module/getUsersForModule/'.$randomModule2->id)
            ->assertJson($users2)
            ->assertStatus(200);
    }

    /**
     * A test for permission denied access to get all users for a module logged in as an Student.
     *
     * @return void
     */
    public function test_getUsersForModule_as_student_unauthorised()
    {
        $randomModule = Module::inRandomOrder()->first();
        $this->actingAs($this->studentUser)
            ->getJson('/api/module/getUsersForModule/'.$randomModule->id)
            ->assertJsonFragment(['error' => 'Unauthorized'])
            ->assertStatus(403);


    }

    /**
     * A test for permission denied access to get all users for a module not assigned to the Module Tutor.
     *
     * @return void
     */
    public function test_getUsersForModule_permission_denied_for_module_tutor()
    {
        $unAssignedModule = Module::whereDoesntHave('users', function ($query){
            $query->where('user_id', $this->moduleTutorUser->id);
        })->first();

        $this->actingAs($this->moduleTutorUser)
            ->getJson('/api/module/getUsersForModule/'.$unAssignedModule->id)
            ->assertJsonFragment(['Error' => 'Permission Denied'])
            ->assertStatus(200);

    }

    /**
     * A test to getAllStudents Authorised by logging in as a Admin or Module Tutor
     *
     * @return void
     */
    public function test_getAllStudents_authorised()
    {
        $users = Role::where('name', 'Student')->first()->users()->get()->toArray();

        $this->actingAs($this->adminUser)
            ->getJson('/api/module/getAllStudents')
            ->assertJson($users)
            ->assertStatus(200);

        $this->actingAs($this->moduleTutorUser)
            ->getJson('/api/module/getAllStudents')
            ->assertJson($users)
            ->assertStatus(200);

    }

    /**
     * A test to getAllStudents unauthorised by logging in as a Student
     *
     * @return void
     */
    public function test_getAllStudents_unauthorised()
    {
        $this->actingAs($this->studentUser)
            ->getJson('/api/module/getAllStudents')
            ->assertJsonFragment(['error' => 'Unauthorized'])
            ->assertStatus(403);
    }

    /**
     * A test to getAllModuleTutors Authorised by logging in as a Admin
     *
     * @return void
     */
    public function test_getAllModuleTutors_authorised()
    {
        $moduleTutors = Role::where('name', 'Module Tutor')->first()->users()->get()->toArray();

        $this->actingAs($this->adminUser)
            ->getJson('/api/module/getAllModuleTutors')
            ->assertJson($moduleTutors)
            ->assertStatus(200);

    }

    /**
     * A test to getAllModuleTutors unauthorised by logging in as a Student or Module Tutor
     *
     * @return void
     */
    public function test_getAllModuleTutors_unauthorised()
    {
        $this->actingAs($this->moduleTutorUser)
            ->getJson('/api/module/getAllModuleTutors')
            ->assertJsonFragment(['error' => 'Unauthorized'])
            ->assertStatus(403);

        $this->actingAs($this->studentUser)
            ->getJson('/api/module/getAllStudents')
            ->assertJsonFragment(['error' => 'Unauthorized'])
            ->assertStatus(403);
    }

    /**
     * A test to view a module based on module id and has the right permission to view
     *
     * @return void
     */
    public function test_show_view_module_page_data_with_permission_to_view()
    {
        //Admins
        $randomModule = Module::inRandomOrder()->first();
        $this->actingAs($this->adminUser)
            ->getJson('/api/module/'.$randomModule->id)
            ->assertJsonFragment(
                [
                    'name' => $randomModule->name,
                    'code' => $randomModule->module_code,
                    'textbooks' => $randomModule->textbooks()->get()->toArray()
                ]
            )
            ->assertStatus(200);

        //Module Tutors
        $randomModule = $this->moduleTutorUser->modules()->inRandomOrder()->first();
        $this->actingAs($this->moduleTutorUser)
            ->getJson('/api/module/'.$randomModule->id)
            ->assertJsonFragment(
                [
                    'name' => $randomModule->name,
                    'code' => $randomModule->module_code,
                    'textbooks' => $randomModule->textbooks()->get()->toArray()
                ]
            )
            ->assertStatus(200);

        //Students
        $randomModule = $this->studentUser->modules()->inRandomOrder()->first();
        $this->actingAs($this->studentUser)
            ->getJson('/api/module/'.$randomModule->id)
            ->assertJsonFragment(
                [
                    'name' => $randomModule->name,
                    'code' => $randomModule->module_code,
                    'textbooks' => $randomModule->textbooks()->get()->toArray()
                ]
            )
            ->assertStatus(200);

    }

    /**
     * A test to view a module based on module id and has no permission to view
     *
     * @return void
     */
    public function test_show_view_module_page_data_with_no_permission_to_view()
    {
        //Module Tutors
        $unAssignedModule = Module::whereDoesntHave('users', function ($query){
            $query->where('user_id', $this->moduleTutorUser->id);
        })->first();

        $this->actingAs($this->moduleTutorUser)
            ->getJson('/api/module/'.$unAssignedModule->id)
            ->assertJsonFragment(
                [
                    'Error' => 'Permission denied to view module!'
                ]
            )
            ->assertStatus(200);

        //Students
        $unAssignedModule = Module::whereDoesntHave('users', function ($query){
            $query->where('user_id', $this->studentUser->id);
        })->first();

        $this->actingAs($this->studentUser)
            ->getJson('/api/module/'.$unAssignedModule->id)
            ->assertJsonFragment(
                [
                    'Error' => 'Permission denied to view module!'
                ]
            )
            ->assertStatus(200);
    }

    /**
     * A test for module not found.
     *
     * @return void
     */
    public function test_not_found_module()
    {
        $this->actingAs($this->adminUser)
            ->getJson('/api/module/'.rand(10000,99999))
            ->assertJsonFragment(
                [
                    'Error' => 'Module not found!'
                ]
            )
            ->assertStatus(200);

        $this->actingAs($this->moduleTutorUser)
            ->getJson('/api/module/'.rand(10000,99999))
            ->assertJsonFragment(
                [
                    'Error' => 'Module not found!'
                ]
            )
            ->assertStatus(200);

        $this->actingAs($this->studentUser)
            ->getJson('/api/module/'.rand(10000,99999))
            ->assertJsonFragment(
                [
                    'Error' => 'Module not found!'
                ]
            )
            ->assertStatus(200);
    }

    /**
     * A test to check data for the edit module page authorised by logging in as Admin
     *
     * @return void
     */
    public function test_edit_page_authorised()
    {
        $randomModule = Module::with('yearGroup')->inRandomOrder()->first();

        $m = Module::with('yearGroup')->find($randomModule->id);
        $textbooks = $m->textbooks()->get()->toArray();
        $unassigned = Textbook::doesntHave('modules')->doesntHave('extensiveReadingCategories')->get()->toArray();

        $this->actingAs($this->adminUser)
            ->getJson('/api/module/'.$randomModule->id.'/edit')
            ->assertJsonFragment(
                [
                'name' => $m->name,
                'code' => $m->module_code,
                'year' => $m->yearGroup->toArray(),
                'textbooks' => $textbooks,
                'unassigned' => $unassigned
                ]
            )
            ->assertStatus(200);

    }

    /**
     * A test to check data for the edit module page unauthorised by logging in a Student
     *
     * @return void
     */
    public function test_edit_page_unauthorised()
    {
        $randomModule = Module::with('yearGroup')->inRandomOrder()->first();

        $this->actingAs($this->studentUser)
            ->getJson('/api/module/'.$randomModule->id.'/edit')
            ->assertJsonFragment(['error' => 'Unauthorized'])
            ->assertStatus(403);

    }

    /**
     * A test to check data for the edit module page permission denied as module tutor is not assigned to the module
     *
     * @return void
     */
    public function test_edit_page_permission_denied_module_tutor()
    {
        $unAssignedModule = Module::whereDoesntHave('users', function ($query){
            $query->where('user_id', $this->moduleTutorUser->id);
        })->first();

        $this->actingAs($this->moduleTutorUser)
            ->getJson('/api/module/'.$unAssignedModule->id.'/edit')
            ->assertJsonFragment(['Error' => 'Permission denied to edit module!'])
            ->assertStatus(200);

    }

    /**
     * A test to check data for the edit module page unauthorised by logging in a Module Tutor or Student
     *
     * @return void
     */
    public function test_edit_page_module_not_found()
    {

        $this->actingAs($this->adminUser)
            ->getJson('/api/module/'.rand(10000,99999).'/edit')
            ->assertJsonFragment(
                [
                    'Error' => 'Module not found!'
                ]
            )
            ->assertStatus(200);

    }

}
