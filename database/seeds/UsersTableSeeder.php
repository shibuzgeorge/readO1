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

        DB::table('module_user')->truncate();

        $adminRole = Role::where('name', 'Admin')->first();
        $moduleTutorRole = Role::where('name', 'Module Tutor')->first();
        $userRole = Role::where('name', 'Student')->first();

        $ci = Module::where('module_code', 'CS3910')->first();
        $eat = Module::where('module_code', 'CS3160')->first();
        $spm = Module::where('module_code', 'CS3360')->first();
        $is = Module::where('module_code', 'CS3190')->first();

        User::create([
            'name'=> 'Admin User',
            'email' => 'admin@admin.com',
            'password' => Hash::make('password'),
            'role_id' => $adminRole->id
        ]);

        $moduleTutor1 = User::create([
            'name'=> 'Module Tutor 1',
            'email' => 'ModuleTutor1@ModuleTutor1.com',
            'password' => Hash::make('password'),
            'role_id' => $moduleTutorRole->id
        ]);

        $moduleTutor2 = User::create([
            'name'=> 'Module Tutor 2',
            'email' => 'ModuleTutor2@ModuleTutor2.com',
            'password' => Hash::make('password'),
            'role_id' => $moduleTutorRole->id
        ]);

        $moduleTutor3 = User::create([
            'name'=> 'Module Tutor 3',
            'email' => 'ModuleTutor3@ModuleTutor3.com',
            'password' => Hash::make('password'),
            'role_id' => $moduleTutorRole->id
        ]);

        $student1 = User::create([
            'name'=> 'Student 1',
            'email' => 'student1@student1.com',
            'password' => Hash::make('password'),
            'role_id' => $userRole->id
        ]);

        $student2 = User::create([
            'name'=> 'Student 2',
            'email' => 'student2@student2.com',
            'password' => Hash::make('password'),
            'role_id' => $userRole->id
        ]);

        $student3 = User::create([
            'name'=> 'Student 3',
            'email' => 'student3@student3.com',
            'password' => Hash::make('password'),
            'role_id' => $userRole->id
        ]);

        $student1->modules()->sync([$ci->id, $spm->id, $eat->id, $is->id]);
        $student2->modules()->sync([$spm->id, $eat->id]);
        $student3->modules()->sync([$ci->id, $spm->id, $is->id]);
        $moduleTutor1->modules()->sync([$ci->id, $spm->id, $eat->id, $is->id]);
        $moduleTutor2->modules()->sync([$spm->id, $eat->id]);
        $moduleTutor3->modules()->sync([$ci->id, $spm->id, $is->id]);
    }
}
