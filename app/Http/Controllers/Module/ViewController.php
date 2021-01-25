<?php

namespace App\Http\Controllers\Module;

use App\Http\Controllers\Controller;
use App\Module;
use App\Role;
use App\YearGroup;
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
        if($user_role->name === 'Admin'){
            $modules = Module::with('yearGroup')->get();
        }else{
            $modules = $user->modules()->with('yearGroup')->get();
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
        $year_groups = YearGroup::all();
        return response()->json([
            'modules' => $modules,
            'year_groups' => $year_groups,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        Module::create(['name' => $request->module_name, 'module_code' => $request->module_code, 'year_group_id' => $request->module_year]);
        return response()->json(['Success' => 'Successfully Created!']);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function addYearGroup(Request $request)
    {
        YearGroup::create(['name' => $request->year_group]);
        return response()->json(['Success' => 'Successfully created the year group!']);
    }

    public function getUsersForModule(int $module_id)
    {
        $modules = Module::find($module_id);

        $users = $modules->users()->get();

        return response()->json($users);
    }

    public function getModuleById(int $module_id)
    {
        $modules = Module::find($module_id);
        return response()->json($modules);
    }

    public function getAllStudents(){
        $users = Role::where('name', 'Student')->first()->users()->get();
        return response()->json($users);
    }

    public function assignStudents(Request $request, int $module_id){

        $modules = Module::find($module_id);

        $module_tutors =  $modules->users()->whereHas(
            'roles', function($q){
            $q->where('name', 'Module Tutor');
        })->pluck('users.id')
            ->toArray();

        if(count($request->all()) === 0){
            $modules->users()->detach();
            $modules->users()->attach($module_tutors);
            return response()->json(['Success' => 'Successfully designed']);
        }else {
            $modules->users()->sync($request->all());
            $modules->users()->attach($module_tutors);
            return response()->json(['Success' => 'Successfully assigned']);
        }

    }

    public function assignModuleTutors(Request $request, int $module_id){

        $modules = Module::find($module_id);

        $students =  $modules->users()->whereHas(
            'roles', function($q){
            $q->where('name', 'Student');
        })->pluck('users.id')
            ->toArray();

        if(count($request->all()) === 0){
            $modules->users()->detach();
            $modules->users()->attach($students);
            return response()->json(['Success' => 'Successfully designed']);
        }else {
            $modules->users()->sync($request->all());
            $modules->users()->attach($students);
            return response()->json(['Success' => 'Successfully assigned']);
        }

    }

    public function getAllModuleTutors(){
        $moduleTutors = Role::where('name', 'Module Tutor')->first()->users()->get();
        return response()->json($moduleTutors);
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
        if(Auth::user()->roles()->first()->name != 'Admin'){
            $checkIfUserHasPermissionToView = Auth::user()->modules()->find($module);
            if($checkIfUserHasPermissionToView != null){
                $textbook = $m->textbooks()->get();
                return response()->json([
                    'name' => $m->name,
                    'code' => $m->module_code,
                    'year' => $m->module_year,
                    'textbooks' => $textbook
                ]);
            }else{
                return response()->json(['Error' => 'Permission denied to view module!']);
            }
        }else{

            if($m != null){
                $textbook = $m->textbooks()->get();
                return response()->json([
                    'name' => $m->name,
                    'code' => $m->module_code,
                    'year' => $m->module_year,
                    'textbooks' => $textbook
                ]);
            }else{
                return response()->json(['Error' => 'Module not found!']);
            }

        }


    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Module  $module
     * @return \Illuminate\Http\Response
     */
    public function edit(int $module)
    {
       $m = Module::with('yearGroup')->find($module);
        if($m != null) {
            return response()->json([
                'name' => $m->name,
                'code' => $m->module_code,
                'year' => $m->yearGroup,
            ]);
        }else{
            return response()->json(['Error' => 'Module not found!']);
        }
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
        $m->year_group_id = $request->module_year;
        $m->save();

        return response()->json(['Success' => 'Successfully Updated!']);
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
