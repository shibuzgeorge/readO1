<?php

namespace App\Http\Controllers;

use App\Module;
use App\Role;
use App\Textbook;
use App\YearGroup;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class ModuleController extends Controller
{
    /**
     *
     * Applies middleware to some of the methods to restrict access.
     *
     * ModuleController constructor.
     *
     */
    public function __construct()
    {
        $this->middleware('role:Admin')->only(['create', 'store', 'destroy', 'getAllModuleTutors', 'assignModuleTutors']);
        $this->middleware('role:Admin,Module Tutor')->only(['getUsersForModule', 'assignStudents', 'getAllStudents', 'update', 'edit']);
    }

    /**
     * Displays all the modules.
     * Only Admins can access all modules.
     * Students and Module Tutors have to be assigned to the modules to have access.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $user = Auth::user();
        $user_role = $user->role;
        if($user_role->name === 'Admin'){
            $modules = Module::with('yearGroup')->get();
        }else{
            $modules = $user->modules()->with('yearGroup')->get();
        }

        return response()->json($modules);

    }

    /**
     * Displays the create form for a new Module.
     * Returns all current modules and all current year groups.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function create()
    {
        $modules = Module::with('yearGroup')->get();
        $unassigned = Textbook::doesntHave('modules')->doesntHave('extensiveReadingCategories')->get();
        $year_groups = YearGroup::all();
        return response()->json([
            'modules' => $modules,
            'year_groups'=> $year_groups,
            'unassigned' => $unassigned
        ]);
    }

    /**
     * Store a newly created Module with the Year Group Id.
     *
     * @param  \Illuminate\Http\Request POST request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $module = Module::create([
            'name' => $request->module_name,
            'module_code' => $request->module_code,
            'year_group_id' => $request->module_year
        ]);

        $module->textbooks()->sync($request->checked);
        return response()->json(['Success' => 'Successfully Created!']);
    }

    /**
     * Returns all users which are assigned to a module associated to the module_id
     *
     * @param $module_id
     * @return \Illuminate\Http\JsonResponse
     *
     */
    public function getUsersForModule($module_id)
    {

        $modules = Module::findOrFail($module_id);
        $users = $modules->users()->get();
        return response()->json($users);
    }

    /**
     * Returns all the students in the database.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllStudents(){
        $users = Role::where('name', 'Student')->first()->users()->get();
        return response()->json($users);
    }

    /**
     * Assigns the Students to the module associated to the module_id passed by the POST request.
     * Performs a sync of the students and then reassigns the Module Tutors.
     *
     * @param Request $request
     * @param $module_id
     * @return \Illuminate\Http\JsonResponse
     *
     */
    public function assignStudents(Request $request, $module_id)
    {
        $modules = Module::findOrFail($module_id);

        $module_tutors =  $modules->users()->whereHas(
            'role', function($q){
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

    /**
     *
     * Assigns the Module Tutors to the module associated to the module_id passed by the POST request.
     * Performs a sync of the Module Tutors and then reassigns the Students.
     *
     * @param Request $request
     * @param $module_id
     * @return \Illuminate\Http\JsonResponse
     *
     */
    public function assignModuleTutors(Request $request, $module_id)
    {
        $modules = Module::findOrFail($module_id);

        $students =  $modules->users()->whereHas(
            'role', function($q){
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

    /**
     * Returns all the Module Tutors in the database
     *
     * @return \Illuminate\Http\JsonResponse
     */
      public function getAllModuleTutors()
      {
        $moduleTutors = Role::where('name', 'Module Tutor')->first()->users()->get();
        return response()->json($moduleTutors);
      }

    /**
     * Returns the module associated to the module_id and based on the role.
     * Admins can view any module.
     * Student and Module Tutors must be assigned to that module to have access.
     * Module Name, Module Code, Textbooks are returned
     *
     * @param  $module_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($module_id)
    {
        $m = Module::find($module_id);

        if(Auth::user()->role->name != 'Admin'){
            $checkIfUserHasPermissionToView = Auth::user()->modules()->find($module_id);
            if($checkIfUserHasPermissionToView != null){
                $textbook = $m->textbooks()->get();
                return response()->json([
                    'name' => $m->name,
                    'code' => $m->module_code,
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
                    'textbooks' => $textbook
                ]);
            } else {
                return response()->json(['Error' => 'Module not found!']);
            }
        }
    }

    /**
     * Displays the edit form for a module associated to the module_id passed.
     * Returns the Module Name, Module Code, Module Year Group
     *
     * @param  $module_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit($module_id)
    {
        $m = Module::with('yearGroup')->find($module_id);
        $textbooks = $m->textbooks()->get();
        $unassigned = Textbook::doesntHave('modules')->doesntHave('extensiveReadingCategories')->get();

        if($m != null) {
            return response()->json([
                'name' => $m->name,
                'code' => $m->module_code,
                'year' => $m->yearGroup,
                'textbooks' => $textbooks,
                'unassigned' => $unassigned
            ]);
        }else{
            return response()->json(['Error' => 'Module not found!']);
        }
    }

    /**
     * Updates the module associated to the module_id based on the PUT request passed.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Module $module_id module id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $module_id)
    {
        $m = Module::findOrFail($module_id);
        $m->name = $request->module_name;
        $m->module_code = $request->module_code;
        $m->year_group_id = $request->module_year;
        $m->save();
        $m->textbooks()->sync($request->checked);
        return response()->json(['Success' => 'Successfully Updated!']);
    }

    /**
     * Removes the module associated to the module_id
     *
     * @param  $module_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($module_id)
    {
        $m = Module::findOrFail($module_id);
        $m->delete();

        return response()->json($module_id);
    }
}
