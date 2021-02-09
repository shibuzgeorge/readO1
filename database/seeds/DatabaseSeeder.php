<?php

use Illuminate\Database\Seeder;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(YearGroupsSeeder::class);
        $this->call(ExtensiveReadingCategoriesTableSeeder::class);
        $this->call(ExtensiveReadingCategoryTextbookTableSeeder::class);
        $this->call(YearGroupsSeeder::class);
        $this->call(ModulesTableSeeder::class);
        $this->call(TextbooksTableSeeder::class);
        $this->call(TextsTableSeeder::class);
        $this->call(RolesTableSeeder::class);
        $this->call(UsersTableSeeder::class);
    }
}
