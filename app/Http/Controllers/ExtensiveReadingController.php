<?php

namespace App\Http\Controllers;

use App\ExtensiveReadingCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
     * Displays all the modules.
     * Only Admins can access all modules.
     * Students and Module Tutors have to be assigned to the modules to have access.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $erc = ExtensiveReadingCategory::all();
        return response()->json($erc);
    }
}
