<?php

namespace Tests\Browser;

use Tests\DuskTestCase;
use Tests\Browser\Pages\Login;
use App\User;

class SideBarMenuTest extends DuskTestCase
{
    /** @var \App\User */
    protected $studentUser;

    /** @var \App\User */
    protected $moduleTutorUser;

    /** @var \App\User */
    protected $adminUser;

    public function setUp(): void
    {
        parent::setup();
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

        static::closeAll();
    }

    /**
     * Expands all the menus in the side bar for an Admin User.
     * Checks if the texts/links are working and visible.
     * Checks it goes to the right pages and checks the title.
     *
     * @test
     * @throws \Throwable
     */
    public function test_what_admins_see_in_side_bar_menu()
    {
        $this->browse(function ($browser) {
            $browser->visit(new Login)
                ->submit($this->adminUser->email, 'password')
                ->pause(2000)
                //expands all menus for a admin
                ->waitFor('#extensive-reading-menu')
                ->clickLink('Extensive Reading')
                ->waitFor('#textbooks-menu')
                ->clickLink('Textbooks')
                ->waitFor('#texts-menu')
                ->clickLink('Texts')
                ->waitFor('#modules-menu')
                ->clickLink('Modules')
                ->waitFor('#users-menu')
                ->clickLink('Users')
                ->pause(1000)
                //checks if these are visible on the side bar menu.
                ->assertSeeIn('#sidebar-wrapper', 'Dashboard')
                ->assertSeeIn('#sidebar-wrapper', 'Library')
                ->assertSeetIn('#sidebar-wrapper', 'Reading Sessions and Quiz Scores')
                ->assertSeeIn('#sidebar-wrapper', 'Extensive Reading')
                ->assertSeeIn('#extensive-reading-expanded', 'Home')
                ->assertSeeIn('#extensive-reading-expanded', 'View all categories')
                ->assertSeeIn('#extensive-reading-expanded', 'Create a category')
                ->assertSeeIn('#sidebar-wrapper', 'Textbooks')
                ->assertSeeIn('#textbooks-expanded', 'Create a textbook')
                ->assertSeeIn('#sidebar-wrapper', 'Texts')
                ->assertSeeIn('#texts-expanded', 'Add a text')
                ->assertSeeIn('#sidebar-wrapper', 'Modules')
                ->assertSeeIn('#modules-expanded', 'All Modules')
                ->assertSeeIn('#modules-expanded', 'Create Module')
                ->assertSeeIn('#modules-expanded', 'Assign Students')
                ->assertSeeIn('#modules-expanded', 'Assign Module Tutors')
                ->assertSeeIn('#sidebar-wrapper', 'Year Groups')
                ->assertSeeIn('#sidebar-wrapper', 'Users')
                ->assertSeeIn('#users-expanded', 'View all users')
                ->assertSeeIn('#users-expanded', 'Create a user');
        });
    }

    /**
     * Expands all the menus in the side bar for an Module Tutor User.
     * Checks if the texts/links are working and visible.
     * Checks it goes to the right pages and checks the title.
     *
     * @test
     * @throws \Throwable
     */
    public function test_what_module_tutors_see_in_side_bar_menu()
    {
        $this->browse(function ($browser) {
            $browser->visit(new Login)
                ->submit($this->moduleTutorUser->email, 'password')
                ->pause(2000)
                //expands all menus for a module tutor
                ->waitFor('#extensive-reading-menu')
                ->clickLink('Extensive Reading')
                ->waitFor('#textbooks-menu')
                ->clickLink('Textbooks')
                ->waitFor('#texts-menu')
                ->clickLink('Texts')
                ->waitFor('#modules-menu')
                ->clickLink('Modules')
                ->pause(1000)
                //checks if these are visible on the side bar menu.
                ->assertSeeIn('#sidebar-wrapper', 'Dashboard')
                ->assertSeeIn('#sidebar-wrapper', 'Library')
                ->assertSeetIn('#sidebar-wrapper', 'Reading Sessions and Quiz Scores')
                ->assertSeeIn('#sidebar-wrapper', 'Extensive Reading')
                ->assertSeeIn('#extensive-reading-expanded', 'Home')
                ->assertSeeIn('#extensive-reading-expanded', 'View all categories')
                ->assertSeeIn('#extensive-reading-expanded', 'Create a category')
                ->assertSeeIn('#sidebar-wrapper', 'Textbooks')
                ->assertSeeIn('#textbooks-expanded', 'Create a textbook')
                ->assertSeeIn('#sidebar-wrapper', 'Texts')
                ->assertSeeIn('#texts-expanded', 'Add a text')
                ->assertSeeIn('#sidebar-wrapper', 'Modules')
                ->assertSeeIn('#modules-expanded', 'All Modules')
                ->assertSeeIn('#modules-expanded', 'Assign Students')
                //checks if these are not visible on the side bar menu.
                ->assertDontSeeIn('#sidebar-wrapper', 'Create Module')
                ->assertDontSeeIn('#sidebar-wrapper', 'Assign Module Tutors')
                ->assertDontSeeIn('#sidebar-wrapper', 'Year Groups')
                ->assertDontSeeIn('#sidebar-wrapper', 'Users')
                ->assertDontSeeIn('#sidebar-wrapper', 'View all users')
                ->assertDontSeeIn('#sidebar-wrapper', 'Create a user');
        });
    }

