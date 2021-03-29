<?php

namespace Tests\Feature;

use App\ExtensiveReadingCategory;
use App\Textbook;
use App\User;
use Tests\TestCase;

class ExtensiveReadingCategoryTest extends TestCase
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
     * A test to check data for index page authorised.
     *
     */
    public function test_index_page_authorised(){
        $erc = ExtensiveReadingCategory::with('textbooks')->get()->toArray();

        $this->actingAs($this->adminUser)
            ->getJson('/api/extensiveReading')
            ->assertJson($erc)
            ->assertStatus(200);

        $this->actingAs($this->moduleTutorUser)
            ->getJson('/api/extensiveReading')
            ->assertJson($erc)
            ->assertStatus(200);

        $this->actingAs($this->studentUser)
            ->getJson('/api/extensiveReading')
            ->assertJson($erc)
            ->assertStatus(200);
    }

    /**
     * A test to check data for displaying all categories authorised.
     *
     */
    public function test_categories_authorised(){
        $erc = ExtensiveReadingCategory::all()->toArray();

        $this->actingAs($this->adminUser)
            ->getJson('/api/extensiveReading/categories')
            ->assertJson($erc)
            ->assertStatus(200);

        $this->actingAs($this->moduleTutorUser)
            ->getJson('/api/extensiveReading/categories')
            ->assertJson($erc)
            ->assertStatus(200);

        $this->actingAs($this->studentUser)
            ->getJson('/api/extensiveReading/categories')
            ->assertJson($erc)
            ->assertStatus(200);
    }

    /**
     * A test to check data for edit page authorised by logging in as a Admin or Module Tutor.
     *
     */
    public function test_edit_page_authorised(){
        $id = ExtensiveReadingCategory::inRandomOrder()->first()->id;
        $erc = ExtensiveReadingCategory::find($id);
        $textbooks = $erc->textbooks()->get()->toArray();
        $unassigned = Textbook::doesntHave('modules')->doesntHave('extensiveReadingCategories')->get()->toArray();

        $this->actingAs($this->adminUser)
            ->getJson('/api/extensiveReading/'.$id.'/edit')
            ->assertJsonFragment(
                [
                    'name' => $erc->name,
                    'description' => $erc->description,
                    'textbooks' => $textbooks,
                    'unassigned' => $unassigned,
                ]
            )
            ->assertStatus(200);

        $this->actingAs($this->moduleTutorUser)
            ->getJson('/api/extensiveReading/'.$id.'/edit')
            ->assertJsonFragment(
                [
                    'name' => $erc->name,
                    'description' => $erc->description,
                    'textbooks' => $textbooks,
                    'unassigned' => $unassigned,
                ]
            )
            ->assertStatus(200);
    }

    /**
     * A test to check edit id not found (404).
     *
     */
    public function test_edit_page_id_not_found_authorised(){

        $this->actingAs($this->adminUser)
            ->getJson('/api/extensiveReading/'.rand(1000, 10000).'/edit')
            ->assertJson([
                'Error' => 'Extensive Reading Category not found!'
            ]);
    }

    /**
     * A test to check data for edit page unauthorised by logging in as a Student.
     *
     */
    public function test_edit_page_unauthorised(){

        $id = ExtensiveReadingCategory::inRandomOrder()->first()->id;

        $this->actingAs($this->studentUser)
            ->getJson('/api/extensiveReading/'.$id.'/edit')
            ->assertJsonFragment(['error' => 'Unauthorized'])
            ->assertStatus(403);
    }

    /**
     *
     * A test for get data to view/show a extensive reading category
     *
     */
    public function test_show_page_found()
    {
        $id = ExtensiveReadingCategory::inRandomOrder()->first()->id;
        $erc = ExtensiveReadingCategory::find($id);
        $textbooks = $erc->textbooks()->get()->toArray();

        $this->actingAs($this->adminUser)
            ->getJson('/api/extensiveReading/'.$id)
            ->assertJson([
                'name' => $erc->name,
                'description' => $erc->description,
                'textbooks' => $textbooks
            ]);

        $this->actingAs($this->moduleTutorUser)
            ->getJson('/api/extensiveReading/'.$id)
            ->assertJson([
                'name' => $erc->name,
                'description' => $erc->description,
                'textbooks' => $textbooks
            ]);

        $this->actingAs($this->studentUser)
            ->getJson('/api/extensiveReading/'.$id)
            ->assertJson([
            'name' => $erc->name,
            'description' => $erc->description,
            'textbooks' => $textbooks
        ]);
    }

    /**
     *
     * A test to check view/show page not found error message
     *
     */
    public function test_show_page_not_found()
    {
        $this->actingAs($this->studentUser)
            ->getJson('/api/extensiveReading/'.rand(1000, 10000))
            ->assertJson([
               'Error' => 'Extensive Reading Category not found!'
            ]);
    }

    /**
     *
     * A test to check data for the create page for an extensive reading category by logging in as a Admin or Module Tutor
     *
     */
    public function test_create_a_extensive_reading_category_page_access_authorised()
    {

        $erc = ExtensiveReadingCategory::all()->toArray();

        $this->actingAs($this->adminUser)
            ->getJson('/api/extensiveReading/create')
            ->assertJson($erc)
            ->assertStatus(200);

        $this->actingAs($this->moduleTutorUser)
            ->getJson('/api/extensiveReading/create')
            ->assertJson($erc)
            ->assertStatus(200);
    }

    /**
     *
     * A test to check data for the create page for an extensive reading category by logging in as a Student
     *
     */
    public function test_create_a_extensive_reading_category_page_access_unauthorised()
    {

        $this->actingAs($this->studentUser)
            ->getJson('/api/extensiveReading/create')
            ->assertJsonFragment(['error' => 'Unauthorized'])
            ->assertStatus(403);
    }

    /**
     * A test to create a extensive reading category authorised by logging in as a Admin or Module Tutor
     *
     * @test
     * @return void
     */
    public function test_create_a_extensive_reading_category_authorised()
    {
        $extensive_reading_category1 = [
            'name' => 'Extensive Reading Test 1',
            'description' => 'This is a test to see if you can create an extensive reading category 1'
        ];

        $extensive_reading_category2 = [
            'name' => 'Extensive Reading Test 2',
            'description' => 'This is a test to see if you can create an extensive reading category 2'
        ];

        $this->actingAs($this->adminUser)
            ->postJson('/api/extensiveReading', $extensive_reading_category1)
            ->assertJsonFragment(['Success' => 'Successfully created the category'])
            ->assertStatus(200);

        $this->assertDatabaseHas('extensive_reading_categories', [
            'name' => $extensive_reading_category1['name'],
            'description' => $extensive_reading_category1['description']
        ]);

        $this->actingAs($this->moduleTutorUser)
            ->postJson('/api/extensiveReading', $extensive_reading_category2)
            ->assertJsonFragment(['Success' => 'Successfully created the category'])
            ->assertStatus(200);

        $this->assertDatabaseHas('extensive_reading_categories', [
            'name' => $extensive_reading_category2['name'],
            'description' => $extensive_reading_category2['description']
        ]);
    }

    /**
     * A test to create a extensive reading category which has name already been used.
     *
     * @test
     * @return void
     */
    public function test_create_a_extensive_reading_category_name_already_used()
    {
        $extensive_reading_category1 = [
            'name' => 'Extensive Reading Test 1',
            'description' => 'This is a test to see if you can create an extensive reading category 1'
        ];

        $extensive_reading_category2 = [
            'name' => 'Extensive Reading Test 2',
            'description' => 'This is a test to see if you can create an extensive reading category 2'
        ];

        //Admin test
        $this->actingAs($this->adminUser)
            ->postJson('/api/extensiveReading', $extensive_reading_category1)
            ->assertJsonFragment(['Success' => 'Successfully created the category'])
            ->assertStatus(200);

        $this->assertDatabaseHas('extensive_reading_categories', [
            'name' => $extensive_reading_category1['name'],
            'description' => $extensive_reading_category1['description']
        ]);

        $this->actingAs($this->adminUser)
            ->postJson('/api/extensiveReading', $extensive_reading_category1)
            ->assertJsonFragment([
                "errors" => [
                    "name" => ["The name has already been taken."]],
                "message" => "The given data was invalid."
            ])
            ->assertStatus(422);

        //Module Tutor Test
        $this->actingAs($this->moduleTutorUser)
            ->postJson('/api/extensiveReading', $extensive_reading_category2)
            ->assertJsonFragment(['Success' => 'Successfully created the category'])
            ->assertStatus(200);

        $this->assertDatabaseHas('extensive_reading_categories', [
            'name' => $extensive_reading_category2['name'],
            'description' => $extensive_reading_category2['description']
        ]);

        $this->actingAs($this->moduleTutorUser)
            ->postJson('/api/extensiveReading', $extensive_reading_category2)
            ->assertJsonFragment([
                "errors" => [
                    "name" => ["The name has already been taken."]],
                "message" => "The given data was invalid."
            ])
            ->assertStatus(422);
    }

    /**
     * A test to create an extensive_reading_category with no name.
     *
     * @test
     * @return void
     */
    public function test_create_a_extensive_reading_category_with_no_name()
    {
        $extensive_reading_category = [
            'name' => '',
            'description' => 'This is a test to see if you can create an extensive reading category with no name'
        ];

        //Admin
        $this->actingAs($this->adminUser)
            ->postJson('/api/extensiveReading', $extensive_reading_category)
            ->assertJsonFragment([
                "errors" => [
                    "name" => ["The name field is required."]],
                "message" => "The given data was invalid."
            ])
            ->assertStatus(422);

        //Module Tutor
        $this->actingAs($this->adminUser)
            ->postJson('/api/extensiveReading', $extensive_reading_category)
            ->assertJsonFragment([
                "errors" => [
                    "name" => ["The name field is required."]],
                "message" => "The given data was invalid."
            ])
            ->assertStatus(422);


    }

    /**
     * A test to create an extensive reading category unauthorised by logging in as a Student
     *
     * @test
     * @return void
     */
    public function test_create_an_extensive_reading_category_unauthorised()
    {
        $extensive_reading_category = [
            'name' => 'Extensive Reading Test Unauthorised',
            'description' => 'This is a test to see if you can create an extensive reading category unauthorised'
        ];

        $this->actingAs($this->studentUser)
            ->postJson('/api/extensiveReading', $extensive_reading_category)
            ->assertJsonFragment(['error' => 'Unauthorized'])
            ->assertStatus(403);

        $this->assertDatabaseMissing('extensive_reading_categories', [
            'name' => $extensive_reading_category['name'],
        ]);
    }

    /**
     * A test to check update id not found (404).
     *
     */
    public function test_update_page_id_not_found_authorised(){

        $extensive_reading_category = [
            'name' => 'Extensive Reading Update Test',
            'description' => 'This is a test to see if you can update an extensive reading category'
        ];

        $this->actingAs($this->adminUser)
            ->putJson('/api/extensiveReading/'.rand(1000, 10000), $extensive_reading_category)
            ->assertStatus(404);

        $this->actingAs($this->moduleTutorUser)
            ->putJson('/api/extensiveReading/'.rand(1000, 10000), $extensive_reading_category)
            ->assertStatus(404);
    }

    /**
     * A test to update an extensive reading category authorised by logging in as a Admin or Module Tutor
     *
     * @test
     * @return void
     */
    public function test_update_a_extensive_reading_category_authorised()
    {
        //Admin
        $extensive_reading_category_original = ExtensiveReadingCategory::inRandomOrder()->first();

        $extensive_reading_category_update = [
            'name' => 'Extensive Reading Update Test',
            'description' => 'This is a test to see if you can update an extensive reading category'
        ];

        $this->actingAs($this->adminUser)
            ->putJson('/api/extensiveReading/'.$extensive_reading_category_original->id, $extensive_reading_category_update)
            ->assertJsonFragment(['Success' => 'Successfully updated the category'])
            ->assertStatus(200);

        $this->assertDatabaseMissing('extensive_reading_categories', [
            'name' => $extensive_reading_category_original->name,
            'description' => $extensive_reading_category_original->description
        ]);

        $this->assertDatabaseHas('extensive_reading_categories', [
            'name' => $extensive_reading_category_update['name'],
            'description' => $extensive_reading_category_update['description']
        ]);

        //Module Tutor
        $extensive_reading_category_original2 = ExtensiveReadingCategory::inRandomOrder()->first();

        $extensive_reading_category_update2 = [
            'name' => 'Extensive Reading Update Test 2',
            'description' => 'This is a test to see if you can update an extensive reading category 2'
        ];

        $this->actingAs($this->moduleTutorUser)
            ->putJson('/api/extensiveReading/'.$extensive_reading_category_original2->id, $extensive_reading_category_update2)
            ->assertJsonFragment(['Success' => 'Successfully updated the category'])
            ->assertStatus(200);

        $this->assertDatabaseMissing('extensive_reading_categories', [
            'name' => $extensive_reading_category_original2->name,
            'description' => $extensive_reading_category_original2->description
        ]);

        $this->assertDatabaseHas('extensive_reading_categories', [
            'name' => $extensive_reading_category_update2['name'],
            'description' => $extensive_reading_category_update2['description']
        ]);
    }

    /**
     * A test to update a extensive reading category which has name already been used.
     *
     * @test
     * @return void
     */
    public function test_update_a_extensive_reading_category_with_name_already_used()
    {
        $extensive_reading_category_original = ExtensiveReadingCategory::inRandomOrder()->first();

        $extensive_reading_category_update = [
            'name' => ExtensiveReadingCategory::where('id', '!=', $extensive_reading_category_original->id)->inRandomOrder()->first()->name,
            'description' => 'This is a test to see if you can update an extensive reading category with name already used'
        ];

        $this->actingAs($this->adminUser)
            ->putJson('/api/extensiveReading/'.$extensive_reading_category_original->id, $extensive_reading_category_update)
            ->assertJsonFragment([
                "errors" => [
                    "name" => ["The name has already been taken."]],
                "message" => "The given data was invalid."
            ])
            ->assertStatus(422);

        $this->assertDatabaseHas('extensive_reading_categories', [
            'name' => $extensive_reading_category_original->name,
            'description' => $extensive_reading_category_original->description,
        ]);

        $this->actingAs($this->moduleTutorUser)
            ->putJson('/api/extensiveReading/'.$extensive_reading_category_original->id, $extensive_reading_category_update)
            ->assertJsonFragment([
                "errors" => [
                    "name" => ["The name has already been taken."]],
                "message" => "The given data was invalid."
            ])
            ->assertStatus(422);

        $this->assertDatabaseHas('extensive_reading_categories', [
            'name' => $extensive_reading_category_original->name,
            'description' => $extensive_reading_category_original->description,
        ]);

    }

    /**
     * A test to update a extensive reading category with no name.
     *
     * @test
     * @return void
     */
    public function test_update_a_extensive_reading_category_with_no_name()
    {
        $extensive_reading_category_original = ExtensiveReadingCategory::inRandomOrder()->first();

        $extensive_reading_category_update = [
            'name' => '',
            'description' => 'This is a test to see if you can update an extensive reading category with name already used'
        ];

        $this->actingAs($this->adminUser)
            ->putJson('/api/extensiveReading/'.$extensive_reading_category_original->id, $extensive_reading_category_update)
            ->assertJsonFragment([
                "errors" => [
                    "name" => ["The name field is required."]],
                "message" => "The given data was invalid."
            ])
            ->assertStatus(422);

        $this->assertDatabaseHas('extensive_reading_categories', [
            'name' => $extensive_reading_category_original->name,
            'description' => $extensive_reading_category_original->description,
        ]);

        $this->actingAs($this->moduleTutorUser)
            ->putJson('/api/extensiveReading/'.$extensive_reading_category_original->id, $extensive_reading_category_update)
            ->assertJsonFragment([
                "errors" => [
                    "name" => ["The name field is required."]],
                "message" => "The given data was invalid."
            ])
            ->assertStatus(422);

        $this->assertDatabaseHas('extensive_reading_categories', [
            'name' => $extensive_reading_category_original->name,
            'description' => $extensive_reading_category_original->description,
        ]);


    }

    /**
     * A test to update a extensive reading category unauthorised by logging in as a Student
     *
     * @test
     * @return void
     */
    public function test_update_a_extensive_reading_category_unauthorised()
    {
        $extensive_reading_category_original = ExtensiveReadingCategory::inRandomOrder()->first();

        $extensive_reading_category_update = [
            'name' => 'Extensive Reading Test',
            'description' => 'This is a test to see if you can update an extensive reading category unauthorised'
        ];

        $this->actingAs($this->studentUser)
            ->putJson('/api/extensiveReading/'.$extensive_reading_category_original->id, $extensive_reading_category_update)
            ->assertJsonFragment(['error' => 'Unauthorized'])
            ->assertStatus(403);

        $this->assertDatabaseMissing('extensive_reading_categories', [
            'name' => $extensive_reading_category_update['name'],
            'description' => $extensive_reading_category_update['description'],
        ]);
    }

    /**
     * A test to check delete id not found (404).
     *
     */
    public function test_delete_id_not_found_authorised(){

        $this->actingAs($this->adminUser)
            ->deleteJson('/api/extensiveReading/'.rand(1000, 10000))
            ->assertStatus(404);
    }

    /**
     * A test to check delete authorised by logging in as an Admin or Module Tutor.
     *
     */
    public function test_delete_authorised(){

        $extensive_reading_category_original = ExtensiveReadingCategory::inRandomOrder()->first();

        $this->actingAs($this->adminUser)
            ->deleteJson('/api/extensiveReading/'.$extensive_reading_category_original->id)
            ->assertStatus(200);

        $this->assertDatabaseMissing('extensive_reading_categories', [
            'name' => $extensive_reading_category_original->name,
        ]);

        $extensive_reading_category_original2 = ExtensiveReadingCategory::inRandomOrder()->first();

        $this->actingAs($this->moduleTutorUser)
            ->deleteJson('/api/extensiveReading/'.$extensive_reading_category_original2->id)
            ->assertStatus(200);

        $this->assertDatabaseMissing('extensive_reading_categories', [
            'name' => $extensive_reading_category_original2->name,
        ]);
    }

    /**
     * A test to check delete unauthorised by logging in as a Student or Module Tutor.
     *
     */
    public function test_delete_unauthorised(){

        $extensive_reading_category_original = ExtensiveReadingCategory::inRandomOrder()->first();

        $this->actingAs($this->studentUser)
            ->deleteJson('/api/extensiveReading/'.$extensive_reading_category_original->id)
            ->assertJsonFragment(['error' => 'Unauthorized'])
            ->assertStatus(403);

        $this->assertDatabaseHas('extensive_reading_categories', [
            'name' => $extensive_reading_category_original->name,
        ]);
    }
}

