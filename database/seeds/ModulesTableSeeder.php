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

        $spm = Module::create(['name' => 'Software Project Management', 'module_code' => 'CS3360', 'year_group_id' => 1]);
        $ci = Module::create(['name' => 'Computational Intelligence', 'module_code' => 'CS3960', 'year_group_id' => 1]);
        $eat = Module::create(['name' => 'Enterprise Application Technology', 'module_code' => 'CS3160', 'year_group_id' => 1]);
        $is = Module::create(['name' => 'Information Security', 'module_code' => 'CS3190', 'year_group_id' => 1]);

        $year3 = YearGroup::where('name', 'Year 3')->first();

            $spm->yearGroup()->associate($year3)->save();
            $ci->yearGroup()->associate($year3)->save();
            $eat->yearGroup()->associate($year3)->save();
            $is->yearGroup()->associate($year3)->save();

    }
}
