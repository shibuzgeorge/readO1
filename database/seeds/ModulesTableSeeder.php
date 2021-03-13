<?php

use App\ModuleTextbook;
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
        $year3 = YearGroup::where('name', 'Year 3')->first();

        Module::create(['name' => 'Software Project Management', 'module_code' => 'CS3360', 'year_group_id' => $year3->id]);
        Module::create(['name' => 'Computational Intelligence', 'module_code' => 'CS3910', 'year_group_id' => $year3->id]);
        Module::create(['name' => 'Enterprise Application Technology', 'module_code' => 'CS3160', 'year_group_id' => $year3->id]);
        Module::create(['name' => 'Information Security', 'module_code' => 'CS3190', 'year_group_id' => $year3->id]);
        Module::create(['name' => 'Data Mining', 'module_code' => 'CS3440', 'year_group_id' => $year3->id]);
        Module::create(['name' => 'Computer Animation', 'module_code' => 'CS2420', 'year_group_id' => $year3->id]);
        Module::create(['name' => 'Geographic Information Systems', 'module_code' => 'CS3210', 'year_group_id' => $year3->id]);
        Module::create(['name' => 'Testing and Reliable Software Engineering', 'module_code' => 'CS3270', 'year_group_id' => $year3->id]);

        factory(ModuleTextbook::class, 10)->create();
    }
}
