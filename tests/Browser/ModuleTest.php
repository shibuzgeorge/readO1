<?php

namespace Tests\Browser;

use Tests\Browser\Pages\Module;
use Tests\DuskTestCase;
use Tests\Browser\Pages\Login;
use App\User;

class ModuleTest extends DuskTestCase
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
     * Test the buttons for an admin.
     *
     * @test
     * @throws \Throwable
     */
    public function test_buttons_for_an_admin()
    {
        $this->browse(function ($browser) {
            $browser->visit(new Login)
                ->submit($this->adminUser->email, 'password')
                ->pause(2000)
                ->visit(new Module)
                ->pause(1000)
                ->createButton()
                ->visit(new Module)
                ->pause(1000)
                ->yearGroupButton();
        });
    }

    /**
     * Create a new module for admin
     *
     * @test
     * @throws \Throwable
     */
    public function test_create_new_module_for_admin()
    {
        $this->browse(function ($browser, $broswer2) {
            $browser->visit(new Login)
                ->submit($this->adminUser->email, 'password')
                ->pause(2000)
                ->visit(new Module)
                ->pause(1000)
                ->createButton()
                ->pause(10000)
                ->submitCreateForm('Testing', 'CS576', 1)
                ->pause(2000)
                ->assertSee('Successfully Created!');

            $broswer2->visit(new Login)
                ->submit($this->adminUser->email, 'password')
                ->pause(2000)
                ->visit(new Module)
                ->type('search', 'Testing')
                ->assertSee('Testing')
                ->assertSee('CS576')
                ->assertSee('Year 1');
        });
    }


}
