<?php

namespace App\Http\Controllers;

use App\ExtensiveReadingCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

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
        //$this->middleware('role:Admin')->only(['create', 'store', 'destroy', 'getAllModuleTutors', 'assignModuleTutors']);
        //$this->middleware('role:Admin,Module Tutor')->only(['getUsersForModule', 'assignStudents', 'getAllStudents', 'update', 'edit']);
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
     * Store the newly created category.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:extensive_reading_categories',
            'description' => 'required|max:20',
        ]);

        ExtensiveReadingCategory::create(['name' => $request->name, 'description' => $request->description]);


        return response()->json(['Success' => 'Successfully created the category']);
    }

    public function edit($id){
        $erc = ExtensiveReadingCategory::find($id);
        $textbooks = $erc->textbooks()->get();
        return response()->json([
            'name' => $erc->name,
            'description' => $erc->description,
            'textbooks' => $textbooks
        ]);
    }

    public function show($id){
        $erc = ExtensiveReadingCategory::find($id);
        $textbooks = $erc->textbooks()->get();
        return response()->json([
            'name' => $erc->name,
            'description' => $erc->description,
            'textbooks' => $textbooks
        ]);
    }
}
