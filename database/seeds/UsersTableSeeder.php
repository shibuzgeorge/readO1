<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use App\User;
use App\Role;
use App\Module;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Schema::disableForeignKeyConstraints();
        User::truncate();
        Schema::enableForeignKeyConstraints();

        DB::table('role_user')->truncate();
        DB::table('module_user')->truncate();

        $adminRole = Role::where('name', 'Admin')->first();
        $moduleTutorRole = Role::where('name', 'Module Tutor')->first();
        $userRole = Role::where('name', 'Student')->first();

        $ci = Module::where('module_code', 'CS3960')->first();
        $eat = Module::where('module_code', 'CS3160')->first();
        $spm = Module::where('module_code', 'CS3360')->first();

        $admin = User::create([
            'name'=> 'Admin User',
            'email' => 'admin@admin.com',
            'password' => Hash::make('password')
        ]);

        $moduleTutor1 = User::create([
            'name'=> 'Module Tutor 1',
            'email' => 'ModuleTutor1@ModuleTutor1.com',
            'password' => Hash::make('password')
        ]);

        $moduleTutor2 = User::create([
            'name'=> 'Module Tutor 2',
            'email' => 'ModuleTutor2@ModuleTutor2.com',
            'password' => Hash::make('password')
        ]);

        $moduleTutor3 = User::create([
            'name'=> 'Module Tutor 3',
            'email' => 'ModuleTutor3@ModuleTutor3.com',
            'password' => Hash::make('password')
        ]);

        $student1 = User::create([
            'name'=> 'Student 1',
            'email' => 'student1@student1.com',
            'password' => Hash::make('password')
        ]);

        $student2 = User::create([
            'name'=> 'Student 2',
            'email' => 'student2@student2.com',
            'password' => Hash::make('password')
        ]);

        $student3 = User::create([
            'name'=> 'Student 3',
            'email' => 'student3@student3.com',
            'password' => Hash::make('password')
        ]);

        $admin->roles()->attach($adminRole);

        $moduleTutor1->roles()->attach($moduleTutorRole);
        $moduleTutor2->roles()->attach($moduleTutorRole);
        $moduleTutor3->roles()->attach($moduleTutorRole);
        $student1->roles()->attach($userRole);
        $student2->roles()->attach($userRole);
        $student3->roles()->attach($userRole);

        $student1->modules()->attach($ci);
        $student2->modules()->attach($spm);
        $student3->modules()->attach($eat);
        $moduleTutor1->modules()->attach($ci);
        $moduleTutor2->modules()->attach($spm);
        $moduleTutor3->modules()->attach($eat);
    }
}
