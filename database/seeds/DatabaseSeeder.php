<?php

use App\ExtensiveReadingCategory;
use App\ExtensiveReadingCategoryTextbook;
use App\Module;
use App\ModuleTextbook;
use App\Option;
use App\Question;
use App\Quiz;
use App\Role;
use App\Text;
use App\Textbook;
use App\User;
use App\YearGroup;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->truncate();
        $this->call(YearGroupsSeeder::class);
        $this->call(ExtensiveReadingCategoriesTableSeeder::class);
        $this->call(ExtensiveReadingCategoryTextbookTableSeeder::class);
        $this->call(ModulesTableSeeder::class);
        $this->call(TextbooksTableSeeder::class);
        $this->call(TextsTableSeeder::class);
        $this->call(QuizzesTableSeeder::class);
        $this->call(QuestionsTableSeeder::class);
        $this->call(OptionsTableSeeder::class);
        $this->call(RolesTableSeeder::class);
        $this->call(UsersTableSeeder::class);
    }

    /**
     *
     */
    protected function truncate()
    {
        Schema::disableForeignKeyConstraints();
        YearGroup::truncate();
        ExtensiveReadingCategory::truncate();
        ExtensiveReadingCategoryTextbook::truncate();
        Module::truncate();
        DB::table('module_user')->truncate();
        ModuleTextbook::truncate();
        Textbook::truncate();
        Text::truncate();
        Quiz::truncate();
        Question::truncate();
        Option::truncate();
        Role::truncate();
        User::truncate();
        Schema::enableForeignKeyConstraints();
    }
}
