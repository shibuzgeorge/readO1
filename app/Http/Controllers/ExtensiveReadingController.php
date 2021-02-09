<?php

namespace App\Http\Controllers;

use App\ExtensiveReadingCategory;
use App\Textbook;
use Illuminate\Http\Request;

class ExtensiveReadingController extends Controller
{
    /**
     *
     * Applies middleware to some of the methods to restrict access.
     *
     * ExtensiveReadingController constructor.
     *
     */
    public function __construct()
    {
        $this->middleware('role:Admin,Module Tutor')->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    /**
     * Displays all extensive reading categories with all textbooks.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $erc = ExtensiveReadingCategory::with('textbooks')->get();
        return response()->json($erc);
    }

    /**
     * Displays all the extensive reading categories
     * @return \Illuminate\Http\JsonResponse
     */
    public function categories()
    {
        $erc = ExtensiveReadingCategory::all();
        return response()->json($erc);
    }

    /**
     * Displays the create a category form to an Admin
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function create()
    {
        $erc = ExtensiveReadingCategory::all();
        return response()->json($erc);
    }

    /**
     * Store the newly created category and checks the validations.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:extensive_reading_categories',
        ]);

        ExtensiveReadingCategory::create(['name' => $request->name, 'description' => $request->description]);


        return response()->json(['Success' => 'Successfully created the category']);
    }

    /**
     * Edit page form with shows the name and description of the category to edit.
     * Also has checkboxes to show the current textbooks in the category and any unassigned textbook.
     *
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     *
     */
    public function edit($id){
        $erc = ExtensiveReadingCategory::find($id);
        $textbooks = $erc->textbooks()->get();
        $unassigned = Textbook::doesntHave('modules')->doesntHave('extensiveReadingCategories')->get();

        return response()->json([
            'name' => $erc->name,
            'description' => $erc->description,
            'textbooks' => $textbooks,
            'unassigned' => $unassigned,
        ]);
    }

    /**
     * Update the category based on the $id passed with validations and
     * updates the textbooks in the category.
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|unique:extensive_reading_categories,name,'.$id,
        ]);
        $erc = ExtensiveReadingCategory::findOrFail($id);
        $erc->name = $request->name;
        $erc->description = $request->description;
        $erc->save();
        $erc->textbooks()->sync($request->checked);
        return response()->json(['Success' => 'Successfully updated the category']);
    }

    /**
     * Displays the category with all the textbooks inside.
     *
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     *
     */
    public function show($id){
        $erc = ExtensiveReadingCategory::find($id);
        $textbooks = $erc->textbooks()->get();
        return response()->json([
            'name' => $erc->name,
            'description' => $erc->description,
            'textbooks' => $textbooks
        ]);
    }

    /**
     * Removes the specified category based on the $id passed.
     *
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     *
     */
    public function destroy($id){
        $erc = ExtensiveReadingCategory::findOrFail($id);
        $erc->delete();
        return response()->json(['Success' => 'Successfully deleted.']);
    }
}
