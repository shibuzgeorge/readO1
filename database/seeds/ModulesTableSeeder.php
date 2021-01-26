<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use App\Module;
use App\YearGroup;

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

        $year3 = YearGroup::where('name', 'Year 3')->first();

        Module::create(['name' => 'Software Project Management', 'module_code' => 'CS3360', 'year_group_id' => $year3->id]);
        Module::create(['name' => 'Computational Intelligence', 'module_code' => 'CS3910', 'year_group_id' => $year3->id]);
        Module::create(['name' => 'Enterprise Application Technology', 'module_code' => 'CS3160', 'year_group_id' => $year3->id]);
        Module::create(['name' => 'Information Security', 'module_code' => 'CS3190', 'year_group_id' => $year3->id]);

    }
}
