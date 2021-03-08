<?php

namespace Tests\Browser\Pages;

class Module extends Page
{
    /**
     * Get the URL for the page.
     *
     * @return string
     */
    public function url()
    {
        return '/module';
    }

    /**
     * Clicks create button on the module page
     *
     * @param  \Laravel\Dusk\Browser $browser
     * @return void
     * @throws \Facebook\WebDriver\Exception\TimeOutException
     */
    public function createButton($browser)
    {
        $browser->waitFor('#create', 10)
        ->press('+ Create Module')
        ->assertPathis('/module/create');
    }

    /**
     * Clicks year group button on the module page
     *
     * @param  \Laravel\Dusk\Browser $browser
     * @return void
     * @throws \Facebook\WebDriver\Exception\TimeOutException
     */
    public function yearGroupButton($browser)
    {
        $browser->waitFor('#yearGroup', 10)
            ->press('+ Add Year Group')
            ->assertPathis('/yearGroup');
    }

    /**
     * Clicks create button on the module page
     *
     * @param  \Laravel\Dusk\Browser $browser
     * @param $name
     * @param $code
     * @param $yearGroup
     * @return void
     */
    public function submitCreateForm($browser, $name, $code, $yearGroup)
    {
        $browser->type('name', $name)
        ->type('code', $code)
        ->select('year_group', $yearGroup)
        ->press('Add')
        ->pause(1000);

    }
}
