<?php

namespace Tests\Feature;

use App\YearGroup;
use App\User;
use Tests\TestCase;

class YearGroupTest extends TestCase
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
        $this->adminUser = User::whereHas('role', function ($q) {
            $q->where('name', 'Admin');
        })->first();
        $this->moduleTutorUser = User::whereHas('role', function ($q) {
            $q->where('name', 'Module Tutor');
        })->first();
        $this->studentUser = User::whereHas('role', function ($q) {
            $q->where('name', 'Student');
        })->first();
    }

    /**
     * A test to check data for index page authorised by logging in as an Admin.
     *
     */
    public function test_index_page_admin_authorised()
    {
        $year_groups = YearGroup::has('module')->get()->toArray();

        $this->actingAs($this->adminUser)
            ->getJson('/api/yearGroup')
            ->assertJson($year_groups)
            ->assertStatus(200);
    }

    /**
     * A test to check data for index page authorised by logging in as an Module Tutor.
     *
     */
    public function test_index_page_module_tutor_authorised()
    {
        $module_ids = $this->moduleTutorUser->modules()->get()->pluck('id');
        $year_groups = YearGroup::
        whereHas('module', function ($query) use ($module_ids){
            $query->whereIn('modules.id', $module_ids);
        })->get()->toArray();

        $this->actingAs($this->moduleTutorUser)
            ->getJson('/api/yearGroup')
            ->assertJson($year_groups)
            ->assertStatus(200);
    }

    /**
     * A test to check data for index page authorised by logging in as an Student.
     *
     */
    public function test_index_page_student_authorised()
    {
        $module_ids = $this->studentUser->modules()->get()->pluck('id');
        $year_groups = YearGroup::
        whereHas('module', function ($query) use ($module_ids){
            $query->whereIn('modules.id', $module_ids);
        })->get()->toArray();

        $this->actingAs($this->studentUser)
            ->getJson('/api/yearGroup')
            ->assertJson($year_groups)
            ->assertStatus(200);
    }

    /**
     * A test to check data for edit page authorised by logging in as an Admin.
     *
     */
    public function test_edit_page_authorised(){
        $year_group = YearGroup::find(1);

        $this->actingAs($this->adminUser)
            ->getJson('/api/yearGroup/'.$year_group->id.'/edit')
            ->assertJson($year_group->toArray())
            ->assertStatus(200);
    }

    /**
     * A test to check edit id not found (404).
     *
     */
    public function test_edit_page_id_not_found_authorised(){

        $this->actingAs($this->adminUser)
            ->getJson('/api/yearGroup/'.rand(1000, 10000).'/edit')
            ->assertStatus(404);
    }

    /**
     * A test to check data for edit page unauthorised by logging in as a Module Tutor or Student.
     *
     */
    public function test_edit_page_unauthorised(){

        $year_group = YearGroup::find(1);

        $this->actingAs($this->moduleTutorUser)
            ->getJson('/api/yearGroup/'.$year_group->id.'/edit')
            ->assertJsonFragment(['error' => 'Unauthorized'])
            ->assertStatus(403);

        $this->actingAs($this->studentUser)
            ->getJson('/api/yearGroup/'.$year_group->id.'/edit')
            ->assertJsonFragment(['error' => 'Unauthorized'])
            ->assertStatus(403);
    }

    /**
     * A test to check the data for the modules for a particular year group with textbooks
     *
     * @test
     * @return void
     */
    public function test_get_modules_for_year_group()
    {
        $year_group = YearGroup::has('module.textbooks')->inRandomOrder()->first();
        $admin = [
        'modules' => $year_group->module()->has('textbooks')->with('textbooks')->get()
        ];
        $module_ids = $this->moduleTutorUser->modules()->get()->pluck('id');
        $module_tutor = [
            'modules' => $modules = $year_group->module()->whereIn('modules.id', $module_ids)->has('textbooks')->with('textbooks')->get()
        ];

        $this->actingAs($this->adminUser)
            ->getJson('/api/yearGroup/getModulesForYearGroup/'.$year_group->id)
            ->assertJson($admin['modules']->toArray())
            ->assertStatus(200);

        $this->actingAs($this->moduleTutorUser)
            ->getJson('/api/yearGroup/getModulesForYearGroup/'.$year_group->id)
            ->assertJson($module_tutor['modules']->toArray())
            ->assertStatus(200);

    }

    /**
     * A test to check the data for getting all modules for a particular year group with or without textbook
     *
     * @test
     * @return void
     */
    public function test_get_all_modules_for_year_group()
    {
        $year_group = YearGroup::has('module')->inRandomOrder()->first();
        $admin = [
            'modules' => $year_group->module()->get()
        ];
        $module_ids = $this->moduleTutorUser->modules()->get()->pluck('id');
        $module_tutor = [
            'modules' => $modules = $year_group->module()->whereIn('modules.id', $module_ids)->get()
        ];

        $this->actingAs($this->adminUser)
            ->getJson('/api/yearGroup/getAllModulesForYearGroup/'.$year_group->id)
            ->assertJson($admin['modules']->toArray())
            ->assertStatus(200);

        $this->actingAs($this->moduleTutorUser)
            ->getJson('/api/yearGroup/getAllModulesForYearGroup/'.$year_group->id)
            ->assertJson($module_tutor['modules']->toArray())
            ->assertStatus(200);

    }

    /**
     * A test to create a year group authorised by logging in as an Admin
     *
     * @test
     * @return void
     */
    public function test_create_a_year_group_authorised()
    {
        $year_group = [
            'name' => 'Year Test'
        ];

        $this->actingAs($this->adminUser)
            ->postJson('/api/yearGroup', $year_group)
            ->assertJsonFragment(['Success' => 'Successfully created the year group!'])
            ->assertStatus(200);

        $this->assertDatabaseHas('year_groups', [
            'name' => $year_group['name'],
        ]);
    }

    /**
     * A test to create a year group which has already been used.
     *
     * @test
     * @return void
     */
    public function test_create_a_year_group_name_already_used()
    {
        $year_group = [
            'name' => 'Year Test'
        ];

        $this->actingAs($this->adminUser)
            ->postJson('/api/yearGroup', $year_group)
            ->assertJsonFragment(['Success' => 'Successfully created the year group!'])
            ->assertStatus(200);

        $this->assertDatabaseHas('year_groups', [
            'name' => $year_group['name'],
        ]);

        //Creating same year group again
        $this->actingAs($this->adminUser)
            ->postJson('/api/yearGroup', $year_group)
            ->assertJsonFragment([
                    "errors" => [
                    "name" => ["The name has already been taken."]],
                    "message" => "The given data was invalid."
                    ])
            ->assertStatus(422);


    }

    /**
     * A test to create a year group with no name.
     *
     * @test
     * @return void
     */
    public function test_create_a_year_group_no_name()
    {
        $year_group = [
            'name' => ''
        ];

        $this->actingAs($this->adminUser)
            ->postJson('/api/yearGroup', $year_group)
            ->assertJsonFragment([
                "errors" => [
                    "name" => ["The name field is required."]],
                "message" => "The given data was invalid."
            ])
            ->assertStatus(422);


    }

    /**
     * A test to create a year group unauthorised by logging in as an Module Tutor or Student
     *
     * @test
     * @return void
     */
    public function test_create_a_year_group_unauthorised()
    {
        $year_group = [
            'name' => 'Year Test'
        ];

        $this->actingAs($this->moduleTutorUser)
            ->postJson('/api/yearGroup', $year_group)
            ->assertJsonFragment(['error' => 'Unauthorized'])
            ->assertStatus(403);

        $this->assertDatabaseMissing('year_groups', [
            'name' => $year_group['name'],
        ]);

        $this->actingAs($this->studentUser)
            ->postJson('/api/yearGroup', $year_group)
            ->assertJsonFragment(['error' => 'Unauthorized'])
            ->assertStatus(403);

        $this->assertDatabaseMissing('year_groups', [
            'name' => $year_group['name'],
        ]);
    }

    /**
     * A test to check update id not found (404).
     *
     */
    public function test_update_page_id_not_found_authorised(){

        $new_year_group = [
            'edit_name' => 'Year Updated'
        ];

        $this->actingAs($this->adminUser)
            ->putJson('/api/yearGroup/'.rand(1000, 10000), $new_year_group)
            ->assertStatus(404);
    }

    /**
     * A test to update a year group authorised by logging in as an Admin
     *
     * @test
     * @return void
     */
    public function test_update_a_year_group_authorised()
    {
        $year_group_original = YearGroup::find(1);

        $new_year_group = [
            'edit_name' => 'Year Updated'
        ];
        $this->actingAs($this->adminUser)
            ->putJson('/api/yearGroup/'.$year_group_original->id, $new_year_group)
            ->assertJsonFragment(['Success' => 'Successfully updated the year group!'])
            ->assertStatus(200);

        $this->assertDatabaseMissing('year_groups', [
            'name' => $year_group_original->name,
        ]);

        $this->assertDatabaseHas('year_groups', [
            'name' => $new_year_group['edit_name'],
        ]);
    }

    /**
     * A test to update a year group which has name already been used.
     *
     * @test
     * @return void
     */
    public function test_update_a_year_group_name_already_used()
    {
        $year_group_original = YearGroup::find(1);

        $new_year_group = [
            'edit_name' => YearGroup::find(2)->name
        ];

        $this->actingAs($this->adminUser)
            ->putJson('/api/yearGroup/'.$year_group_original->id, $new_year_group)
            ->assertJsonFragment([
                "errors" => [
                    "edit_name" => ["The edit name has already been taken."]],
                "message" => "The given data was invalid."
            ])
            ->assertStatus(422);

        $this->assertDatabaseHas('year_groups', [
            'name' => $year_group_original->name,
        ]);

    }

    /**
     * A test to update a year group with no name.
     *
     * @test
     * @return void
     */
    public function test_update_a_year_group_no_name()
    {
        $year_group_original = YearGroup::find(1);

        $new_year_group = [
            'edit_name' => ''
        ];

        $this->actingAs($this->adminUser)
            ->putJson('/api/yearGroup/'.$year_group_original->id, $new_year_group)
            ->assertJsonFragment([
                "errors" => [
                    "edit_name" => ["The edit name field is required."]],
                "message" => "The given data was invalid."
            ])
            ->assertStatus(422);

        $this->assertDatabaseHas('year_groups', [
            'name' => $year_group_original->name,
        ]);


    }

    /**
     * A test to update a year group unauthorised by logging in as an Module Tutor or Student
     *
     * @test
     * @return void
     */
    public function test_update_a_year_group_unauthorised()
    {
        $year_group_original = YearGroup::find(1);

        $new_year_group = [
            'edit_name' => 'Year Updated'
        ];

        $this->actingAs($this->moduleTutorUser)
            ->putJson('/api/yearGroup/'.$year_group_original->id, $new_year_group)
            ->assertJsonFragment(['error' => 'Unauthorized'])
            ->assertStatus(403);

        $this->assertDatabaseMissing('year_groups', [
            'name' => $new_year_group['edit_name'],
        ]);

        $this->actingAs($this->studentUser)
            ->putJson('/api/yearGroup/'.$year_group_original->id, $new_year_group)
            ->assertJsonFragment(['error' => 'Unauthorized'])
            ->assertStatus(403);

        $this->assertDatabaseMissing('year_groups', [
            'name' => $new_year_group['edit_name'],
        ]);
    }

    /**
     * A test to check delete id not found (404).
     *
     */
    public function test_delete_id_not_found_authorised(){

        $this->actingAs($this->adminUser)
            ->deleteJson('/api/yearGroup/'.rand(1000, 10000))
            ->assertStatus(404);
    }

    /**
     * A test to check delete authorised by logging in as an Admin.
     *
     */
    public function test_delete_authorised(){

        $year_group_original = YearGroup::find(1);

        $this->actingAs($this->adminUser)
            ->deleteJson('/api/yearGroup/'.$year_group_original->id)
            ->assertStatus(200);

        $this->assertDatabaseMissing('year_groups', [
            'name' => $year_group_original->name,
        ]);
    }

    /**
     * A test to check delete unauthorised by logging in as a Student or Module Tutor.
     *
     */
    public function test_delete_unauthorised(){

        $year_group_original = YearGroup::find(1);

        $this->actingAs($this->moduleTutorUser)
            ->deleteJson('/api/yearGroup/'.$year_group_original->id)
            ->assertJsonFragment(['error' => 'Unauthorized'])
            ->assertStatus(403);

        $this->assertDatabaseHas('year_groups', [
            'name' => $year_group_original->name,
        ]);

        $this->actingAs($this->studentUser)
            ->deleteJson('/api/yearGroup/'.$year_group_original->id)
            ->assertJsonFragment(['error' => 'Unauthorized'])
            ->assertStatus(403);

        $this->assertDatabaseHas('year_groups', [
            'name' => $year_group_original->name,
        ]);
    }
}

