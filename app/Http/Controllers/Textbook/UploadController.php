<?php

namespace App\Http\Controllers\Textbook;

use App\Http\Controllers\Controller;
use App\Module;
use App\Textbook;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class UploadController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();
        $user_role = $user->role;

        if($user_role->name === 'Admin'){
            $modules = Module::all();
            return response()->json($modules);
        }else {
            $modules = $user->modules()->get();
            return response()->json($modules);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $file = null;

        if ($request->file('file')) {
            $file = base64_encode(file_get_contents($request->file('file')));
        }


        //data that will be inserted into new row in document table
        $data = [
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'file' => $file,

        ];

        //create a document using the POST data
       $textbook =  Textbook::create($data);
       $module = Module::find($request->input('module_id'));
       $textbook->modules()->attach($module);

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        //
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
