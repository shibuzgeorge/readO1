<?php

namespace App\Http\Controllers;

use App\YearGroup;
use Illuminate\Http\Request;

class YearGroupController extends Controller
{


    public function __construct()
    {
        $this->middleware('admin');
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
        $year_group = YearGroup::find($id);
        return response()->json($year_group);
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
        $year_group = YearGroup::find($id);
        $year_group->name = $request->input('name');
        $year_group->save();
        return response()->json(['Success' => 'Successfully Updated!']);
    }

    /**
     * Remove the specified year group from database.
     *
     * @param $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $year_group = YearGroup::find($id);
        $year_group->delete();

        return response()->json(['Success' => 'Successfully Deleted!']);
    }

}
