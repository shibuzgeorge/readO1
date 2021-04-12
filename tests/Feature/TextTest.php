<?php

namespace Tests\Feature;

use App\ExtensiveReadingCategory;
use App\ReadingSession;
use App\Text;
use App\Textbook;
use App\Module;
use App\User;
use App\YearGroup;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Spatie\PdfToText;
use Illuminate\Support\Facades\File;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class TextTest extends TestCase
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
        $this->withoutMiddleware(ThrottleRequests::class);
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
     * A test to check data for create page as a Admin
     *
     * @test
     * @return void
     */
      public function test_create_page_admin_authorised()
      {
          $yearGroup = YearGroup::has('module.textbooks')->get();
          $extensiveReadingCategories = ExtensiveReadingCategory::has('textbooks')->with('textbooks')->get();

          $this->actingAs($this->adminUser)
            ->getJson('/api/text/create')
            ->assertJson([
                'yearGroup' => $yearGroup->toArray(),
                'extensiveReadingCategories' => $extensiveReadingCategories->toArray()
            ]);
      }

    /**
     * A test to check data for create page as a Module Tutor
     *
     * @test
     * @return void
     */
    public function test_create_page_module_tutor_authorised()
    {
        $module_ids = $this->moduleTutorUser->modules()->get()->pluck('id');

        $yearGroup = YearGroup::has('module.textbooks')->
           whereHas('module', function ($query) use ($module_ids){
                $query->whereIn('modules.id', $module_ids);
            })->get();

        $extensiveReadingCategories = ExtensiveReadingCategory::has('textbooks')->with('textbooks')->get();

        $this->actingAs($this->moduleTutorUser)
            ->getJson('/api/text/create')
            ->assertJson([
                'yearGroup' => $yearGroup->toArray(),
                'extensiveReadingCategories' => $extensiveReadingCategories->toArray()
            ]);
    }

    /**
     * A test to check data for create page unauthorised as a Student
     *
     * @test
     * @return void
     */
    public function test_create_page_student_unauthorised()
    {
        $this->actingAs($this->studentUser)
            ->getJson('/api/text/create')
            ->assertJsonFragment(["error" => "Unauthorized"])
            ->assertStatus(403);
    }

    /**
     * A test to store a text with no title as Admin and Module Tutor
     *
     * @test
     * @return void
     */
    public function test_store_text_no_title()
    {

        $randomTextbook = Textbook::inRandomOrder()->first();

        $text = [
            'title' => '',
            'description' => 'This is a test for create a new text with no title as admin',
            'file' =>  new UploadedFile(public_path('/readingMaterial/example.pdf'), 'example.pdf', 'application/pdf', null,  true),
            'section' => 'moduleTextbook',
            'selected' => $randomTextbook->toJson()
        ];

        $this->actingAs($this->adminUser)
        ->postJson('/api/text', $text)
            ->assertJson([
                "errors" => [
                    "title" => ["The title field is required."]],
                "message" => "The given data was invalid."
            ])
            ->assertStatus(422);

        $this->assertDatabaseMissing('texts', [
            'title' => $text['title'],
            'description' => $text['description'],
        ]);

        $randomTextbook =  Module::has('textbooks.texts')->whereHas('users', function ($query){
            $query->where('user_id', $this->moduleTutorUser->id);
        })->first()->textbooks()->first();

        $text = [
            'title' => '',
            'description' => 'This is a test for create a new text with no title as module tutor',
            'file' =>  new UploadedFile(public_path('/readingMaterial/example.pdf'), 'example.pdf', 'application/pdf', null,  true),
            'section' => 'moduleTextbook',
            'selected' => $randomTextbook->toJson()
        ];

        $this->actingAs($this->moduleTutorUser)
            ->postJson('/api/text', $text)
            ->assertJson([
                "errors" => [
                    "title" => ["The title field is required."]],
                "message" => "The given data was invalid."
            ])
            ->assertStatus(422);

        $this->assertDatabaseMissing('texts', [
            'title' => $text['title'],
            'description' => $text['description'],
        ]);

    }

    /**
     * A test to store a text with no description as Admin and Module Tutor
     *
     * @test
     * @return void
     */
    public function test_store_text_no_description()
    {
        $randomTextbook = Textbook::inRandomOrder()->first();

        $text = [
            'title' => 'This is a test for create a new text with no description as admin',
            'description' => '',
            'file' =>  new UploadedFile(public_path('/readingMaterial/example.pdf'), 'example.pdf', 'application/pdf', null,  true),
            'section' => 'moduleTextbook',
            'selected' => $randomTextbook->toJson()
        ];

        $this->actingAs($this->adminUser)
            ->postJson('/api/text', $text)
            ->assertJson([
                "errors" => [
                    "description" => ["The description field is required."]],
                "message" => "The given data was invalid."
            ])
            ->assertStatus(422);

        $this->assertDatabaseMissing('texts', [
            'title' => $text['title'],
            'description' => $text['description'],
        ]);

        $randomTextbook =  Module::has('textbooks.texts')->whereHas('users', function ($query){
            $query->where('user_id', $this->moduleTutorUser->id);
        })->first()->textbooks()->first();

        $text = [
            'title' => 'This is a test for create a new text with no description as module tutor',
            'description' => '',
            'file' =>  new UploadedFile(public_path('/readingMaterial/example.pdf'), 'example.pdf', 'application/pdf', null,  true),
            'section' => 'moduleTextbook',
            'selected' => $randomTextbook->toJson()
        ];

        $this->actingAs($this->moduleTutorUser)
            ->postJson('/api/text', $text)
            ->assertJson([
                "errors" => [
                    "description" => ["The description field is required."]],
                "message" => "The given data was invalid."
            ])
            ->assertStatus(422);

        $this->assertDatabaseMissing('texts', [
            'title' => $text['title'],
            'description' => $text['description'],
        ]);

    }

    /**
     * A test to store a text with no textbook selected as Admin and Module Tutor
     *
     * @test
     * @return void
     */
    public function test_store_text_no_selected_textbook()
    {
        $text = [
            'title' => 'This is a test for create a new text with no textbook selected as admin',
            'description' => 'This is a test for create a new text with no textbook selected as admin',
            'file' =>  new UploadedFile(public_path('/readingMaterial/example.pdf'), 'example.pdf', 'application/pdf', null,  true),
            'section' => 'moduleTextbook',
            'selected' => ''
        ];

        $this->actingAs($this->adminUser)
            ->postJson('/api/text', $text)
            ->assertJson([
                "errors" => [
                    "selected" => ["You have to choose a textbook to upload a text into!"]],
                "message" => "The given data was invalid."
            ])
            ->assertStatus(422);

        $this->assertDatabaseMissing('texts', [
            'title' => $text['title'],
            'description' => $text['description'],
        ]);

        $text = [
            'title' => 'This is a test for create a new text with no textbook selected as admin',
            'description' => 'This is a test for create a new text with no textbook selected as admin',
            'file' =>  new UploadedFile(public_path('/readingMaterial/example.pdf'), 'example.pdf', 'application/pdf', null,  true),
            'section' => 'moduleTextbook',
            'selected' => '[]'
        ];

        $this->actingAs($this->adminUser)
            ->postJson('/api/text', $text)
            ->assertJson([
                "errors" => [
                    "selected" => ["You have to choose a textbook to upload a text into!"]],
                "message" => "The given data was invalid."
            ])
            ->assertStatus(422);

        $this->assertDatabaseMissing('texts', [
            'title' => $text['title'],
            'description' => $text['description'],
        ]);

        //Module Tutor

        $text = [
            'title' => 'This is a test for create a new text with no textbook selected as module tutor',
            'description' => 'This is a test for create a new text no textbook selected as module tutor',
            'file' =>  new UploadedFile(public_path('/readingMaterial/example.pdf'), 'example.pdf', 'application/pdf', null,  true),
            'section' => 'moduleTextbook',
            'selected' => ''
        ];

        $this->actingAs($this->moduleTutorUser)
            ->postJson('/api/text', $text)
            ->assertJson([
                "errors" => [
                    "selected" => ["You have to choose a textbook to upload a text into!"]],
                "message" => "The given data was invalid."
            ])
            ->assertStatus(422);

        $this->assertDatabaseMissing('texts', [
            'title' => $text['title'],
            'description' => $text['description'],
        ]);

        $text = [
            'title' => 'This is a test for create a new text with no textbook selected as module tutor',
            'description' => 'This is a test for create a new text no textbook selected as module tutor',
            'file' =>  new UploadedFile(public_path('/readingMaterial/example.pdf'), 'example.pdf', 'application/pdf', null,  true),
            'section' => 'moduleTextbook',
            'selected' => '[]'
        ];

        $this->actingAs($this->moduleTutorUser)
            ->postJson('/api/text', $text)
            ->assertJson([
                "errors" => [
                    "selected" => ["You have to choose a textbook to upload a text into!"]],
                "message" => "The given data was invalid."
            ])
            ->assertStatus(422);

        $this->assertDatabaseMissing('texts', [
            'title' => $text['title'],
            'description' => $text['description'],
        ]);

    }

    /**
     * A test to store a text with no section as Admin and Module Tutor
     *
     * @test
     * @return void
     */
    public function test_store_text_no_section_textbook()
    {
        $randomTextbook = Textbook::inRandomOrder()->first();

        $text = [
            'title' => 'This is a test for create a new text with no section as admin',
            'description' => 'This is a test for create a new text with no section as admin',
            'file' =>  new UploadedFile(public_path('/readingMaterial/example.pdf'), 'example.pdf', 'application/pdf', null,  true),
            'section' => '',
            'selected' => $randomTextbook->toJson()
        ];

        $this->actingAs($this->adminUser)
            ->postJson('/api/text', $text)
            ->assertJson([
                "errors" => [
                    "section" => ["The section field is required."]],
                "message" => "The given data was invalid."
            ])
            ->assertStatus(422);

        $this->assertDatabaseMissing('texts', [
            'title' => $text['title'],
            'description' => $text['description'],
        ]);

        //Module Tutor
        $randomTextbook =  Module::has('textbooks.texts')->whereHas('users', function ($query){
            $query->where('user_id', $this->moduleTutorUser->id);
        })->first()->textbooks()->first();

        $text = [
            'title' => 'This is a test for create a new text with no section as module tutor',
            'description' => 'This is a test for create a new text no section as module tutor',
            'file' =>  new UploadedFile(public_path('/readingMaterial/example.pdf'), 'example.pdf', 'application/pdf', null,  true),
            'section' => '',
            'selected' => $randomTextbook->toJson()
        ];

        $this->actingAs($this->moduleTutorUser)
            ->postJson('/api/text', $text)
            ->assertJson([
                "errors" => [
                    "section" => ["The section field is required."]],
                "message" => "The given data was invalid."
            ])
            ->assertStatus(422);

        $this->assertDatabaseMissing('texts', [
            'title' => $text['title'],
            'description' => $text['description'],
        ]);
    }

    /**
     * A test to store a text with no file upload as Admin and Module Tutor
     *
     * @test
     * @return void
     */
    public function test_store_text_no_file_textbook()
    {
        $randomTextbook = Textbook::inRandomOrder()->first();

        $text = [
            'title' => 'This is a test for create a new text with no file as admin',
            'description' => 'This is a test for create a new text with no file as admin',
            'file' => '',
            'section' => 'moduleTexbook',
            'selected' => $randomTextbook->toJson()
        ];

        $this->actingAs($this->adminUser)
            ->postJson('/api/text', $text)
            ->assertJson([
                "errors" => [
                    "file" => ["The file field is required."]],
                "message" => "The given data was invalid."
            ])
            ->assertStatus(422);

        $this->assertDatabaseMissing('texts', [
            'title' => $text['title'],
            'description' => $text['description'],
        ]);

        //Module Tutor
        $randomTextbook =  Module::has('textbooks.texts')->whereHas('users', function ($query){
            $query->where('user_id', $this->moduleTutorUser->id);
        })->first()->textbooks()->first();

        $text = [
            'title' => 'This is a test for create a new text with no file as module tutor',
            'description' => 'This is a test for create a new text no file as module tutor',
            'file' =>  '',
            'section' => 'moduleTextbook',
            'selected' => $randomTextbook->toJson()
        ];

        $this->actingAs($this->moduleTutorUser)
            ->postJson('/api/text', $text)
            ->assertJson([
                "errors" => [
                    "file" => ["The file field is required."]],
                "message" => "The given data was invalid."
            ])
            ->assertStatus(422);

        $this->assertDatabaseMissing('texts', [
            'title' => $text['title'],
            'description' => $text['description'],
        ]);
    }

    /**
     * A test to store a text with invalid file upload (Not PDF) as Admin and Module Tutor
     *
     * @test
     * @return void
     */
    public function test_store_text__file_invalid_textbook()
    {
        $randomTextbook = Textbook::inRandomOrder()->first();

        $text = [
            'title' => 'This is a test for create a new text with invalid file as admin',
            'description' => 'This is a test for create a new text invalid file as admin',
            'file' => new UploadedFile(public_path('/readingMaterial/thumbnail.png'), 'thumbnail.png', 'image/png', null,  true),
            'section' => 'moduleTexbook',
            'selected' => $randomTextbook->toJson()
        ];

        $this->actingAs($this->adminUser)
            ->postJson('/api/text', $text)
            ->assertJson([
                "errors" => [
                    "file" => ["The file must be a file of type: pdf."]],
                "message" => "The given data was invalid."
            ])
            ->assertStatus(422);

        $this->assertDatabaseMissing('texts', [
            'title' => $text['title'],
            'description' => $text['description'],
        ]);

        //Module Tutor
        $randomTextbook =  Module::has('textbooks.texts')->whereHas('users', function ($query){
            $query->where('user_id', $this->moduleTutorUser->id);
        })->first()->textbooks()->first();

        $text = [
            'title' => 'This is a test for create a new text with file invalid as module tutor',
            'description' => 'This is a test for create a new text file invalid as module tutor',
            'file' =>  new UploadedFile(public_path('/readingMaterial/thumbnail.png'), 'thumbnail.png', 'image/png', null,  true),
            'section' => 'moduleTextbook',
            'selected' => $randomTextbook->toJson()
        ];

        $this->actingAs($this->moduleTutorUser)
            ->postJson('/api/text', $text)
            ->assertJson([
                "errors" => [
                    "file" => ["The file must be a file of type: pdf."]],
                "message" => "The given data was invalid."
            ])
            ->assertStatus(422);

        $this->assertDatabaseMissing('texts', [
            'title' => $text['title'],
            'description' => $text['description'],
        ]);
    }

    /**
     * A test to store a module text as an Admin authorised and valid.
     *
     * @test
     * @return void
     */
    public function test_store_text_as_admin_module_text_all_valid()
    {
        $randomModuleTextbook = Textbook::has('modules')->inRandomOrder()->first();

        $AdminModuleTextbookText = [
            'title' => 'Testing create a new text for a module textbook admin',
            'description' => 'This is a test for create a new text for a module textbook as admin',
            'file' =>  new UploadedFile(public_path('/readingMaterial/example.pdf'), 'example.pdf', 'application/pdf', null,  true),
            'section' => 'moduleTextbook',
            'selected' => $randomModuleTextbook->toJson()
        ];
        $this->actingAs($this->adminUser)
            ->postJson('/api/text', $AdminModuleTextbookText)
            ->assertSuccessful();;

        $this->assertDatabaseHas('texts', [
            'title' => $AdminModuleTextbookText['title'],
            'description' => $AdminModuleTextbookText['description'],
            'file' => base64_encode(file_get_contents(public_path('/readingMaterial/example.pdf'))),
            'textbook_id' => $randomModuleTextbook->id,
        ]);
    }

    /**
     * A test to store a extensive reading text as an Admin authorised and valid.
     *
     * @test
     * @return void
     */
    public function test_store_text_as_admin_extensive_reading_text_all_valid()
    {
        $randomExtensiveReadingTextbook = Textbook::has('extensiveReadingCategories')->inRandomOrder()->first();

        $AdminExtensiveReadingTextbookText = [
            'title' => 'Testing create a new text for a extensive reading textbook admin',
            'description' => 'This is a test for create a new text for a extensive reading textbook as admin',
            'file' =>  new UploadedFile(public_path('/readingMaterial/example.pdf'), 'example.pdf', 'application/pdf', null,  true),
            'section' => 'extensiveReadingTextbook',
            'selected' => $randomExtensiveReadingTextbook->toJson()
        ];
        $this->actingAs($this->adminUser)
            ->postJson('/api/text', $AdminExtensiveReadingTextbookText)
            ->assertSuccessful();;

        $this->assertDatabaseHas('texts', [
            'title' => $AdminExtensiveReadingTextbookText['title'],
            'description' => $AdminExtensiveReadingTextbookText['description'],
            'file' => base64_encode(file_get_contents(public_path('/readingMaterial/example.pdf'))),
            'textbook_id' => $randomExtensiveReadingTextbook->id,
        ]);
    }

    /**
     * A test to store a module text as an module tutor authorised and valid.
     *
     * @test
     * @return void
     */
    public function test_store_text_as_module_tutor_module_text_all_valid()
    {
        $randomModuleTextbook = Module::has('textbooks.texts')->whereHas('users', function ($query){
            $query->where('user_id', $this->moduleTutorUser->id);
        })->first()->textbooks()->first();

        $Module_Tutor_ModuleTextbookText = [
            'title' => 'Testing create a new text for a module textbook module tutor',
            'description' => 'This is a test for create a new text for a module textbook as module tutor',
            'file' =>  new UploadedFile(public_path('/readingMaterial/example.pdf'), 'example.pdf', 'application/pdf', null,  true),
            'section' => 'moduleTextbook',
            'selected' => $randomModuleTextbook->toJson()
        ];
        $this->actingAs($this->moduleTutorUser)
            ->postJson('/api/text', $Module_Tutor_ModuleTextbookText)
            ->assertSuccessful();;

        $this->assertDatabaseHas('texts', [
            'title' => $Module_Tutor_ModuleTextbookText['title'],
            'description' => $Module_Tutor_ModuleTextbookText['description'],
            'file' => base64_encode(file_get_contents(public_path('/readingMaterial/example.pdf'))),
            'textbook_id' => $randomModuleTextbook->id,
        ]);
    }

    /**
     * A test to store a extensive reading text as an module tutor authorised and valid.
     *
     * @test
     * @return void
     */
    public function test_store_text_as_module_tutor_extensive_reading_text_all_valid()
    {
        $randomExtensiveReadingTextbook = Textbook::has('extensiveReadingCategories')->inRandomOrder()->first();

        $Module_Tutor_ExtensiveReadingTextbookText = [
            'title' => 'Testing create a new text for a extensive reading textbook module tutor',
            'description' => 'This is a test for create a new text for a extensive reading textbook as module tutor',
            'file' =>  new UploadedFile(public_path('/readingMaterial/example.pdf'), 'example.pdf', 'application/pdf', null,  true),
            'section' => 'extensiveReadingTextbook',
            'selected' => $randomExtensiveReadingTextbook->toJson()
        ];
        $this->actingAs($this->moduleTutorUser)
            ->postJson('/api/text', $Module_Tutor_ExtensiveReadingTextbookText)
            ->assertSuccessful();

        $this->assertDatabaseHas('texts', [
            'title' => $Module_Tutor_ExtensiveReadingTextbookText['title'],
            'description' => $Module_Tutor_ExtensiveReadingTextbookText['description'],
            'file' => base64_encode(file_get_contents(public_path('/readingMaterial/example.pdf'))),
            'textbook_id' => $randomExtensiveReadingTextbook->id,
        ]);
    }

    /**
     * A test to check data for text show if id not found
     *
     * @test
     * @return void
     */
    public function test_show_a_text_id_not_found_authorised()
    {
        $this->actingAs($this->adminUser)
            ->getJson('/api/text/'.rand(9999, 10000))
            ->assertJsonFragment(['Error' => 'Text not found!']);
    }

    /**
     * A test to check permission denied for text show if module tutor or student
     * views a text who has a textbook which has a module not assigned to them.
     *
     * @test
     * @return void
     */
    public function test_show_a_text_permission_denied()
    {
        $unAssignedModuleModuleTutorText = Module::has('textbooks.texts')->whereDoesntHave('users', function ($query){
            $query->where('user_id', $this->moduleTutorUser->id);
        })->first()->textbooks()->first()
            ->texts()->first();

        $this->actingAs($this->moduleTutorUser)
            ->getJson('/api/text/'.$unAssignedModuleModuleTutorText->id)
            ->assertJsonFragment(['Error' => 'Permission denied to view the text!']);

        $unAssignedModuleStudentText = Module::has('textbooks.texts')->whereDoesntHave('users', function ($query){
            $query->where('user_id', $this->studentUser->id);
        })->first()->textbooks()->first()
            ->texts()->first();

        $this->actingAs($this->studentUser)
            ->getJson('/api/text/'.$unAssignedModuleStudentText->id)
            ->assertJsonFragment(['Error' => 'Permission denied to view the text!']);
    }

    /**
     * A test to check data for viewing a text as a Admin
     *
     * @test
     * @return void
     */
    public function test_show_a_text_admin_authorised()
    {
        $text = Text::inRandomOrder()->first();

        $decoded = base64_decode($text->file);
        file_put_contents(public_path('file.pdf'), $decoded);

        //Checks operating system if it is windows or not
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $pdftext = base64_encode(PdfToText\Pdf::getText(public_path('file.pdf'), public_path('pdftotext/pdftotext.exe')));
        } else {
            $pdftext = base64_encode(PdfToText\Pdf::getText(public_path('file.pdf')));
        }
        File::delete('file.pdf');

        $this->actingAs($this->adminUser)
            ->getJson('/api/text/'.$text->id)
            ->assertSuccessful()
            ->assertJson([
                'title' => $text->title,
                'description' => $text->description,
                'textbook' => $text->textbook()->first()->toArray(),
                'text' => $pdftext
            ]);
    }

    /**
     * A test to check data for viewing a text as a Module Tutor
     *
     * @test
     * @return void
     */
    public function test_show_a_text_module_tutor_authorised()
    {
        $text = Module::has('textbooks.texts')->whereHas('users', function ($query){
            $query->where('user_id', $this->moduleTutorUser->id);
        })->first()->textbooks()->first()
            ->texts()->first();

        $decoded = base64_decode($text->file);
        file_put_contents(public_path('file.pdf'), $decoded);

        //Checks operating system if it is windows or not
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $pdftext = base64_encode(PdfToText\Pdf::getText(public_path('file.pdf'), public_path('pdftotext/pdftotext.exe')));
        } else {
            $pdftext = base64_encode(PdfToText\Pdf::getText(public_path('file.pdf')));
        }
        File::delete('file.pdf');

        $this->actingAs($this->moduleTutorUser)
            ->getJson('/api/text/'.$text->id)
            ->assertSuccessful()
            ->assertJson([
                'title' => $text->title,
                'description' => $text->description,
                'textbook' => $text->textbook()->first()->toArray(),
                'text' => $pdftext
            ]);
    }

    /**
     * A test to check data for viewing a text as a Student
     *
     * @test
     * @return void
     */
    public function test_show_a_text_student_authorised()
    {
        $text = Module::has('textbooks.texts')->whereHas('users', function ($query){
            $query->where('user_id', $this->studentUser->id);
        })->first()->textbooks()->first()
            ->texts()->first();

        $decoded = base64_decode($text->file);
        file_put_contents(public_path('file.pdf'), $decoded);

        //Checks operating system if it is windows or not
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $pdftext = base64_encode(PdfToText\Pdf::getText(public_path('file.pdf'), public_path('pdftotext/pdftotext.exe')));
        } else {
            $pdftext = base64_encode(PdfToText\Pdf::getText(public_path('file.pdf')));
        }
        File::delete('file.pdf');

        $this->actingAs($this->studentUser)
            ->getJson('/api/text/'.$text->id)
            ->assertSuccessful()
            ->assertJson([
                'title' => $text->title,
                'description' => $text->description,
                'textbook' => $text->textbook()->first()->toArray(),
                'text' => $pdftext
            ]);
    }

    /**
     *
     * A test to check if an Admin can download pdf of text
     *
     */
    public function test_pdf_download_admin_authorised()
    {
        $text = Text::inRandomOrder()->first();

        $file_contents = base64_decode($text->file);

        $this->actingAs($this->adminUser)
            ->getJson('/api/text/pdf/'.$text->id)
            ->assertHeader('Cache-Control', 'no-cache private, private')
            ->assertHeader('Content-Description', 'File Transfer')
            ->assertHeader('Content-Type', 'application/x-pdf')
            ->assertHeader('Content-Length', strlen($file_contents))
            ->assertHeader('Content-Disposition', 'inline; filename="example.pdf"')
            ->assertHeader('Content-Transfer-Encoding', 'binary');

    }

    /**
     *
     * A test to check if a module tutor can download pdf of text
     *
     */
    public function test_pdf_download_module_tutor_authorised()
    {
        $text = Module::has('textbooks.texts')->whereHas('users', function ($query){
            $query->where('user_id', $this->moduleTutorUser->id);
        })->first()->textbooks()->first()
            ->texts()->first();

        $file_contents = base64_decode($text->file);

        $this->actingAs($this->moduleTutorUser)
            ->getJson('/api/text/pdf/'.$text->id)
            ->assertHeader('Cache-Control', 'no-cache private, private')
            ->assertHeader('Content-Description', 'File Transfer')
            ->assertHeader('Content-Type', 'application/x-pdf')
            ->assertHeader('Content-Length', strlen($file_contents))
            ->assertHeader('Content-Disposition', 'inline; filename="example.pdf"')
            ->assertHeader('Content-Transfer-Encoding', 'binary');

    }

    /**
     *
     * A test to check if a student can download pdf of text
     *
     */
    public function test_pdf_download_student_authorised()
    {
        $text = Module::has('textbooks.texts')->whereHas('users', function ($query){
            $query->where('user_id', $this->studentUser->id);
        })->first()->textbooks()->first()
            ->texts()->first();

        $file_contents = base64_decode($text->file);

        $this->actingAs($this->studentUser)
            ->getJson('/api/text/pdf/'.$text->id)
            ->assertHeader('Cache-Control', 'no-cache private, private')
            ->assertHeader('Content-Description', 'File Transfer')
            ->assertHeader('Content-Type', 'application/x-pdf')
            ->assertHeader('Content-Length', strlen($file_contents))
            ->assertHeader('Content-Disposition', 'inline; filename="example.pdf"')
            ->assertHeader('Content-Transfer-Encoding', 'binary');

    }

    /**
     *
     * A test to permission denied to download pdf
     *
     */
    public function test_pdf_download_permission_denied()
    {
        $textModuleTutorDenied = Module::has('textbooks.texts')->whereDoesntHave('users', function ($query){
            $query->where('user_id', $this->studentUser->id);
        })->first()->textbooks()->first()
            ->texts()->first();

        $this->actingAs($this->moduleTutorUser)
            ->getJson('/api/text/pdf/'.$textModuleTutorDenied->id)
            ->assertJsonFragment(['Error' => 'Permission denied to download the text!']);

        $textStudentDenied = Module::has('textbooks.texts')->whereDoesntHave('users', function ($query){
            $query->where('user_id', $this->studentUser->id);
        })->first()->textbooks()->first()
            ->texts()->first();

        $this->actingAs($this->studentUser)
            ->getJson('/api/text/pdf/'.$textStudentDenied->id)
            ->assertJsonFragment(['Error' => 'Permission denied to download the text!']);

    }

    /**
     *
     * A test to check non found id for pdf download for a text
     *
     */
    public function test_pdf_download_text_id_not_found()
    {
        $this->actingAs($this->adminUser)
            ->getJson('/api/text/pdf/'.rand(9999,10000))
            ->assertJsonFragment(['Error' => 'Text not found!']);

        $this->actingAs($this->moduleTutorUser)
            ->getJson('/api/text/pdf/'.rand(9999,10000))
            ->assertJsonFragment(['Error' => 'Text not found!']);

        $this->actingAs($this->studentUser)
            ->getJson('/api/text/pdf/'.rand(9999,10000))
            ->assertJsonFragment(['Error' => 'Text not found!']);


    }

    /**
     * A test to check data for edit page for a module text as a Admin
     *
     * @test
     * @return void
     */
    public function test_edit_page_admin_with_module_text_authorised()
    {
        $text = Text::has('textbook.modules')->inRandomOrder()->first();
        $textbook = Textbook::find($text->textbook_id);
        $yearGroup = YearGroup::has('module.textbooks')->get();
        $extensiveReadingCategories = ExtensiveReadingCategory::has('textbooks')->with('textbooks')->get();
        $year_group = $textbook->modules()->first()->yearGroup()->first();

        $this->actingAs($this->adminUser)
            ->getJson('/api/text/'.$text->id.'/edit')
            ->assertJson([
                'title' => $text->title,
                'description' => $text->description,
                'selected' => $textbook->toArray(),
                'section' => 'moduleTextbook',
                'year_group' => $year_group->toArray(),
                'selectedModule' => $textbook->modules()->with('textbooks')->first()->toArray(),
                'textbook' => $textbook->toArray(),
                'yearGroup' => $yearGroup->toArray(),
                'extensiveReadingCategories' => $extensiveReadingCategories->toArray()
            ]);
    }

    /**
     * A test to check data for edit page for a extensive reading text as a Admin
     *
     * @test
     * @return void
     */
    public function test_edit_page_admin_with_extensive_reading_text_authorised()
    {
        $text = Text::has('textbook.extensiveReadingCategories')->inRandomOrder()->first();
        $textbook = Textbook::find($text->textbook_id);
        $yearGroup = YearGroup::has('module.textbooks')->get();
        $extensiveReadingCategories = ExtensiveReadingCategory::has('textbooks')->with('textbooks')->get();

        $this->actingAs($this->adminUser)
            ->getJson('/api/text/'.$text->id.'/edit')
            ->assertJson([
                'title' => $text->title,
                'description' => $text->description,
                'selected' => $textbook->toArray(),
                'section' => 'extensiveReadingTextbook',
                'selectedExtensiveReadingCategory' => $textbook->extensiveReadingCategories()->with('textbooks')->first()->toArray(),
                'textbook' => $textbook->toArray(),
                'yearGroup' => $yearGroup->toArray(),
                'extensiveReadingCategories' => $extensiveReadingCategories->toArray()
            ]);
    }

    /**
     * A test to check data for edit page for a module text as a Module Tutor
     *
     * @test
     * @return void
     */
    public function test_edit_page_module_tutor_with_module_text_authorised()
    {
        $text = Module::has('textbooks.texts')->whereHas('users', function ($query){
            $query->where('user_id', $this->moduleTutorUser->id);
        })->first()->textbooks()->first()
            ->texts()->first();
        $textbook = Textbook::find($text->textbook_id);

        $module_ids = $this->moduleTutorUser->modules()->get()->pluck('id');
        $yearGroup = YearGroup::has('module.textbooks')->
        whereHas('module', function ($query) use ($module_ids){
            $query->whereIn('modules.id', $module_ids);
        })->get();

        $extensiveReadingCategories = ExtensiveReadingCategory::has('textbooks')->with('textbooks')->get();
        $year_group = $textbook->modules()->first()->yearGroup()->first();

        $this->actingAs($this->moduleTutorUser)
            ->getJson('/api/text/'.$text->id.'/edit')
            ->assertJson([
                'title' => $text->title,
                'description' => $text->description,
                'selected' => $textbook->toArray(),
                'section' => 'moduleTextbook',
                'year_group' => $year_group->toArray(),
                'selectedModule' => $textbook->modules()->with('textbooks')->first()->toArray(),
                'textbook' => $textbook->toArray(),
                'yearGroup' => $yearGroup->toArray(),
                'extensiveReadingCategories' => $extensiveReadingCategories->toArray()
            ]);
    }

    /**
     * A test to check data for edit page for a extensive reading text as a Module Tutor
     *
     * @test
     * @return void
     */
    public function test_edit_page_module_tutor_with_extensive_reading_text_authorised()
    {
        $text = Text::has('textbook.extensiveReadingCategories')->inRandomOrder()->first();
        $textbook = Textbook::find($text->textbook_id);

        $module_ids = $this->moduleTutorUser->modules()->get()->pluck('id');
        $yearGroup = YearGroup::has('module.textbooks')->
        whereHas('module', function ($query) use ($module_ids){
            $query->whereIn('modules.id', $module_ids);
        })->get();

        $extensiveReadingCategories = ExtensiveReadingCategory::has('textbooks')->with('textbooks')->get();

        $this->actingAs($this->moduleTutorUser)
            ->getJson('/api/text/'.$text->id.'/edit')
            ->assertJson([
                'title' => $text->title,
                'description' => $text->description,
                'selected' => $textbook->toArray(),
                'section' => 'extensiveReadingTextbook',
                'selectedExtensiveReadingCategory' => $textbook->extensiveReadingCategories()->with('textbooks')->first()->toArray(),
                'textbook' => $textbook->toArray(),
                'yearGroup' => $yearGroup->toArray(),
                'extensiveReadingCategories' => $extensiveReadingCategories->toArray()
            ]);
    }

    /**
     * A test to check data for edit page unauthorised by logging in as a student.
     *
     * @test
     * @return void
     */
    public function test_edit_page_student_unauthorised()
    {
        $text = Text::inRandomOrder()->first();

        $this->actingAs($this->studentUser)
            ->getJson('/api/text/'.$text->id.'/edit')
            ->assertJsonFragment(["error" => "Unauthorized"])
            ->assertStatus(403);

    }

    /**
     * A test to check data for edit page permission denied for Module Tutor
     * by trying to edit a text which a module tutor has no access because the module
     * is not assigned to them
     *
     * @test
     * @return void
     */
    public function test_edit_page_permission_denied_module_tutor_unauthorised()
    {
        $unAssignedModuleModuleTutorText = Module::has('textbooks.texts')->whereDoesntHave('users', function ($query){
            $query->where('user_id', $this->moduleTutorUser->id);
        })->first()->textbooks()->first()
            ->texts()->first();

        $this->actingAs($this->moduleTutorUser)
            ->getJson('/api/text/'.$unAssignedModuleModuleTutorText->id.'/edit')
            ->assertJson(['Error' => 'Permission denied to edit the text!']);
    }

    /**
     * A test to check data for a text id not found.
     *
     * @test
     * @return void
     */
    public function test_edit_page_text_id_not_found_unauthorised()
    {
        $this->actingAs($this->adminUser)
            ->getJson('/api/text/'.rand(9999, 10000).'/edit')
            ->assertJson(['Error' => 'Textbook not found!']);

        $this->actingAs($this->moduleTutorUser)
            ->getJson('/api/text/'.rand(9999, 10000).'/edit')
            ->assertJson(['Error' => 'Textbook not found!']);
    }

    //Update

    /**
     * A test to update a text with no title as Admin and Module Tutor
     *
     * @test
     * @return void
     */
    public function test_update_text_no_title()
    {
        //Admin
        $randomText = Text::inRandomOrder()->first();

        $textbook = Textbook::find($randomText->textbook_id);

        $text = [
            'title' => '',
            'description' => 'This is a test for update a text with no title as admin',
            'file' =>  new UploadedFile(public_path('/readingMaterial/example.pdf'), 'example.pdf', 'application/pdf', null,  true),
            'section' => 'moduleTextbook',
            'selected' => $textbook->toJson()
        ];

        $this->actingAs($this->adminUser)
            ->patchJson('/api/text/'.$randomText->id, $text)
            ->assertJson([
                "errors" => [
                    "title" => ["The title field is required."]],
                "message" => "The given data was invalid."
            ])
            ->assertStatus(422);

        $this->assertDatabaseMissing('texts', [
            'title' => $text['title'],
            'description' => $text['description'],
        ]);

        $randomText =  Module::has('textbooks.texts')->whereHas('users', function ($query){
            $query->where('user_id', $this->moduleTutorUser->id);
        })->first()->textbooks()->first()->texts()->first();

        $textbook = Textbook::find($randomText->textbook_id);

        $text = [
            'title' => '',
            'description' => 'This is a test for update a text with no title as module tutor',
            'file' =>  new UploadedFile(public_path('/readingMaterial/example.pdf'), 'example.pdf', 'application/pdf', null,  true),
            'section' => 'moduleTextbook',
            'selected' => $textbook->toJson()
        ];

        $this->actingAs($this->moduleTutorUser)
            ->patchJson('/api/text/'.$randomText->id, $text)
            ->assertJson([
                "errors" => [
                    "title" => ["The title field is required."]],
                "message" => "The given data was invalid."
            ])
            ->assertStatus(422);

        $this->assertDatabaseMissing('texts', [
            'title' => $text['title'],
            'description' => $text['description'],
        ]);

    }

    /**
     * A test to update a text with no description as Admin and Module Tutor
     *
     * @test
     * @return void
     */
    public function test_update_text_no_description()
    {
        //Admin
        $randomText = Text::inRandomOrder()->first();

        $textbook = Textbook::find($randomText->textbook_id);

        $text = [
            'title' => 'This is a test for update a text with no description as admin',
            'description' => '',
            'file' =>  new UploadedFile(public_path('/readingMaterial/example.pdf'), 'example.pdf', 'application/pdf', null,  true),
            'section' => 'moduleTextbook',
            'selected' => $textbook->toJson()
        ];

        $this->actingAs($this->adminUser)
            ->patchJson('/api/text/'.$randomText->id, $text)
            ->assertJson([
                "errors" => [
                    "description" => ["The description field is required."]],
                "message" => "The given data was invalid."
            ])
            ->assertStatus(422);

        $this->assertDatabaseMissing('texts', [
            'title' => $text['title'],
            'description' => $text['description'],
        ]);

        $randomText =  Module::has('textbooks.texts')->whereHas('users', function ($query){
            $query->where('user_id', $this->moduleTutorUser->id);
        })->first()->textbooks()->first()->texts()->first();

        $textbook = Textbook::find($randomText->textbook_id);

        $text = [
            'title' => 'This is a test for update a text with no description as module tutor',
            'description' => '',
            'file' =>  new UploadedFile(public_path('/readingMaterial/example.pdf'), 'example.pdf', 'application/pdf', null,  true),
            'section' => 'moduleTextbook',
            'selected' => $textbook->toJson()
        ];

        $this->actingAs($this->moduleTutorUser)
            ->patchJson('/api/text/'.$randomText->id, $text)
            ->assertJson([
                "errors" => [
                    "description" => ["The description field is required."]],
                "message" => "The given data was invalid."
            ])
            ->assertStatus(422);

        $this->assertDatabaseMissing('texts', [
            'title' => $text['title'],
            'description' => $text['description'],
        ]);

    }

    /**
     * A test to update a text with no textbook selected as Admin and Module Tutor
     *
     * @test
     * @return void
     */
    public function test_update_text_no_selected_textbook()
    {
        $randomText = Text::inRandomOrder()->first();

        $text = [
            'title' => 'This is a test for update a text with no textbook selected as admin',
            'description' => 'This is a test for update a text with no textbook selected as admin',
            'file' =>  new UploadedFile(public_path('/readingMaterial/example.pdf'), 'example.pdf', 'application/pdf', null,  true),
            'section' => 'moduleTextbook',
            'selected' => ''
        ];

        $this->actingAs($this->adminUser)
            ->patchJson('/api/text/'.$randomText->id, $text)
            ->assertJson([
                "errors" => [
                    "selected" => ["You have to choose a textbook to upload a text into!"]],
                "message" => "The given data was invalid."
            ])
            ->assertStatus(422);

        $this->assertDatabaseMissing('texts', [
            'title' => $text['title'],
            'description' => $text['description'],
        ]);

        $text = [
            'title' => 'This is a test for update a text with no textbook selected as admin',
            'description' => 'This is a test for create a new text with no textbook selected as admin',
            'file' =>  new UploadedFile(public_path('/readingMaterial/example.pdf'), 'example.pdf', 'application/pdf', null,  true),
            'section' => 'moduleTextbook',
            'selected' => '[]'
        ];

        $this->actingAs($this->adminUser)
            ->patchJson('/api/text/'.$randomText->id, $text)
            ->assertJson([
                "errors" => [
                    "selected" => ["You have to choose a textbook to upload a text into!"]],
                "message" => "The given data was invalid."
            ])
            ->assertStatus(422);

        $this->assertDatabaseMissing('texts', [
            'title' => $text['title'],
            'description' => $text['description'],
        ]);

        //Module Tutor

        $randomText =  Module::has('textbooks.texts')->whereHas('users', function ($query){
            $query->where('user_id', $this->moduleTutorUser->id);
        })->first()->textbooks()->first()->texts()->first();

        $text = [
            'title' => 'This is a test for update a text with no textbook selected as module tutor',
            'description' => 'This is a test for update a text no textbook selected as module tutor',
            'file' =>  new UploadedFile(public_path('/readingMaterial/example.pdf'), 'example.pdf', 'application/pdf', null,  true),
            'section' => 'moduleTextbook',
            'selected' => ''
        ];

        $this->actingAs($this->moduleTutorUser)
            ->patchJson('/api/text/'.$randomText->id, $text)
            ->assertJson([
                "errors" => [
                    "selected" => ["You have to choose a textbook to upload a text into!"]],
                "message" => "The given data was invalid."
            ])
            ->assertStatus(422);

        $this->assertDatabaseMissing('texts', [
            'title' => $text['title'],
            'description' => $text['description'],
        ]);

        $text = [
            'title' => 'This is a test for create a new text with no textbook selected as module tutor',
            'description' => 'This is a test for create a new text no textbook selected as module tutor',
            'file' =>  new UploadedFile(public_path('/readingMaterial/example.pdf'), 'example.pdf', 'application/pdf', null,  true),
            'section' => 'moduleTextbook',
            'selected' => '[]'
        ];

        $this->actingAs($this->moduleTutorUser)
            ->patchJson('/api/text/'.$randomText->id, $text)
            ->assertJson([
                "errors" => [
                    "selected" => ["You have to choose a textbook to upload a text into!"]],
                "message" => "The given data was invalid."
            ])
            ->assertStatus(422);

        $this->assertDatabaseMissing('texts', [
            'title' => $text['title'],
            'description' => $text['description'],
        ]);

    }

    /**
     * A test to update a text with no section as Admin and Module Tutor
     *
     * @test
     * @return void
     */
    public function test_update_text_no_section_textbook()
    {
        $randomText = Text::inRandomOrder()->first();

        $textbook = Textbook::find($randomText->textbook_id);

        $text = [
            'title' => 'This is a test for update a text with no section as admin',
            'description' => 'This is a test for update a text with no section as admin',
            'file' =>  new UploadedFile(public_path('/readingMaterial/example.pdf'), 'example.pdf', 'application/pdf', null,  true),
            'section' => '',
            'selected' => $textbook->toJson()
        ];

        $this->actingAs($this->adminUser)
            ->patchJson('/api/text/'.$randomText->id, $text)
            ->assertJson([
                "errors" => [
                    "section" => ["The section field is required."]],
                "message" => "The given data was invalid."
            ])
            ->assertStatus(422);

        $this->assertDatabaseMissing('texts', [
            'title' => $text['title'],
            'description' => $text['description'],
        ]);

        //Module Tutor
        $randomText =  Module::has('textbooks.texts')->whereHas('users', function ($query){
            $query->where('user_id', $this->moduleTutorUser->id);
        })->first()->textbooks()->first()->texts()->first();

        $textbook = Textbook::find($randomText->textbook_id);

        $text = [
            'title' => 'This is a test for create a new text with no section as module tutor',
            'description' => 'This is a test for create a new text no section as module tutor',
            'file' =>  new UploadedFile(public_path('/readingMaterial/example.pdf'), 'example.pdf', 'application/pdf', null,  true),
            'section' => '',
            'selected' => $textbook->toJson()
        ];

        $this->actingAs($this->moduleTutorUser)
            ->patchJson('/api/text/'.$randomText->id, $text)
            ->assertJson([
                "errors" => [
                    "section" => ["The section field is required."]],
                "message" => "The given data was invalid."
            ])
            ->assertStatus(422);

        $this->assertDatabaseMissing('texts', [
            'title' => $text['title'],
            'description' => $text['description'],
        ]);
    }

    /**
     * A test to update a text with invalid file upload (Not PDF) as Admin and Module Tutor
     *
     * @test
     * @return void
     */
    public function test_update_text_file_invalid_textbook()
    {
        $randomText = Text::inRandomOrder()->first();

        $textbook = Textbook::find($randomText->textbook_id);

        $text = [
            'title' => 'This is a test for update a text with invalid file as admin',
            'description' => 'This is a test for update a text invalid file as admin',
            'file' => new UploadedFile(public_path('/readingMaterial/thumbnail.png'), 'thumbnail.png', 'image/png', null,  true),
            'section' => 'moduleTexbook',
            'selected' => $textbook->toJson()
        ];

        $this->actingAs($this->adminUser)
            ->patchJson('/api/text/'.$randomText->id, $text)
            ->assertJson([
                "errors" => [
                    "file" => ["The file must be a file of type: pdf."]],
                "message" => "The given data was invalid."
            ])
            ->assertStatus(422);

        $this->assertDatabaseMissing('texts', [
            'title' => $text['title'],
            'description' => $text['description'],
        ]);

        //Module Tutor
        $randomText =  Module::has('textbooks.texts')->whereHas('users', function ($query){
            $query->where('user_id', $this->moduleTutorUser->id);
        })->first()->textbooks()->first()->texts()->first();

        $textbook = Textbook::find($randomText->textbook_id);

        $text = [
            'title' => 'This is a test for create a new text with file invalid as module tutor',
            'description' => 'This is a test for create a new text file invalid as module tutor',
            'file' =>  new UploadedFile(public_path('/readingMaterial/thumbnail.png'), 'thumbnail.png', 'image/png', null,  true),
            'section' => 'moduleTextbook',
            'selected' => $textbook->toJson()
        ];

        $this->actingAs($this->moduleTutorUser)
            ->patchJson('/api/text/'.$randomText->id, $text)
            ->assertJson([
                "errors" => [
                    "file" => ["The file must be a file of type: pdf."]],
                "message" => "The given data was invalid."
            ])
            ->assertStatus(422);

        $this->assertDatabaseMissing('texts', [
            'title' => $text['title'],
            'description' => $text['description'],
        ]);
    }

    /**
     * A test to update a module text as an Admin authorised and valid.
     *
     * @test
     * @return void
     */
    public function test_update_text_as_admin_module_text_all_valid()
    {
        $randomText = Text::has('textbook.modules')->inRandomOrder()->first();
        $randomModuleTextbook = Textbook::has('modules')->inRandomOrder()->first();

        $AdminModuleTextbookText = [
            'title' => 'Testing update a text for a module textbook admin',
            'description' => 'This is a test for update a text for a module textbook as admin',
            'file' =>  new UploadedFile(public_path('/readingMaterial/example.pdf'), 'example.pdf', 'application/pdf', null,  true),
            'section' => 'moduleTextbook',
            'selected' => $randomModuleTextbook->toJson()
        ];
        $this->actingAs($this->adminUser)
            ->patchJson('/api/text/'.$randomText->id, $AdminModuleTextbookText)
            ->assertSuccessful();

        $this->assertDatabaseHas('texts', [
            'title' => $AdminModuleTextbookText['title'],
            'description' => $AdminModuleTextbookText['description'],
            'file' => base64_encode(file_get_contents(public_path('/readingMaterial/example.pdf'))),
            'textbook_id' => $randomModuleTextbook->id,
        ]);

        $this->assertDatabaseMissing('texts', [
            'title' => $randomText->title,
            'description' => $randomText->description,
            'file' => base64_encode(file_get_contents(public_path('/readingMaterial/example.pdf'))),
            'textbook_id' => $randomText->textbook_id,
        ]);
    }

    /**
     * A test to update a extensive reading text as an Admin authorised and valid.
     *
     * @test
     * @return void
     */
    public function test_update_text_as_admin_extensive_reading_text_all_valid()
    {
        $randomText = Text::has('textbook.extensiveReadingCategories')->inRandomOrder()->first();
        $randomExtensiveReadingTextbook = Textbook::has('extensiveReadingCategories')->inRandomOrder()->first();

        $AdminExtensiveReadingTextbookText = [
            'title' => 'Testing update a text for a extensive reading textbook admin',
            'description' => 'This is a test for update a text for a extensive reading textbook as admin',
            'file' =>  new UploadedFile(public_path('/readingMaterial/example.pdf'), 'example.pdf', 'application/pdf', null,  true),
            'section' => 'extensiveReadingTextbook',
            'selected' => $randomExtensiveReadingTextbook->toJson()
        ];
        $this->actingAs($this->adminUser)
            ->patchJson('/api/text/'.$randomText->id, $AdminExtensiveReadingTextbookText)
            ->assertSuccessful();;

        $this->assertDatabaseHas('texts', [
            'title' => $AdminExtensiveReadingTextbookText['title'],
            'description' => $AdminExtensiveReadingTextbookText['description'],
            'file' => base64_encode(file_get_contents(public_path('/readingMaterial/example.pdf'))),
            'textbook_id' => $randomExtensiveReadingTextbook->id,
        ]);

        $this->assertDatabaseMissing('texts', [
            'title' => $randomText->title,
            'description' => $randomText->description,
            'file' => base64_encode(file_get_contents(public_path('/readingMaterial/example.pdf'))),
            'textbook_id' => $randomText->textbook_id,
        ]);
    }

    /**
     * A test to store a module text as an module tutor authorised and valid.
     *
     * @test
     * @return void
     */
    public function test_update_text_as_module_tutor_module_text_all_valid()
    {
        $randomText =  Module::has('textbooks.texts')->whereHas('users', function ($query){
            $query->where('user_id', $this->moduleTutorUser->id);
        })->first()->textbooks()->first()->texts()->has('textbook.modules')->first();

        $randomModuleTextbook = Module::has('textbooks.texts')->whereHas('users', function ($query){
            $query->where('user_id', $this->moduleTutorUser->id);
        })->first()->textbooks()->first();

        $Module_Tutor_ModuleTextbookText = [
            'title' => 'Testing update a text for a module textbook module tutor',
            'description' => 'This is a test for update a text for a module textbook as module tutor',
            'file' =>  new UploadedFile(public_path('/readingMaterial/example.pdf'), 'example.pdf', 'application/pdf', null,  true),
            'section' => 'moduleTextbook',
            'selected' => $randomModuleTextbook->toJson()
        ];
        $this->actingAs($this->moduleTutorUser)
            ->patchJson('/api/text/'.$randomText->id, $Module_Tutor_ModuleTextbookText)
            ->assertSuccessful();

        $this->assertDatabaseHas('texts', [
            'title' => $Module_Tutor_ModuleTextbookText['title'],
            'description' => $Module_Tutor_ModuleTextbookText['description'],
            'file' => base64_encode(file_get_contents(public_path('/readingMaterial/example.pdf'))),
            'textbook_id' => $randomModuleTextbook->id,
        ]);

        $this->assertDatabaseMissing('texts', [
            'title' => $randomText->title,
            'description' => $randomText->description,
            'file' => base64_encode(file_get_contents(public_path('/readingMaterial/example.pdf'))),
            'textbook_id' => $randomText->textbook_id,
        ]);
    }

    /**
     * A test to update a extensive reading text as an module tutor authorised and valid.
     *
     * @test
     * @return void
     */
    public function test_update_text_as_module_tutor_extensive_reading_text_all_valid()
    {
        $randomText = Text::has('textbook.extensiveReadingCategories')->inRandomOrder()->first();
        $randomExtensiveReadingTextbook = Textbook::has('extensiveReadingCategories')->inRandomOrder()->first();

        $Module_Tutor_ExtensiveReadingTextbookText = [
            'title' => 'Testing update a text for a extensive reading textbook module tutor',
            'description' => 'This is a test for update a text for a extensive reading textbook as module tutor',
            'file' =>  new UploadedFile(public_path('/readingMaterial/example.pdf'), 'example.pdf', 'application/pdf', null,  true),
            'section' => 'extensiveReadingTextbook',
            'selected' => $randomExtensiveReadingTextbook->toJson()
        ];
        $this->actingAs($this->moduleTutorUser)
            ->patchJson('/api/text/'.$randomText->id, $Module_Tutor_ExtensiveReadingTextbookText)
            ->assertSuccessful();

        $this->assertDatabaseHas('texts', [
            'title' => $Module_Tutor_ExtensiveReadingTextbookText['title'],
            'description' => $Module_Tutor_ExtensiveReadingTextbookText['description'],
            'file' => base64_encode(file_get_contents(public_path('/readingMaterial/example.pdf'))),
            'textbook_id' => $randomExtensiveReadingTextbook->id,
        ]);

        $this->assertDatabaseMissing('texts', [
            'title' => $randomText->title,
            'description' => $randomText->description,
            'file' => base64_encode(file_get_contents(public_path('/readingMaterial/example.pdf'))),
            'textbook_id' => $randomText->textbook_id,
        ]);
    }

    /**
     * A test to delete a text authorised by logging in as an Admin.
     *
     * @test
     * @return void
     */
    public function test_delete_a_text_admin_authorised()
    {
        $text = Text::inRandomOrder()->first();
        $this->actingAs($this->adminUser)
            ->deleteJson('/api/text/'.$text->id)
            ->assertSuccessful()
            ->assertStatus(200);

        $this->assertDatabaseMissing('texts', [
            'title' => $text->title,
            'description' => $text->description,
            'textbook_id' => $text->textbook_id,
        ]);
    }

    /**
     * A test to delete a text authorised by logging in as an Module Tutor.
     *
     * @test
     * @return void
     */
    public function test_delete_a_text_module_tutor_authorised()
    {
        $text =  Module::has('textbooks.texts')->whereHas('users', function ($query){
            $query->where('user_id', $this->moduleTutorUser->id);
        })->first()->textbooks()->first()->texts()->first();

        $this->actingAs($this->moduleTutorUser)
            ->deleteJson('/api/text/'.$text->id)
            ->assertSuccessful()
            ->assertStatus(200);

        $this->assertDatabaseMissing('texts', [
            'title' => $text->title,
            'description' => $text->description,
            'textbook_id' => $text->textbook_id,
        ]);
    }

    /**
     * A test to delete a text permission denied by logging in as an Module Tutor.
     *
     * @test
     * @return void
     */
    public function test_delete_a_text_module_tutor_permission_denied()
    {
        $text =  Module::has('textbooks.texts')->whereDoesntHave('users', function ($query){
            $query->where('user_id', $this->moduleTutorUser->id);
        })->first()->textbooks()->first()->texts()->first();

        $this->actingAs($this->moduleTutorUser)
            ->deleteJson('/api/text/'.$text->id)
            ->assertJsonFragment(["Error" => "Permission denied to delete text!"]);

        $this->assertDatabaseHas('texts', [
            'title' => $text->title,
            'description' => $text->description,
            'textbook_id' => $text->textbook_id,
        ]);
    }

    /**
     * A test to delete a text unauthorised by logging in as a Student.
     *
     * @test
     * @return void
     */
    public function test_delete_a_text_student_unauthorised()
    {
        $text = Text::inRandomOrder()->first();

        $this->actingAs($this->studentUser)
            ->deleteJson('/api/text/'.$text->id)
            ->assertJsonFragment(["error" => "Unauthorized"])
            ->assertStatus(403);

        $this->assertDatabaseHas('texts', [
            'title' => $text->title,
            'description' => $text->description,
            'textbook_id' => $text->textbook_id,
        ]);
    }

    /**
     * A test to delete a text id not found for Admins or Module Tutors
     *
     * @test
     * @return void
     */
    public function test_delete_a_text_id_not_found()
    {
        $text = Text::inRandomOrder()->first();

        $this->actingAs($this->adminUser)
            ->deleteJson('/api/text/'.rand(9999, 10000))
            ->assertStatus(404);

        $this->assertDatabaseHas('texts', [
            'title' => $text->title,
            'description' => $text->description,
            'textbook_id' => $text->textbook_id,
        ]);

        $this->actingAs($this->moduleTutorUser)
            ->deleteJson('/api/text/'.rand(9999, 10000))
            ->assertStatus(404);

        $this->assertDatabaseHas('texts', [
            'title' => $text->title,
            'description' => $text->description,
            'textbook_id' => $text->textbook_id,
        ]);
    }

    /**
     * A test to create a reading session as a student authorised
     *
     * @test
     * @return void
     */
    public function test_save_attempt_student_authorised()
    {
        $text =  Module::has('textbooks.texts')->whereHas('users', function ($query){
            $query->where('user_id', $this->studentUser->id);
        })->first()->textbooks()->first()->texts()->first();

        $attempt = [
            'text_id' => $text->id,
            'attempt_num' => 1,
            'time' => '00:10:56'
        ];

        $this->actingAs($this->studentUser)
            ->postJson('/api/text/saveAttempt', $attempt)
            ->assertSuccessful()
            ->assertJson(['Success' => 'Successfully saved the attempt!']);

        $this->assertDatabaseHas('reading_sessions', [
            'text_id' => $attempt['text_id'],
            'user_id' => $this->studentUser->id,
            'attempt_number' => $attempt['attempt_num'],
            'time_taken' => $attempt['time']
        ]);
    }

    /**
     * A test to create a reading session as a student permission denied
     *
     * @test
     * @return void
     */
    public function test_save_attempt_student_permission_denied()
    {
        $text =  Module::has('textbooks.texts')->whereDoesntHave('users', function ($query){
            $query->where('user_id', $this->studentUser->id);
        })->first()->textbooks()->first()->texts()->first();

        $attempt = [
            'text_id' => $text->id,
            'attempt_num' => 1,
            'time' => '00:10:56'
        ];

        $this->actingAs($this->studentUser)
            ->postJson('/api/text/saveAttempt', $attempt)
            ->assertJson(['Error' => 'Permission denied to delete text!']);

        $this->assertDatabaseMissing('reading_sessions', [
            'text_id' => $attempt['text_id'],
            'user_id' => $this->studentUser->id,
            'attempt_number' => $attempt['attempt_num'],
            'time_taken' => $attempt['time']
        ]);
    }

    /**
     * A test to create a reading session as a student text not found.
     *
     * @test
     * @return void
     */
    public function test_save_attempt_student_text_not_found()
    {
        $attempt = [
            'text_id' => rand(9999, 10000),
            'attempt_num' => 1,
            'time' => '00:10:56'
        ];

        $this->actingAs($this->studentUser)
            ->postJson('/api/text/saveAttempt', $attempt)
            ->assertJson(['Error' => 'Text not found!']);

        $this->assertDatabaseMissing('reading_sessions', [
            'text_id' => $attempt['text_id'],
            'user_id' => $this->studentUser->id,
            'attempt_number' => $attempt['attempt_num'],
            'time_taken' => $attempt['time']
        ]);
    }

    /**
     * A test to get attempt for a text as attempt one by making sure the response is null/empty
     * as a student authorised.
     *
     * @test
     * @return void
     */
    public function test_get_attempt_student_attempt_null_authorised()
    {
        $text = Module::has('textbooks.texts')->whereHas('users', function ($query) {
            $query->where('user_id', $this->studentUser->id);
        })->first()->textbooks()->first()->texts()->first();


        //Check is JSON is null/empty as there is no attempt of the text.
        $this->actingAs($this->studentUser)
            ->getJson('/api/text/getAttempt/' . $text->id)
            ->assertExactJson([]);

    }
    /**
     * A test to get attempt for a text as attempt one by storing attempt one
     * then getting the attempt to make sure the response is 1
     * as a student authorised.
     *
     * @test
     * @return void
     */
    public function test_get_attempt_student_attempt_one_authorised()
    {
        $text =  Module::has('textbooks.texts')->whereHas('users', function ($query){
            $query->where('user_id', $this->studentUser->id);
        })->first()->textbooks()->first()->texts()->first();

        $attempt = [
            'text_id' => $text->id,
            'attempt_num' => 1,
            'time' => '00:10:56'
        ];

        $this->actingAs($this->studentUser)
            ->postJson('/api/text/saveAttempt', $attempt)
            ->assertSuccessful()
            ->assertJson(['Success' => 'Successfully saved the attempt!']);

        //Check that the attempt number is 1 now.
        $this->actingAs($this->studentUser)
            ->getJson('/api/text/getAttempt/' . $text->id)
            ->assertJson([
                'attempt' => 1,
                'time_taken' => $attempt['time']
            ]);
    }

    /**
     * A test to get all attempts as Admin.
     * Admins can view any Students reading session attempts.
     *
     * @test
     * @return void
     */
    public function test_get_all_attempts_admin()
    {
        for($i=0; $i<10; $i++){
            $text =  Module::has('textbooks.texts')->whereHas('users', function ($query){
                $query->where('user_id', $this->studentUser->id);
            })->first()->textbooks()->first()->texts()->first();

            $attempt = [
                'text_id' => $text->id,
                'attempt_num' => $i+1,
                'time' => '00:10:56'
            ];

            $this->actingAs($this->studentUser)
                ->postJson('/api/text/saveAttempt', $attempt)
                ->assertSuccessful()
                ->assertJson(['Success' => 'Successfully saved the attempt!']);
        }

        $attempts = ReadingSession::with('text.textbook')->with('user')->get();
        $textbooks = ReadingSession::with('text.textbook')->get()->unique('text.textbook')->pluck('text.textbook');

        $this->actingAs($this->adminUser)
            ->getJson('/api/text/getAllAttemptsForCurrentUser')
            ->assertSuccessful()
            ->assertJson([
                'attempts' => $attempts->toArray(),
                'textbooks' => $textbooks->toArray()
            ]);
    }

    /**
     * A test to get all attempts as a Module Tutor.
     * Module Tutors can see Students reading session attempts to all extensive reading category
     * and only the module students that are assigned to them.
     *
     * @test
     * @return void
     */
    public function test_get_all_attempts_module_tutor()
    {
        for($i=0; $i<10; $i++){
            $text =  Module::has('textbooks.texts')->whereHas('users', function ($query){
                $query->where('user_id', $this->studentUser->id);
            })->first()->textbooks()->first()->texts()->first();

            $attempt = [
                'text_id' => $text->id,
                'attempt_num' => $i+1,
                'time' => '00:10:56'
            ];

            $this->actingAs($this->studentUser)
                ->postJson('/api/text/saveAttempt', $attempt)
                ->assertSuccessful()
                ->assertJson(['Success' => 'Successfully saved the attempt!']);
        }

        $moduleTextbooks = $this->moduleTutorUser->modules()->has('textbooks')->
            with('textbooks')->get()->pluck('textbooks');
            $extensiveReadingTextbooks = ExtensiveReadingCategory::has('textbooks')->
            with('textbooks')->get()->pluck('textbooks');

            $textbooks = $moduleTextbooks->merge($extensiveReadingTextbooks);
            $textbooks = call_user_func_array('array_merge', $textbooks->toArray());
            $allTextbookIds = collect($textbooks)->pluck('id');
            $attempts = ReadingSession::with('text.textbook')->with('user')
                ->whereHas('text.textbook', function($query) use ($allTextbookIds){
                    $query->whereIn('id',$allTextbookIds);
                })->get();

            $textbooks = $attempts->unique('text.textbook')->pluck('text.textbook');

        $this->actingAs($this->moduleTutorUser)
            ->getJson('/api/text/getAllAttemptsForCurrentUser')
            ->assertSuccessful()
            ->assertJson([
                'attempts' => $attempts->toArray(),
                'textbooks' => $textbooks->toArray()
            ]);
    }

    /**
     * A test to get all attempts as a Student.
     * Students can only see the reading session attempts they've done.
     *
     * @test
     * @return void
     */
    public function test_get_all_attempts_student()
    {
        for($i=0; $i<10; $i++){
            $text =  Module::has('textbooks.texts')->whereHas('users', function ($query){
                $query->where('user_id', $this->studentUser->id);
            })->first()->textbooks()->first()->texts()->first();

            $attempt = [
                'text_id' => $text->id,
                'attempt_num' => $i+1,
                'time' => '00:10:56'
            ];

            $this->actingAs($this->studentUser)
                ->postJson('/api/text/saveAttempt', $attempt)
                ->assertSuccessful()
                ->assertJson(['Success' => 'Successfully saved the attempt!']);
        }

        $attempts = ReadingSession::where('user_id', $this->studentUser->id)->with('text.textbook')->get();
        $textbooks = ReadingSession::where('user_id', $this->studentUser->id)->with('text.textbook')->get()->unique('text.textbook')->pluck('text.textbook');

        $this->actingAs($this->studentUser)
            ->getJson('/api/text/getAllAttemptsForCurrentUser')
            ->assertSuccessful()
            ->assertJson([
                'attempts' => $attempts->toArray(),
                'textbooks' => $textbooks->toArray()
            ]);
    }

    /**
     * A test to get last 5 attempts as Admin.
     * Admins can view any last 5 students reading session attempts.
     *
     * @test
     * @return void
     */
    public function test_get_last_5_attempts_admin()
    {
        for($i=0; $i<10; $i++){
            $text =  Module::has('textbooks.texts')->whereHas('users', function ($query){
                $query->where('user_id', $this->studentUser->id);
            })->first()->textbooks()->first()->texts()->first();

            $attempt = [
                'text_id' => $text->id,
                'attempt_num' => $i+1,
                'time' => '00:10:56'
            ];

            $this->actingAs($this->studentUser)
                ->postJson('/api/text/saveAttempt', $attempt)
                ->assertSuccessful()
                ->assertJson(['Success' => 'Successfully saved the attempt!']);
        }

        $last5 = ReadingSession::with('text.textbook')->with('user')->orderBy('id', 'desc')->take(5)->get();

        $this->actingAs($this->adminUser)
            ->getJson('/api/text/getLast5attempts')
            ->assertSuccessful()
            ->assertJson(
                $last5->toArray()
            );
    }

    /**
     * A test to get last 5 attempts as a Module Tutor.
     * Module Tutors can see last 5 students reading session attempts to all extensive reading category
     * and only the module students that are assigned to them.
     *
     * @test
     * @return void
     */
    public function test_get_last_5_attempts_module_tutor()
    {
        for($i=0; $i<10; $i++){
            $text =  Module::has('textbooks.texts')->whereHas('users', function ($query){
                $query->where('user_id', $this->studentUser->id);
            })->first()->textbooks()->first()->texts()->first();

            $attempt = [
                'text_id' => $text->id,
                'attempt_num' => $i+1,
                'time' => '00:10:56'
            ];

            $this->actingAs($this->studentUser)
                ->postJson('/api/text/saveAttempt', $attempt)
                ->assertSuccessful()
                ->assertJson(['Success' => 'Successfully saved the attempt!']);
        }

        $moduleTextbooks = $this->moduleTutorUser->modules()->has('textbooks')->
        with('textbooks')->get()->pluck('textbooks');

        $extensiveReadingTextbooks = ExtensiveReadingCategory::has('textbooks')->
        with('textbooks')->get()->pluck('textbooks');

        $textbooks = $moduleTextbooks->merge($extensiveReadingTextbooks);
        $textbooks = call_user_func_array('array_merge', $textbooks->toArray());
        $allTextbookIds = collect($textbooks)->pluck('id');

        $last5 = ReadingSession::with('text.textbook')->with('user')
            ->whereHas('text.textbook', function($query) use ($allTextbookIds){
                $query->whereIn('id',$allTextbookIds);
            })->orderBy('id', 'desc')->take(5)->get();

        $this->actingAs($this->moduleTutorUser)
            ->getJson('/api/text/getLast5attempts')
            ->assertSuccessful()
            ->assertJson(
                $last5->toArray()
            );
    }

    /**
     * A test to get all attempts as a Student.
     * Students can only see the reading session attempts they've done.
     *
     * @test
     * @return void
     */
    public function test_get_last_5_attempts_student()
    {
        for($i=0; $i<10; $i++){
            $text =  Module::has('textbooks.texts')->whereHas('users', function ($query){
                $query->where('user_id', $this->studentUser->id);
            })->first()->textbooks()->first()->texts()->first();

            $attempt = [
                'text_id' => $text->id,
                'attempt_num' => $i+1,
                'time' => '00:10:56'
            ];

            $this->actingAs($this->studentUser)
                ->postJson('/api/text/saveAttempt', $attempt)
                ->assertSuccessful()
                ->assertJson(['Success' => 'Successfully saved the attempt!']);
        }

        $last5 = ReadingSession::with('text.textbook')
                ->where('user_id', auth()->user()->id)
                ->orderBy('id', 'desc')->take(5)->get();

        $this->actingAs($this->studentUser)
            ->getJson('/api/text/getLast5attempts')
            ->assertSuccessful()
            ->assertJson(
                $last5->toArray()
            );
    }

}