    /**
     * Expands all the menus in the side bar for an Student User.
     * Checks if the texts/links are working and visible.
     * Checks it goes to the right pages and checks the title.
     *
     * @test
     * @throws \Throwable
     */
    public function test_what_students_see_in_side_bar_menu()
    {
        $this->browse(function ($browser) {
            $browser->visit(new Login)
                ->submit($this->studentUser->email, 'password')
                ->pause(2000)
                //expands all menus for a student
                ->waitFor('#extensive-reading-menu')
                ->clickLink('Extensive Reading')
                ->waitFor('#modules-menu')
                ->clickLink('Modules')
                ->pause(1000)
                //checks if these are visible on the side bar menu.
                ->assertSeeIn('#sidebar-wrapper', 'Dashboard')
                ->assertSeeIn('#sidebar-wrapper', 'Library')
                ->assertSeetIn('#sidebar-wrapper', 'Reading Sessions and Quiz Scores')
                ->assertSeeIn('#sidebar-wrapper', 'Extensive Reading')
                ->assertSeeIn('#extensive-reading-expanded', 'Home')
                ->assertSeeIn('#extensive-reading-expanded', 'View all categories')
                ->assertSeeIn('#sidebar-wrapper', 'Modules')
                ->assertSeeIn('#modules-expanded', 'My Modules')
                //checks if these are not visible on the side bar menu.
                ->assertDontSeeIn('#sidebar-wrapper', 'Create a category')
                ->assertDontSeeIn('#sidebar-wrapper', 'Textbooks')
                ->assertDontSeeIn('#sidebar-wrapper', 'Create a textbook')
                ->assertDontSeeIn('#sidebar-wrapper', 'All Modules')
                ->assertDontSeeIn('#sidebar-wrapper', 'Create Module')
                ->assertDontSeeIn('#sidebar-wrapper', 'Assign Module Tutors')
                ->assertDontSeeIn('#sidebar-wrapper', 'Assign Students')
                ->assertDontSeeIn('#sidebar-wrapper', 'Year Groups')
                ->assertDontSeeIn('#sidebar-wrapper', 'Users')
                ->assertDontSeeIn('#sidebar-wrapper', 'View all users')
                ->assertDontSeeIn('#sidebar-wrapper', 'Create a user');
        });
    }

    /**
     * Expands all the menus in the modules section in the side bar menu for a Student User.
     * Checks if the texts/links are working and visible.
     * Checks it goes to the right pages and checks the title.
     *
     * @test
     * @throws \Throwable
     */
    public function test_student_modules_menu()
    {
        $this->browse(function ($browser) {
            $browser->visit(new Login)
                ->submit($this->studentUser->email, 'password')
                ->pause(2000)
                ->waitFor('#modules-menu')
                ->pause(1000)
                ->clickLink('Modules')
                ->waitFor('#my-modules-menu')
                ->clickLink('My Modules')
                ->waitFor('#title-my-modules')
                ->assertPathis('/module')
                ->assertSeeIn('#title-my-modules', 'My Modules');
        });
    }

