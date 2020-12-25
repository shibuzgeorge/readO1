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
        }else{
            $textbooks = $user->modules()->has('textbooks')->
            with('textbooks')->get()->pluck('textbooks')->toArray();
            $textbooks = call_user_func_array('array_merge', $textbooks);
        }
        return response()->json($textbooks);
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
    public function edit(int $textbook)
    {
        $t = Textbook::find($textbook);
        $module = $t->modules()->first();
        return response()->json([
            'title' => $t->title,
            'description' => $t->description,
            'file' => $t->file,
            'module' => $module,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, int $textbook_id)
    {
        $file = null;
        $t = Textbook::find($textbook_id);
        if ($request->file('file')) {
            $file = base64_encode(file_get_contents($request->file('file')));
            $t->file = $file;
        }

        $t->title = $request->input('title');
        $t->description = $request->input('description');

        $t->save();
        return response()->json([
            'Success' => 'Successfully updated']
        );
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
