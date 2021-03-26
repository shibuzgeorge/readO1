<?php

namespace Tests\Feature;

use App\ExtensiveReadingCategory;
use App\Text;
use App\Textbook;
use App\Module;
use App\User;
use App\YearGroup;
use Illuminate\Support\Facades\File;
use Spatie\PdfToText;
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
        })->inRandomOrder()->first()->textbooks()->inRandomOrder()->first()
            ->texts()->inRandomOrder()->first();

        $this->actingAs($this->moduleTutorUser)
            ->getJson('/api/text/'.$unAssignedModuleModuleTutorText->id)
            ->assertJsonFragment(['Error' => 'Permission denied to view the text!']);

        $unAssignedModuleStudentText = Module::has('textbooks.texts')->whereDoesntHave('users', function ($query){
            $query->where('user_id', $this->studentUser->id);
        })->inRandomOrder()->first()->textbooks()->inRandomOrder()->first()
            ->texts()->inRandomOrder()->first();

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
        $text = Text::where('file','!=', 'null')->inRandomOrder()->first();

        $decoded = base64_decode($text->file);
        file_put_contents(public_path('file.pdf'), $decoded);
        $pdftext = base64_encode(PdfToText\Pdf::getText(public_path('file.pdf')));
        File::delete(public_path('file.pdf'));
        $this->actingAs($this->adminUser)
            ->getJson('/api/text/'.$text->id)
            ->assertSuccessful()
            ->assertJson([
                'title' => $text->title,
                'description' => $text->description,
                'text' => $pdftext,
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
        })->inRandomOrder()->first()->textbooks()->inRandomOrder()->first()
            ->texts()->where('file','!=', 'null')->inRandomOrder()->first();

        $decoded = base64_decode($text->file);
        file_put_contents(public_path('file.pdf'), $decoded);
        $pdftext = base64_encode(PdfToText\Pdf::getText(public_path('file.pdf')));
        File::delete(public_path('file.pdf'));
        $this->actingAs($this->moduleTutorUser)
            ->getJson('/api/text/'.$text->id)
            ->assertSuccessful()
            ->assertJson([
                'title' => $text->title,
                'description' => $text->description,
                'text' => $pdftext,
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
        })->inRandomOrder()->first()->textbooks()->inRandomOrder()->first()
            ->texts()->where('file','!=', 'null')->inRandomOrder()->first();

        $decoded = base64_decode($text->file);
        file_put_contents(public_path('file.pdf'), $decoded);
        $pdftext = base64_encode(PdfToText\Pdf::getText(public_path('file.pdf')));
        File::delete(public_path('file.pdf'));
        $this->actingAs($this->studentUser)
            ->getJson('/api/text/'.$text->id)
            ->assertSuccessful()
            ->assertJson([
                'title' => $text->title,
                'description' => $text->description,
                'text' => $pdftext,
            ]);
    }

    /**
     *
     * A test to check if an Admin can download pdf of text
     *
     */
    public function test_pdf_download_admin_authorised()
    {
        $text = Text::where('file', '!=', 'null')->inRandomOrder()->first();

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
        })->inRandomOrder()->first()->textbooks()->inRandomOrder()->first()
            ->texts()->where('file','!=', 'null')->inRandomOrder()->first();

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
        })->inRandomOrder()->first()->textbooks()->inRandomOrder()->first()
            ->texts()->where('file','!=', 'null')->inRandomOrder()->first();

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
        })->inRandomOrder()->first()->textbooks()->inRandomOrder()->first()
            ->texts()->where('file','!=', 'null')->inRandomOrder()->first();

        $this->actingAs($this->moduleTutorUser)
            ->getJson('/api/text/pdf/'.$textModuleTutorDenied->id)
            ->assertJsonFragment(['Error' => 'Permission denied to download the text!']);

        $textStudentDenied = Module::has('textbooks.texts')->whereDoesntHave('users', function ($query){
            $query->where('user_id', $this->studentUser->id);
        })->inRandomOrder()->first()->textbooks()->inRandomOrder()->first()
            ->texts()->where('file','!=', 'null')->inRandomOrder()->first();;

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
     * A test to check data for edit page for a text as a Admin
     *
     * @test
     * @return void
     */
    public function test_edit_page_admin_module_authorised()
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
}