    /**
     * Expands all the menus in the modules section in the side bar menu for a Module Tutor User.
     * Checks if the texts/links are working and visible.
     * Checks it goes to the right pages and checks the title.
     *
     * @test
     * @throws \Throwable
     */
    public function test_module_tutor_modules_menu()
    {
        $this->browse(function ($browser) {
            $browser->visit(new Login)
                ->submit($this->moduleTutorUser->email, 'password')
                ->pause(2000)
                ->waitFor('#modules-menu')
                ->clickLink('Modules')
                ->waitFor('#all-modules-menu')
                ->clickLink('All Modules')
                ->waitFor('#title-all-modules')
                ->pause(1000)
                ->assertPathis('/module')
                ->assertSeeIn('#title-all-modules', 'All Modules')
                //click assign students
                ->clickLink('Assign Students')
                ->waitFor('#title')
                ->pause(1000)
                ->assertPathis('/module/assignStudents')
                ->assertSeeIn('#title', 'Assign students to modules');
        });
    }

    /**
     * Expands all the menus in the modules section in the side bar menu for a Admin User.
     * Checks if the texts/links are working and visible.
     * Checks it goes to the right pages and checks the title.
     *
     * @test
     * @throws \Throwable
     */
    public function test_admins_modules_menu()
    {
        $this->browse(function ($browser ) {
            $browser->visit(new Login)
                ->submit($this->adminUser->email, 'password')
                ->pause(2000)
                ->waitFor('#modules-menu')
                ->clickLink('Modules')
                ->waitFor('#all-modules-menu')
                ->clickLink('All Modules')
                ->waitFor('#title-all-modules')
                ->assertPathis('/module')
                ->assertSeeIn('#title-all-modules', 'All Modules')
                //click create module
                ->clickLink('Create Module')
                ->waitFor('#title')
                ->pause(2000)
                ->assertPathis('/module/create')
                ->assertSeeIn('#title', 'Create Module')
                //click assign students
                ->clickLink('Assign Students')
                ->waitFor('#title', 10)
                ->pause(2000)
                ->assertPathis('/module/assignStudents')
                ->assertSeeIn('#title', 'Assign students to modules')
                //click assign module tutors
                ->clickLink('Assign Module Tutors')
                ->waitFor('#title', 10)
                ->pause(2000)
                ->assertPathis('/module/assignModuleTutors')
                ->assertSeeIn('#title', 'Assign module tutors to modules');
        });
    }

    /**
     * Checks the library text and links are working for all Student, Module Tutor and Admins
     *
     * @test
     * @throws \Throwable
     */
    public function test_library_menu()
    {
        $this->browse(function ($browser1, $browser2, $browser3) {
            $browser1->visit(new Login)
                ->submit($this->studentUser->email, 'password')
                ->pause(2000)
                ->waitFor('#library-menu')
                ->clickLink('Library')
                ->pause(5000)
                ->waitFor('#title')
                ->assertPathis('/library')
                ->assertSeeIn('#title', 'Library');

            $browser2->visit(new Login)
                ->submit($this->moduleTutorUser->email, 'password')
                ->pause(2000)
                ->waitFor('#library-menu')
                ->clickLink('Library')
                ->pause(5000)
                ->waitFor('#title')
                ->assertPathis('/library')
                ->assertSeeIn('#title', 'Library');

            $browser3->visit(new Login)
                ->submit($this->adminUser->email, 'password')
                ->pause(2000)
                ->waitFor('#library-menu')
                ->clickLink('Library')
                ->pause(5000)
                ->waitFor('#title')
                ->assertPathis('/library')
                ->assertSeeIn('#title', 'Library');
        });
    }

