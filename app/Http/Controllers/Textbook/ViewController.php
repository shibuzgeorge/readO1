<?php

namespace App\Http\Controllers\Textbook;

use App\Http\Controllers\Controller;
use App\Module;
use App\Textbook;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class ViewController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();
        $user_role = $user->roles()->first();
        if($user_role->name === 'Admin'){
            $textbooks = Textbook::all();
            return response()->json($textbooks);
        }else  if($user_role->name === 'Module Tutor'){
            $textbooks = Textbook::all();
            return response()->json($textbooks);
        }else  if($user_role->name === 'User'){
            $textbooks = Textbook::all();
            return response()->json($textbooks);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param
     * @return \Illuminate\Http\Response
     */
    public function show(int $textbook)
    {
        $m = Textbook::find($textbook);
        return response()->json([
            'title' => $m->title,
            'description' => $m->description,
            'file' => $m->file,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        //
    }
}
