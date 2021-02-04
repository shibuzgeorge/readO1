<?php

use Illuminate\Database\Seeder;
use App\ModuleTextbook;

class ModuleTextbookTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(ModuleTextbook::class, 10)->create();
    }
}
