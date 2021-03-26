<?php

namespace App\Http\Controllers;

use App\YearGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class YearGroupController extends Controller
{


    public function __construct()
    {
        $this->middleware('role:Admin')->only(['edit','update', 'store', 'destroy']);
    }

    /**
     * Display a list of all year groups in the database
     *
     * @return \Illuminate\Http\Response json
     */
    public function index()
    {
        $year_groups = YearGroup::all();
        return response()->json($year_groups);
    }

    /**
     * Returns the year group based on the id
     *
     * @param $id
     * @return \Illuminate\Http\Response json
     */
    public function edit($id)
    {
        $year_group = YearGroup::findorFail($id);
        return response()->json($year_group);
    }

    /**
     * Returns all modules for a particular year group
     *
     * @param $year_group_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getModulesForYearGroup($year_group_id)
    {
        $user = Auth::user();
        $user_role = $user->role;
        if($user_role->name === 'Admin'){
            $yearGroup = YearGroup::findOrFail($year_group_id);
            $modules = $yearGroup->module()->has('textbooks')->with('textbooks')->get();
        } else{
            $module_ids = $user->modules()->get()->pluck('id');
            $year = YearGroup::findOrFail($year_group_id);
            $modules = $year->module()->whereIn('modules.id', $module_ids)->has('textbooks')->with('textbooks')->get();
        }
        return response()->json($modules);
    }

    /**
     * Updates the year group based on the id given
     *
     * @param $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response json
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'edit_name' => 'required|unique:year_groups,name,'.$id,
        ]);

        $year_group = YearGroup::findorFail($id);
        $year_group->name = $request->input('edit_name');
        $year_group->save();
        return response()->json(['Success' => 'Successfully updated the year group!']);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:year_groups',
        ]);

        YearGroup::create(['name' => $request->name]);
        return response()->json(['Success' => 'Successfully created the year group!']);
    }

    /**
     * Remove the specified year group from database.
     *
     * @param $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $year_group = YearGroup::findorFail($id);
        $year_group->delete();

        return response()->json(['Success' => 'Successfully Deleted!']);
    }

}
