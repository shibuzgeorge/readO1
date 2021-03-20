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
    public function test_index_page_authorised(){
        $year_groups = YearGroup::all()->toArray();

        $this->actingAs($this->adminUser)
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

        //Creating same year group again
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
        //Creating same year group again
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
        //Creating same year group again
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
     * A test to create a year group unauthorised by logging in as an Module Tutor or Student
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

