<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use App\Module;

class ModulesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Schema::disableForeignKeyConstraints();
        Module::truncate();
        Schema::enableForeignKeyConstraints();

        Module::create(['name' => 'Software Project Management', 'module_code' => 'CS3360', 'module_year' => 'Year 3']);
        Module::create(['name' => 'Computational Intelligence', 'module_code' => 'CS3960', 'module_year' => 'Year 3']);
        Module::create(['name' => 'Enterprise Application Technology', 'module_code' => 'CS3160', 'module_year' => 'Year 3']);
        Module::create(['name' => 'Information Security', 'module_code' => 'CS3190', 'module_year' => 'Year 3']);
    }
}
