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
     * @test
     * @throws \Throwable
     */
    public function test_what_admins_see_in_side_menu()
    {
        $this->browse(function ($browser) {
            $browser->visit(new Login)
                ->submit($this->adminUser->email, 'password')
                ->pause(1000)
                //expands all menus for a student
                ->waitFor('#modules-menu')
                ->clickLink('Modules')
                ->waitFor('#textbooks-menu')
                ->clickLink('Textbooks')
                ->waitFor('#users-menu')
                ->clickLink('Users')
                ->pause(1000)
                //checks if these are visible on the side menu.
                ->assertSeeIn('#sidebar-wrapper', 'Dashboard')
                ->assertSeeIn('#sidebar-wrapper', 'Library')
                ->assertSeeIn('#sidebar-wrapper', 'Textbooks')
                ->assertSeeIn('#textbooks-expanded', 'Create a textbook')
                ->assertSeeIn('#sidebar-wrapper', 'Modules')
                ->assertSeeIn('#modules-expanded', 'All Modules')
                ->assertSeeIn('#modules-expanded', 'Assign Students')
                ->assertSeeIn('#modules-expanded', 'Create Module')
                ->assertSeeIn('#modules-expanded', 'Assign Module Tutors')
                ->assertSeeIn('#sidebar-wrapper', 'Users')
                ->assertSeeIn('#users-expanded', 'View all users')
                ->assertSeeIn('#users-expanded', 'Create a user');
        });
    }

    /**
     * @test
     * @throws \Throwable
     */
    public function test_what_module_tutors_see_in_side_menu()
    {
        $this->browse(function ($browser) {
            $browser->visit(new Login)
                ->submit($this->moduleTutorUser->email, 'password')
                ->pause(1000)
                //expands all menus for a student
                ->waitFor('#modules-menu')
                ->clickLink('Modules')
                ->waitFor('#textbooks-menu')
                ->clickLink('Textbooks')
                ->pause(1000)
                //checks if these are visible on the side menu.
                ->assertSeeIn('#sidebar-wrapper', 'Dashboard')
                ->assertSeeIn('#sidebar-wrapper', 'Library')
                ->assertSeeIn('#sidebar-wrapper', 'Textbooks')
                ->assertSeeIn('#textbooks-expanded', 'Create a textbook')
                ->assertSeeIn('#sidebar-wrapper', 'Modules')
                ->assertSeeIn('#modules-expanded', 'All Modules')
                ->assertSeeIn('#modules-expanded', 'Assign Students')
                //checks if these are not visible on the side menu.
                ->assertDontSeeIn('#sidebar-wrapper', 'Create Module')
                ->assertDontSeeIn('#sidebar-wrapper', 'Assign Module Tutors')
                ->assertDontSeeIn('#sidebar-wrapper', 'Users')
                ->assertDontSeeIn('#sidebar-wrapper', 'View all users')
                ->assertDontSeeIn('#sidebar-wrapper', 'Create a user');
        });
    }

    /**
     * @test
     * @throws \Throwable
     */
    public function test_what_students_see_in_side_menu()
    {
        $this->browse(function ($browser) {
            $browser->visit(new Login)
                ->submit($this->studentUser->email, 'password')
                ->pause(1000)
                //expands all menus for a student
                ->waitFor('#modules-menu')
                ->clickLink('Modules')
                ->pause(1000)
                //checks if these are visible on the page.
                ->assertSeeIn('#sidebar-wrapper', 'Dashboard')
                ->assertSeeIn('#sidebar-wrapper', 'Library')
                ->assertSeeIn('#sidebar-wrapper', 'Modules')
                ->assertSeeIn('#modules-expanded', 'My Modules')
                //checks if these are not visible on the page.
                ->assertDontSeeIn('#sidebar-wrapper', 'Textbooks')
                ->assertDontSeeIn('#sidebar-wrapper', 'Create a textbook')
                ->assertDontSeeIn('#sidebar-wrapper', 'All Modules')
                ->assertDontSeeIn('#sidebar-wrapper', 'Create Module')
                ->assertDontSeeIn('#sidebar-wrapper', 'Assign Module Tutors')
                ->assertDontSeeIn('#sidebar-wrapper', 'Assign Students')
                ->assertDontSeeIn('#sidebar-wrapper', 'Users')
                ->assertDontSeeIn('#sidebar-wrapper', 'View all users')
                ->assertDontSeeIn('#sidebar-wrapper', 'Create a user');
        });
    }

    /**
     * @test
     * @throws \Throwable
     */
    public function test_student_modules_menu()
    {
        $this->browse(function ($browser) {
            $browser->visit(new Login)
                ->submit($this->studentUser->email, 'password')
                ->waitFor('#modules-menu')
                ->clickLink('Modules')
                ->waitFor('#my-modules-menu')
                ->clickLink('My Modules')
                ->waitFor('#title-my-modules')
                ->assertPathis('/module')
                ->assertSeeIn('#title-my-modules', 'My Modules');
        });
    }

    /**
     * @test
     * @throws \Throwable
     */
    public function test_Module_Tutor_modules_menu()
    {
        $this->browse(function ($browser) {
            $browser->visit(new Login)
                ->submit($this->moduleTutorUser->email, 'password')
                ->waitFor('#modules-menu')
                ->clickLink('Modules')
                ->waitFor('#all-modules-menu')
                ->clickLink('All Modules')
                ->waitFor('#title-all-modules')
                ->assertPathis('/module')
                ->assertSeeIn('#title-all-modules', 'All Modules');
        });
    }

    /**
     * @test
     * @throws \Throwable
     */
    public function test_Admin_modules_menu()
    {
        $this->browse(function ($browser) {
            $browser->visit(new Login)
                ->submit($this->adminUser->email, 'password')
                ->waitFor('#modules-menu')
                ->clickLink('Modules')
                ->waitFor('#all-modules-menu')
                ->clickLink('All Modules')
                ->waitFor('#title-all-modules')
                ->assertPathis('/module')
                ->assertSeeIn('#title-all-modules', 'All Modules');
        });
    }
}
