<?php

use Illuminate\Database\Seeder;
use App\YearGroup;
use Illuminate\Support\Facades\Schema;

class YearGroupsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        YearGroup::create(['name' => 'Year 1']);
        YearGroup::create(['name' => 'Year 2']);
        YearGroup::create(['name' => 'Year 3']);

        //Create fake years
        factory(YearGroup::class, 3)->create();

    }
}
