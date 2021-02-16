<?php

namespace Tests\Feature;

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
     * A test to create a textbook authorised by logging in as an Admin and Module Tutor
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
            'file' =>  new UploadedFile(public_path('example.pdf'), 'example.pdf', 'application/pdf', null,  true),
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
            'file' => base64_encode(file_get_contents(public_path('example.pdf')))
        ]);

        $this->assertDatabaseHas('module_textbook', [
            'module_id' => $randomModule->id,
            'textbook_id' => Textbook::where('title', 'Testing create a new textbook as admin')->first()->id,
        ]);

        //Module Tutor textbook upload
        $ModuleTutorTextbook = [
            'title' => 'Testing create a new textbook as module tutor',
            'description' => 'This is a test for create a new textbook as module tutor',
            'file' =>  new UploadedFile(public_path('example.pdf'), 'example.pdf', 'application/pdf', null,  true),
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
            'file' => base64_encode(file_get_contents(public_path('example.pdf')))
        ]);

        $this->assertDatabaseHas('module_textbook', [
            'module_id' => $this->moduleTutorUser->modules()->first()->id,
            'textbook_id' => Textbook::where('title', 'Testing create a new textbook as module tutor')->first()->id,
        ]);
    }

}
