<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use App\User;
use App\Role;

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

        $adminRole = Role::where('name', 'Admin')->first();
        $moduleTutorRole = Role::where('name', 'Module Tutor')->first();
        $userRole = Role::where('name', 'User')->first();

        $admin = User::create([
            'name'=> 'Admin User',
            'email' => 'admin@admin.com',
            'password' => Hash::make('password')
        ]);

        $moduleTutor = User::create([
            'name'=> 'Module Tutor User',
            'email' => 'ModuleTutor@ModuleTutor.com',
            'password' => Hash::make('password')
        ]);

        $user = User::create([
            'name'=> 'Generic User',
            'email' => 'user@user.com',
            'password' => Hash::make('password')
        ]);

        $admin->roles()->attach($adminRole);
        $moduleTutor->roles()->attach($moduleTutorRole);
        $user->roles()->attach($userRole);
    }
}