    /**
     * Checks the dashboard text and links are working for all Student, Module Tutor and Admins
     *
     * @test
     * @throws \Throwable
     */
    public function test_dashboard_menu()
    {
        $this->browse(function ($browser1,$browser2,$browser3) {
            $browser1->visit(new Login)
                ->submit($this->studentUser->email, 'password')
                ->pause(2000)
                ->waitFor('#dashboard-menu')
                ->clickLink('Dashboard')
                ->pause(1000)
                ->assertPathis('/home')
                ->assertSeeIn('#title', 'Dashboard');

            $browser2->visit(new Login)
                ->submit($this->moduleTutorUser->email, 'password')
                ->pause(2000)
                ->waitFor('#dashboard-menu')
                ->clickLink('Dashboard')
                ->pause(1000)
                ->assertPathis('/home')
                ->assertSeeIn('#title', 'Dashboard');

            $browser3->visit(new Login)
                ->submit($this->adminUser->email, 'password')
                ->pause(2000)
                ->waitFor('#dashboard-menu')
                ->clickLink('Dashboard')
                ->pause(1000)
                ->assertPathis('/home')
                ->assertSeeIn('#title', 'Dashboard');
        });
    }

    /**
     * Checks the reading session and quiz score text and links are working for all Student, Module Tutor and Admins
     *
     * @test
     * @throws \Throwable
     */
    public function test_reading_session_quiz_score_menu()
    {
        $this->browse(function ($browser1,$browser2,$browser3) {
            $browser1->visit(new Login)
                ->submit($this->studentUser->email, 'password')
                ->pause(2000)
                ->waitFor('#reading-sessions-quiz-scores-menu')
                ->clickLink('Reading Sessions and Quiz Scores')
                ->pause(1000)
                ->assertPathis('/readingQuizScores')
                ->assertSeeIn('#title', 'Full list of scores and reading times');

            $browser2->visit(new Login)
                ->submit($this->moduleTutorUser->email, 'password')
                ->pause(2000)
                ->waitFor('#reading-sessions-quiz-scores-menu')
                ->clickLink('Reading Sessions and Quiz Scores')
                ->pause(1000)
                ->assertPathis('/readingQuizScores')
                ->assertSeeIn('#title', 'Full list of scores and reading times');

            $browser3->visit(new Login)
                ->submit($this->adminUser->email, 'password')
                ->pause(2000)
                ->waitFor('#reading-sessions-quiz-scores-menu')
                ->clickLink('Reading Sessions and Quiz Scores')
                ->pause(1000)
                ->assertPathis('/readingQuizScores')
                ->assertSeeIn('#title', 'Full list of scores and reading times');
        });
    }

    /**
     * Checks the extensive reading text and links are working for Student, Module Tutor and Admins
     *
     * @test
     * @throws \Throwable
     */
    public function test_extensive_reading_menu()
    {
        $this->browse(function ($browser1,$browser2,$browser3) {
            $browser1->visit(new Login)
                ->submit($this->studentUser->email, 'password')
                ->pause(2000)
                ->waitFor('#extensive-reading-menu')
                ->clickLink('Extensive Reading')
                ->waitFor('#extensive-reading-home-menu')
                ->clickLink('Home')
                ->pause(5000)
                ->waitFor('#title')
                ->assertPathis('/extensiveReading')
                ->assertSeeIn('#title', 'Extensive Reading')
                ->clickLink('View all categories')
                ->pause(5000)
                ->waitFor('#title')
                ->assertPathis('/extensiveReading/categories')
                ->assertSeeIn('#title', 'Extensive Reading Categories');

            $browser2->visit(new Login)
                ->submit($this->moduleTutorUser->email, 'password')
                ->pause(2000)
                ->waitFor('#extensive-reading-menu')
                ->clickLink('Extensive Reading')
                ->waitFor('#extensive-reading-home-menu')
                ->clickLink('Home')
                ->pause(5000)
                ->waitFor('#title')
                ->assertPathis('/extensiveReading')
                ->assertSeeIn('#title', 'Extensive Reading')
                ->clickLink('View all categories')
                ->pause(5000)
                ->waitFor('#title')
                ->assertPathis('/extensiveReading/categories')
                ->assertSeeIn('#title', 'Extensive Reading Categories')
                ->clickLink('Create a category')
                ->pause(5000)
                ->waitFor('#title')
                ->assertPathis('/extensiveReading/create')
                ->assertSeeIn('#title', 'Create a category');

            $browser3->visit(new Login)
                ->submit($this->adminUser->email, 'password')
                ->pause(2000)
                ->waitFor('#extensive-reading-menu')
                ->clickLink('Extensive Reading')
                ->waitFor('#extensive-reading-home-menu')
                ->clickLink('Home')
                ->pause(5000)
                ->waitFor('#title')
                ->assertPathis('/extensiveReading')
                ->assertSeeIn('#title', 'Extensive Reading')
                ->clickLink('View all categories')
                ->pause(5000)
                ->waitFor('#title')
                ->assertPathis('/extensiveReading/categories')
                ->assertSeeIn('#title', 'Extensive Reading Categories')
                ->clickLink('Create a category')
                ->pause(5000)
                ->waitFor('#title')
                ->assertPathis('/extensiveReading/create')
                ->assertSeeIn('#title', 'Create a category');
        });
    }
    /**
     * Checks the textbook text and links are working for Module Tutor and Admins
     *
     * @test
     * @throws \Throwable
     */
    public function test_textbooks_menu()
    {
        $this->browse(function ($browser1, $browser2) {

            //Module Tutor User
            $browser1->visit(new Login)
                ->submit($this->moduleTutorUser->email, 'password')
                ->pause(2000)
                ->waitFor('#textbooks-menu')
                ->clickLink('Textbooks')
                ->waitFor('#create-textbook-menu')
                ->clickLink('Create a textbook')
                ->waitFor('#title')
                ->pause(1000)
                ->assertPathis('/textbook/create')
                ->assertSeeIn('#title', 'Create textbook');

            //Admin User
            $browser2->visit(new Login)
                ->submit($this->adminUser->email, 'password')
                ->pause(2000)
                ->waitFor('#textbooks-menu')
                ->clickLink('Textbooks')
                ->waitFor('#create-textbook-menu')
                ->clickLink('Create a textbook')
                ->waitFor('#title')
                ->pause(1000)
                ->assertPathis('/textbook/create')
                ->assertSeeIn('#title', 'Create textbook');
        });
    }

