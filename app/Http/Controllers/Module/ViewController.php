<?php

namespace App\Http\Controllers\Module;

use App\Http\Controllers\Controller;
use App\Module;
use App\Textbook;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class ViewController extends Controller
{


    public function __construct()
    {
        $this->middleware('admin')->only('create');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();
        $user_role = $user->roles()->first();
        if($user_role->name === 'Admin' || $user_role->name === 'Module Tutor'){
            $modules = Module::all();
        }else{
            $modules = $user->modules()->get();
        }

        return response()->json($modules);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $modules = Module::all();
        return response()->json($modules);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        Module::create(['name' => $request->module_name, 'module_code' => $request->module_code, 'module_year' => $request->module_year]);
        $modules = Module::all();

        return response()->json($modules);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Module  $module
     * @return \Illuminate\Http\Response
     */
    public function show(int $module)
    {
        $m = Module::find($module);
        $textbook = $m->textbooks()->get();
        return response()->json([
            'name' => $m->name,
            'code' => $m->module_code,
            'year' => $m->module_year,
            'textbooks' => $textbook
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Module  $module
     * @return \Illuminate\Http\Response
     */
    public function edit(int $module)
    {
       $m = Module::find($module);
        return response()->json([
            'name' => $m->name,
            'code' => $m->module_code,
            'year' => $m->module_year,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Module  $module
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, int $module)
    {
        $m = Module::find($module);
        $m->name = $request->module_name;
        $m->module_code = $request->module_code;
        $m->module_year = $request->module_year;
        $m->save();
        return response()->json($module);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Module  $module
     * @return \Illuminate\Http\Response
     */
    public function destroy(int $module)
    {
        $m = Module::find($module);
        $m->delete();

        return response()->json($module);
    }
}
