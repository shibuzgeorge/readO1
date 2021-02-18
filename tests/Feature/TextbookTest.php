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

}