    /**
     * Checks the Texts text and links are working for Module Tutor and Admins
     *
     * @test
     * @throws \Throwable
     */
    public function test_texts_menu()
    {
        $this->browse(function ($browser1, $browser2) {

            //Module Tutor User
            $browser1->visit(new Login)
                ->submit($this->moduleTutorUser->email, 'password')
                ->pause(2000)
                ->waitFor('#texts-menu')
                ->clickLink('Texts')
                ->waitFor('#add-a-text-menu')
                ->clickLink('Add a text')
                ->waitFor('#title')
                ->pause(1000)
                ->assertPathis('/text/create')
                ->assertSeeIn('#title', 'Upload text');

            //Admin User
            $browser2->visit(new Login)
                ->submit($this->adminUser->email, 'password')
                ->pause(2000)
                ->waitFor('#texts-menu')
                ->clickLink('Texts')
                ->waitFor('#add-a-text-menu')
                ->clickLink('Add a text')
                ->waitFor('#title')
                ->pause(1000)
                ->assertPathis('/text/create')
                ->assertSeeIn('#title', 'Upload text');
        });
    }

    /**
     * Checks the year groups menu in the side bar menu for a Admin User.
     * Checks if the texts/links are working and visible.
     * Checks it goes to the right pages and checks the title.
     *
     * @test
     * @throws \Throwable
     */
    public function test_admin_year_groups_menu()
    {
        $this->browse(function ($browser ) {
            $browser->visit(new Login)
                ->submit($this->adminUser->email, 'password')
                ->pause(2000)
                ->waitFor('#year-groups-menu')
                ->clickLink('Year Groups')
                ->waitFor('#title')
                ->pause(2000)
                ->assertPathis('/yearGroup')
                ->assertSeeIn('#title', 'Add Year Group');
        });
    }

    /**
     * Expands all the menus in the users section in the side bar menu for a Admin User.
     * Checks if the texts/links are working and visible.
     * Checks it goes to the right pages and checks the title.
     *
     * @test
     * @throws \Throwable
     */
    public function test_admin_users_menu()
    {
        $this->browse(function ($browser ) {
            $browser->visit(new Login)
                ->submit($this->adminUser->email, 'password')
                ->pause(2000)
                ->waitFor('#users-menu')
                ->clickLink('Users')
                ->waitFor('#view-all-users-menu')
                ->clickLink('View all users')
                ->waitFor('#title')
                ->pause(2000)
                ->assertPathis('/admin/users')
                ->assertSeeIn('#title', 'All Users');
        });
    }
}
