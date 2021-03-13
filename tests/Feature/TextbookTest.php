<?php

namespace Tests\Feature;

use App\ExtensiveReadingCategory;
use App\Textbook;
use App\Module;
use App\User;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class TextbookTest extends TestCase
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
     * A test to create a textbook to a module authorised by logging in as an Admin and Module Tutor
     *
     * @test
     * @return void
     */
    public function test_create_a_textbook_for_one_module_authorised()
    {
        $randomModule = Module::inRandomOrder()->first();

        $AdminTextbook = [
            'title' => 'Testing create a new textbook as admin',
            'description' => 'This is a test for create a new textbook as admin',
            'file' =>  new UploadedFile(public_path('/readingMaterial/example.pdf'), 'example.pdf', 'application/pdf', null,  true),
            'section' => 'module',
            'selected' => $randomModule->toJson()
        ];

        //Admin textbook upload
        $this->actingAs($this->adminUser)
            ->postJson('/api/textbook', $AdminTextbook)
            ->assertSuccessful()
            ->assertStatus(200);

        $this->assertDatabaseHas('textbooks', [
            'title' => $AdminTextbook['title'],
            'description' => $AdminTextbook['description'],
            'file' => base64_encode(file_get_contents(public_path('/readingMaterial/example.pdf')))
        ]);

        $this->assertDatabaseHas('module_textbook', [
            'module_id' => $randomModule->id,
            'textbook_id' => Textbook::where('title', $AdminTextbook['title'])->first()->id,
        ]);

        //Module Tutor textbook upload
        $ModuleTutorTextbook = [
            'title' => 'Testing create a new textbook as module tutor',
            'description' => 'This is a test for create a new textbook as module tutor',
            'file' =>  new UploadedFile(public_path('/readingMaterial/example.pdf'), 'example.pdf', 'application/pdf', null,  true),
            'section' => 'module',
            'selected' => $this->moduleTutorUser->modules()->first()->toJson()
        ];

        $this->actingAs($this->moduleTutorUser)
            ->postJson('/api/textbook', $ModuleTutorTextbook)
            ->assertSuccessful()
            ->assertStatus(200);

        $this->assertDatabaseHas('textbooks', [
            'title' => $ModuleTutorTextbook['title'],
            'description' => $ModuleTutorTextbook['description'],
            'file' => base64_encode(file_get_contents(public_path('/readingMaterial/example.pdf')))
        ]);

        $this->assertDatabaseHas('module_textbook', [
            'module_id' => $this->moduleTutorUser->modules()->first()->id,
            'textbook_id' => Textbook::where('title', $ModuleTutorTextbook['title'])->first()->id,
        ]);
    }

    /**
     * A test to create a textbook for multiple modules authorised by logging in as an Admin and Module Tutor
     *
     * @test
     * @return void
     */
    public function test_create_a_textbook_for_multiple_modules_authorised()
    {
        $randomModules = Module::inRandomOrder()->take(3)->get();

        $AdminTextbook = [
            'title' => 'Testing create a new textbook as admin',
            'description' => 'This is a test for create a new textbook as admin',
            'file' =>  new UploadedFile(public_path('/readingMaterial/example.pdf'), 'example.pdf', 'application/pdf', null,  true),
            'section' => 'module',
            'selected' => $randomModules->toJson()
        ];

        //Admin textbook upload
        $this->actingAs($this->adminUser)
            ->postJson('/api/textbook', $AdminTextbook)
            ->assertSuccessful()
            ->assertStatus(200);

        $this->assertDatabaseHas('textbooks', [
            'title' => $AdminTextbook['title'],
            'description' => $AdminTextbook['description'],
            'file' => base64_encode(file_get_contents(public_path('/readingMaterial/example.pdf')))
        ]);

        $randomModulesIds = $randomModules->pluck('id')->toArray();

        foreach ($randomModulesIds as $randomModulesId){
            $this->assertDatabaseHas('module_textbook', [
                'module_id' => $randomModulesId,
                'textbook_id' => Textbook::where('title', $AdminTextbook['title'])->first()->id,
            ]);
        }

        //Module Tutor textbook upload
        $ModuleTutorTextbook = [
            'title' => 'Testing create a new textbook as module tutor',
            'description' => 'This is a test for create a new textbook as module tutor',
            'file' =>  new UploadedFile(public_path('/readingMaterial/example.pdf'), 'example.pdf', 'application/pdf', null,  true),
            'section' => 'module',
            'selected' => $this->moduleTutorUser->modules()->take(2)->get()->toJson()
        ];

        $this->actingAs($this->moduleTutorUser)
            ->postJson('/api/textbook', $ModuleTutorTextbook)
            ->assertSuccessful()
            ->assertStatus(200);

        $this->assertDatabaseHas('textbooks', [
            'title' => $ModuleTutorTextbook['title'],
            'description' => $ModuleTutorTextbook['description'],
            'file' => base64_encode(file_get_contents(public_path('/readingMaterial/example.pdf')))
        ]);

        $moduleIds = $this->moduleTutorUser->modules()->take(2)->pluck('modules.id')->toArray();

        foreach ($moduleIds as $moduleId){
            $this->assertDatabaseHas('module_textbook', [
                'module_id' => $moduleId,
                'textbook_id' => Textbook::where('title', $ModuleTutorTextbook['title'])->first()->id,
            ]);
        }
    }

    /**
     * A test to create a textbook unauthorised to a module by logging in as a Student
     *
     * @test
     * @return void
     */
    public function test_create_a_textbook_for_one_module_unauthorised_student()
    {
        $randomModule = Module::inRandomOrder()->first();

        $textbook = [
            'title' => 'Testing create a new textbook as student',
            'description' => 'This is a test for create a new textbook as student',
            'file' =>  new UploadedFile(public_path('/readingMaterial/example.pdf'), 'example.pdf', 'application/pdf', null,  true),
            'section' => 'module',
            'selected' => $randomModule->toJson()
        ];

        $this->actingAs($this->studentUser)
            ->postJson('/api/textbook', $textbook)
            ->assertJsonFragment(['error' => 'Unauthorized'])
            ->assertStatus(403);

        $this->assertDatabaseMissing('textbooks', [
            'title' => $textbook['title'],
            'description' => $textbook['description'],
            'file' => base64_encode(file_get_contents(public_path('/readingMaterial/example.pdf')))
        ]);

    }

    /**
     * A test to create a textbook authorised to a extensive reading category
     * by logging in as a Admin or Module Tutor.
     *
     * @test
     * @return void
     */
    public function test_create_a_textbook_for_extensive_reading_category_authorised()
    {
        $randomExtensiveReadingCategory1 = ExtensiveReadingCategory::inRandomOrder()->first();

        $AdminTextbook = [
            'title' => 'Testing create a new textbook for extensive reading admin',
            'description' => 'This is a test for create a new textbook for extensive reading admin',
            'file' =>  new UploadedFile(public_path('/readingMaterial/example.pdf'), 'example.pdf', 'application/pdf', null,  true),
            'section' => 'extensiveReading',
            'selected' => $randomExtensiveReadingCategory1->toJson()
        ];

        //Admin textbook upload
        $this->actingAs($this->adminUser)
            ->postJson('/api/textbook', $AdminTextbook)
            ->assertSuccessful()
            ->assertStatus(200);

        $this->assertDatabaseHas('textbooks', [
            'title' => $AdminTextbook['title'],
            'description' => $AdminTextbook['description'],
            'file' => base64_encode(file_get_contents(public_path('/readingMaterial/example.pdf')))
        ]);

        $this->assertDatabaseHas('extensive_reading_category_textbook', [
            'extensive_reading_category_id' => $randomExtensiveReadingCategory1->id,
            'textbook_id' => Textbook::where('title', $AdminTextbook['title'])->first()->id,
        ]);

        $randomExtensiveReadingCategory2 = ExtensiveReadingCategory::inRandomOrder()->first();

        //Module Tutor textbook upload
        $ModuleTutorTextbook = [
            'title' => 'Testing create a new textbook for extensive reading module tutor',
            'description' => 'This is a test for create a new textbook for extensive reading module tutor',
            'file' =>  new UploadedFile(public_path('/readingMaterial/example.pdf'), 'example.pdf', 'application/pdf', null,  true),
            'section' => 'extensiveReading',
            'selected' => $randomExtensiveReadingCategory2->toJson()
        ];

        $this->actingAs($this->moduleTutorUser)
            ->postJson('/api/textbook', $ModuleTutorTextbook)
            ->assertSuccessful()
            ->assertStatus(200);

        $this->assertDatabaseHas('textbooks', [
            'title' => $ModuleTutorTextbook['title'],
            'description' => $ModuleTutorTextbook['description'],
            'file' => base64_encode(file_get_contents(public_path('/readingMaterial/example.pdf')))
        ]);

        $this->assertDatabaseHas('extensive_reading_category_textbook', [
            'extensive_reading_category_id' => $randomExtensiveReadingCategory2->id,
            'textbook_id' => Textbook::where('title', $ModuleTutorTextbook['title'])->first()->id,
        ]);

    }

    /**
     * A test to create a textbook unauthorised to a extensive reading category
     * by logging in as a Student.
     *
     * @test
     * @return void
     */
    public function test_create_a_textbook_for_extensive_reading_category_unauthorised_as_student()
    {
        $randomExtensiveReadingCategory1 = ExtensiveReadingCategory::inRandomOrder()->first();

        $StudentTextbook = [
            'title' => 'Testing create a new textbook for extensive reading student',
            'description' => 'This is a test for create a new textbook for extensive reading student',
            'file' =>  new UploadedFile(public_path('/readingMaterial/example.pdf'), 'example.pdf', 'application/pdf', null,  true),
            'section' => 'extensiveReading',
            'selected' => $randomExtensiveReadingCategory1->toJson()
        ];

        //Student textbook upload
        $this->actingAs($this->studentUser)
            ->postJson('/api/textbook', $StudentTextbook)
            ->assertJsonFragment(['error' => 'Unauthorized'])
            ->assertStatus(403);

        $this->assertDatabaseMissing('textbooks', [
            'title' => $StudentTextbook['title'],
            'description' => $StudentTextbook['description'],
            'file' => base64_encode(file_get_contents(public_path('/readingMaterial/example.pdf')))
        ]);

    }

    /**
     * A test to update a textbook to a module authorised by logging in as an Admin and Module Tutor.
     *
     * @test
     * @return void
     */
    public function test_update_a_textbook_for_one_module_authorised()
    {
        $randomModule = Module::inRandomOrder()->first();

        $AdminTextbook = [
            'title' => 'Testing update a textbook as admin',
            'description' => 'This is a test for update a textbook as admin',
            'file' =>  new UploadedFile(public_path('/readingMaterial/example.pdf'), 'example.pdf', 'application/pdf', null,  true),
            'section' => 'module',
            'selected' => $randomModule->toJson()
        ];

        $findTextbook1 = Textbook::doesntHave('extensiveReadingCategories')->inRandomOrder()->first();

        //Admin textbook update
        $this->actingAs($this->adminUser)
            ->patchJson('/api/textbook/'.$findTextbook1->id, $AdminTextbook)
            ->assertSuccessful()
            ->assertStatus(200);

        //Checks if the updated textbook is in the database
        $this->assertDatabaseHas('textbooks', [
            'id' => $findTextbook1->id,
            'title' => $AdminTextbook['title'],
            'description' => $AdminTextbook['description'],
            'file' => base64_encode(file_get_contents(public_path('/readingMaterial/example.pdf')))
        ]);

        //Checks if the old textbook title and description is missing from the database
        $this->assertDatabaseMissing('textbooks', [
            'id' => $findTextbook1->id,
            'title' => $findTextbook1->title,
            'description' => $findTextbook1->description,
            'file' => base64_encode(file_get_contents(public_path('/readingMaterial/example.pdf')))
        ]);

        //Checks if the module_id is associated to the textbook_id
        $this->assertDatabaseHas('module_textbook', [
            'module_id' => $randomModule->id,
            'textbook_id' => $findTextbook1->id,
        ]);

        $randomModuleModuleTutor = $this->moduleTutorUser->modules()->inRandomOrder()->first();
        /**
         * Module Tutor update textbook
         */
        $ModuleTutorTextbook = [
            'title' => 'Testing update a textbook as module tutor',
            'description' => 'This is a test for update a textbook as module tutor',
            'file' =>  new UploadedFile(public_path('/readingMaterial/example.pdf'), 'example.pdf', 'application/pdf', null,  true),
            'section' => 'module',
            'selected' => $randomModuleModuleTutor->toJson()
        ];

        $findTextbook2 = $this->moduleTutorUser->modules()->has('textbooks')->inRandomOrder()
            ->first()->textbooks()->inRandomOrder()->first();

        $this->actingAs($this->moduleTutorUser)
            ->patchJson('/api/textbook/'.$findTextbook2->id, $ModuleTutorTextbook)
            ->assertSuccessful()
            ->assertStatus(200);

        //Checks if the updated textbook is in the database
        $this->assertDatabaseHas('textbooks', [
            'id' => $findTextbook2->id,
            'title' => $ModuleTutorTextbook['title'],
            'description' => $ModuleTutorTextbook['description'],
            'file' => base64_encode(file_get_contents(public_path('/readingMaterial/example.pdf')))
        ]);

        //Checks if the old textbook title and description is missing from the database
        $this->assertDatabaseMissing('textbooks', [
            'id' => $findTextbook2->id,
            'title' => $findTextbook2->title,
            'description' => $findTextbook2->description,
            'file' => base64_encode(file_get_contents(public_path('/readingMaterial/example.pdf')))
        ]);

        //Checks if the module_id is associated to the textbook_id
        $this->assertDatabaseHas('module_textbook', [
            'module_id' => $randomModuleModuleTutor->id,
            'textbook_id' => $findTextbook2->id,
        ]);
    }

    /**
     * A test to update a textbook to a module unauthorised by logging in as an Student.
     *
     * @test
     * @return void
     */
    public function test_update_a_textbook_for_one_module_unauthorised_as_a_student()
    {
        $randomModule = Module::inRandomOrder()->first();

        $StudentTextbook = [
            'title' => 'Testing update a textbook for module as student',
            'description' => 'This is a test for update a textbook for module as student',
            'file' => new UploadedFile(public_path('/readingMaterial/example.pdf'), 'example.pdf', 'application/pdf', null, true),
            'section' => 'module',
            'selected' => $randomModule->toJson()
        ];

        $findTextbook1 = $this->studentUser->modules()->has('textbooks')->inRandomOrder()->first()
            ->textbooks()->inRandomOrder()->first();

        //Admin textbook update
        $this->actingAs($this->studentUser)
            ->patchJson('/api/textbook/' . $findTextbook1->id, $StudentTextbook)
            ->assertJsonFragment(['error' => 'Unauthorized'])
            ->assertStatus(403);

        //Checks if the updated textbook is not in the database
        $this->assertDatabaseMissing('textbooks', [
            'id' => $findTextbook1->id,
            'title' => $StudentTextbook['title'],
            'description' => $StudentTextbook['description'],
            'file' => base64_encode(file_get_contents(public_path('/readingMaterial/example.pdf')))
        ]);

        //Checks if the old textbook title and description is still in the database
        $this->assertDatabaseHas('textbooks', [
            'id' => $findTextbook1->id,
            'title' => $findTextbook1->title,
            'description' => $findTextbook1->description,
        ]);

    }

    /**
     * A test to update a textbook for multiple modules authorised by logging in as an Admin and Module Tutor.
     *
     * @test
     * @return void
     */
    public function test_update_a_textbook_for_multiple_modules_authorised()
    {
        $randomModulesAdmin = Module::inRandomOrder()->take(3)->get();

        $AdminTextbook = [
            'title' => 'Testing update a textbook for multiple modules as admin',
            'description' => 'This is a test for update a textbook multiple modules as admin',
            'file' =>  new UploadedFile(public_path('/readingMaterial/example.pdf'), 'example.pdf', 'application/pdf', null,  true),
            'section' => 'module',
            'selected' => $randomModulesAdmin->toJson()
        ];

        $findTextbook1 = Textbook::doesntHave('extensiveReadingCategories')->inRandomOrder()->first();

        //Admin textbook upload
        $this->actingAs($this->adminUser)
            ->patchJson('/api/textbook/'.$findTextbook1->id, $AdminTextbook)
            ->assertSuccessful()
            ->assertStatus(200);

        //Checks if the updated textbook is in the database
        $this->assertDatabaseHas('textbooks', [
            'id' => $findTextbook1->id,
            'title' => $AdminTextbook['title'],
            'description' => $AdminTextbook['description'],
            'file' => base64_encode(file_get_contents(public_path('/readingMaterial/example.pdf')))
        ]);

        //Checks if the old textbook title and description is missing from the database
        $this->assertDatabaseMissing('textbooks', [
            'id' => $findTextbook1->id,
            'title' => $findTextbook1->title,
            'description' => $findTextbook1->description,
            'file' => base64_encode(file_get_contents(public_path('/readingMaterial/example.pdf')))
        ]);

        //Checks if all the modules is associated to the textbook_id
        foreach ($randomModulesAdmin as $randomModule){
            $this->assertDatabaseHas('module_textbook', [
                'module_id' => $randomModule->id,
                'textbook_id' => $findTextbook1->id,
            ]);
        }

        /**
         * Module Tutor update
         */
        $randomModulesForModuleTutor = $this->moduleTutorUser->modules()->take(2)->get();

        //Module Tutor textbook update
        $ModuleTutorTextbook = [
            'title' => 'Testing update a textbook for multiple modules as module tutor',
            'description' => 'This is a test for update a textbook for multiple modules as module tutor',
            'file' =>  new UploadedFile(public_path('/readingMaterial/example.pdf'), 'example.pdf', 'application/pdf', null,  true),
            'section' => 'module',
            'selected' => $randomModulesForModuleTutor->toJson()
        ];

        $findTextbook2 = $this->moduleTutorUser->modules()->has('textbooks')->inRandomOrder()->first()
            ->textbooks()->inRandomOrder()->first();

        $this->actingAs($this->moduleTutorUser)
            ->patchJson('/api/textbook/'.$findTextbook2->id, $ModuleTutorTextbook)
            ->assertSuccessful()
            ->assertStatus(200);

        //Checks if the updated textbook is in the database
        $this->assertDatabaseHas('textbooks', [
            'id' => $findTextbook2->id,
            'title' => $ModuleTutorTextbook['title'],
            'description' => $ModuleTutorTextbook['description'],
            'file' => base64_encode(file_get_contents(public_path('/readingMaterial/example.pdf')))
        ]);

        //Checks if the old textbook title and description is missing from the database
        $this->assertDatabaseMissing('textbooks', [
            'id' => $findTextbook2->id,
            'title' => $findTextbook2->title,
            'description' => $findTextbook2->description,
            'file' => base64_encode(file_get_contents(public_path('/readingMaterial/example.pdf')))
        ]);

        //Checks if all the modules are associated to the textbook_id
        foreach ($randomModulesForModuleTutor as $randomModuleForModuleTutor){
            $this->assertDatabaseHas('module_textbook', [
                'module_id' => $randomModuleForModuleTutor->id,
                'textbook_id' => $findTextbook2->id,
            ]);
        }

    }

    /**
     * A test to update a textbook to a extensive reading category
     * authorised by logging in as an Admin and Module Tutor.
     *
     * @test
     * @return void
     */
    public function test_update_a_textbook_for_extensive_reading_category_authorised()
    {
        $randomExtensiveReadingCategory1 = ExtensiveReadingCategory::inRandomOrder()->first();

        $AdminTextbook = [
            'title' => 'Testing update a textbook for erc as admin',
            'description' => 'This is a test for update a textbook for erc as admin',
            'file' =>  new UploadedFile(public_path('/readingMaterial/example.pdf'), 'example.pdf', 'application/pdf', null,  true),
            'section' => 'extensiveReading',
            'selected' => $randomExtensiveReadingCategory1->toJson()
        ];

        $findTextbook1 = Textbook::doesntHave('modules')->inRandomOrder()->first();

        //Admin textbook update
        $this->actingAs($this->adminUser)
            ->patchJson('/api/textbook/'.$findTextbook1->id, $AdminTextbook)
            ->assertSuccessful()
            ->assertStatus(200);

        //Checks if the updated textbook is in the database
        $this->assertDatabaseHas('textbooks', [
            'id' => $findTextbook1->id,
            'title' => $AdminTextbook['title'],
            'description' => $AdminTextbook['description'],
            'file' => base64_encode(file_get_contents(public_path('/readingMaterial/example.pdf')))
        ]);

        //Checks if the old textbook title and description is missing from the database
        $this->assertDatabaseMissing('textbooks', [
            'id' => $findTextbook1->id,
            'title' => $findTextbook1->title,
            'description' => $findTextbook1->description,
            'file' => base64_encode(file_get_contents(public_path('/readingMaterial/example.pdf')))
        ]);

        //Checks if the extensive_reading_category_id is associated to the textbook_id
        $this->assertDatabaseHas('extensive_reading_category_textbook', [
            'extensive_reading_category_id' => $randomExtensiveReadingCategory1->id,
            'textbook_id' => $findTextbook1->id,
        ]);

        /**
         * Module Tutor update textbook
         */

        $randomExtensiveReadingCategory2 = ExtensiveReadingCategory::inRandomOrder()->first();

        $ModuleTutorTextbook = [
            'title' => 'Testing update a textbook for erc as module tutor',
            'description' => 'This is a test for update a textbook as for erc module tutor',
            'file' =>  new UploadedFile(public_path('/readingMaterial/example.pdf'), 'example.pdf', 'application/pdf', null,  true),
            'section' => 'extensiveReading',
            'selected' => $randomExtensiveReadingCategory2->toJson()
        ];

        $findTextbook2 = Textbook::doesntHave('modules')->inRandomOrder()->first();

        $this->actingAs($this->moduleTutorUser)
            ->patchJson('/api/textbook/'.$findTextbook2->id, $ModuleTutorTextbook)
            ->assertSuccessful()
            ->assertStatus(200);

        //Checks if the updated textbook is in the database
        $this->assertDatabaseHas('textbooks', [
            'id' => $findTextbook2->id,
            'title' => $ModuleTutorTextbook['title'],
            'description' => $ModuleTutorTextbook['description'],
            'file' => base64_encode(file_get_contents(public_path('/readingMaterial/example.pdf')))
        ]);

        //Checks if the old textbook title and description is missing from the database
        $this->assertDatabaseMissing('textbooks', [
            'id' => $findTextbook2->id,
            'title' => $findTextbook2->title,
            'description' => $findTextbook2->description,
            'file' => base64_encode(file_get_contents(public_path('/readingMaterial/example.pdf')))
        ]);

        //Checks if the extensive_reading_category_id is associated to the textbook_id
        $this->assertDatabaseHas('extensive_reading_category_textbook', [
            'extensive_reading_category_id' => $randomExtensiveReadingCategory2->id,
            'textbook_id' => $findTextbook2->id,
        ]);
    }

    /**
     * A test to update a textbook to for extensive reading category
     * unauthorised by logging in as an Student.
     *
     * @test
     * @return void
     */
    public function test_update_a_textbook_for_extensive_reading_category_unauthorised_as_a_student()
    {
        $randomExtensiveReadingCategory = ExtensiveReadingCategory::inRandomOrder()->first();

        $StudentTextbook = [
            'title' => 'Testing update a textbook for erc as student',
            'description' => 'This is a test for update a textbook for erc as student',
            'file' => new UploadedFile(public_path('/readingMaterial/example.pdf'), 'example.pdf', 'application/pdf', null, true),
            'section' => 'extensiveReading',
            'selected' => $randomExtensiveReadingCategory->toJson()
        ];

        $findTextbook1 = Textbook::doesntHave('modules')->inRandomOrder()->first();

        //Student textbook update
        $this->actingAs($this->studentUser)
            ->patchJson('/api/textbook/' . $findTextbook1->id, $StudentTextbook)
            ->assertJsonFragment(['error' => 'Unauthorized'])
            ->assertStatus(403);

        //Checks if the updated textbook is not in the database
        $this->assertDatabaseMissing('textbooks', [
            'id' => $findTextbook1->id,
            'title' => $StudentTextbook['title'],
            'description' => $StudentTextbook['description'],
            'file' => base64_encode(file_get_contents(public_path('/readingMaterial/example.pdf')))
        ]);

        //Checks if the old textbook title and description is still in the database
        $this->assertDatabaseHas('textbooks', [
            'id' => $findTextbook1->id,
            'title' => $findTextbook1->title,
            'description' => $findTextbook1->description,
        ]);

        //Checks if the extensive_reading_category_id is not associated to the textbook_id
        $this->assertDatabaseMissing('extensive_reading_category_textbook', [
            'extensive_reading_category_id' => $randomExtensiveReadingCategory->id,
            'textbook_id' => $findTextbook1->id,
        ]);
    }

    /**
     * A test to delete a textbook authorised by logging in as an Admin and Module Tutor.
     *
     * @test
     * @return void
     */
    public function test_delete_a_textbook_authorised()
    {
        //Admin delete textbook
        $textbook1 = Textbook::inRandomOrder()->first();
        $this->actingAs($this->adminUser)
            ->deleteJson('/api/textbook/'.$textbook1->id)
            ->assertSuccessful()
            ->assertStatus(200);

        $this->assertDatabaseMissing('textbooks', [
            'id' => $textbook1->id,
            'title' => $textbook1->title,
            'description' => $textbook1->description,
        ]);

        //Module Tutor delete textbook
        $textbook2 = $this->moduleTutorUser->modules()->has('textbooks')->inRandomOrder()->first()
            ->textbooks()->inRandomOrder()->first();

        $this->actingAs($this->moduleTutorUser)
            ->deleteJson('/api/textbook/'.$textbook2->id)
            ->assertSuccessful()
            ->assertStatus(200);

        $this->assertDatabaseMissing('textbooks', [
            'id' => $textbook2->id,
            'title' => $textbook2->title,
            'description' => $textbook2->description,
        ]);
    }

    /**
     * A test to delete a textbook unauthorised by logging in as a Student
     *
     * @test
     * @return void
     */
    public function test_delete_a_textbook_unauthorised()
    {
        $textbook = Textbook::inRandomOrder()->first();

        $this->actingAs($this->studentUser)
            ->deleteJson('/api/textbook/'.$textbook->id)
            ->assertJsonFragment(['error' => 'Unauthorized'])
            ->assertStatus(403);

        //Checks to make sure that the textbook is still in the database.
        $this->assertDatabaseHas('textbooks', [
            'id' => $textbook->id,
            'title' => $textbook->title,
            'description' => $textbook->description,
        ]);
    }
}
