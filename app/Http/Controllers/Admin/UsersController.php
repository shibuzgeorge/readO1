<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\User;
use App\Role;
use Illuminate\Http\Request;

class UsersController extends Controller
{

    /**
     *
     * Applies middleware to all methods to restrict access.
     * Only admins are allowed.
     *
     * UsersController constructor.
     */
    public function __construct()
    {
        $this->middleware('role:Admin');
    }

    /**
     * Returns the all users and roles
     *
     * @return \Illuminate\Http\JsonResponse
     *
     */
    public function index(){
        $users = User::with('role')->get();
        $roles = Role::all();
        return response()->json([
            'users' => $users,
            'roles' => $roles
        ]);
    }

    /**
     * Create a form for a new user to be created.
     *
     * @return void
     */
    public function create()
    {
        //
    }

    /**
     * Displays the form to edit a user.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {

        $roles = Role::all();

        $user_role = $user->role;

        return response()->json([
            'user' => $user,
            'user_role' => $user_role,
            'roles' => $roles
        ]);
    }

    /**
     * Update the specified user role and saves to database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        $user->role()->associate($request->checked);
        $user->save();

        return response()->json($request->checked);

    }

    /**
     * Remove the specified user from the database.
     *
     * @param  \App\User $user
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(User::with('role')->get());
    }
}
