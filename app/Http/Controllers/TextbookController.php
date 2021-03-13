<?php

namespace App\Http\Controllers;

use App\ExtensiveReadingCategory;
use App\Module;
use App\Textbook;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class TextbookController extends Controller
{
    /**
     *
     * Applies middleware to some of the methods to restrict access.
     *
     * TextbookController constructor.
     *
     */
    public function __construct()
    {
        $this->middleware('role:Admin,Module Tutor')->only('create');
        $this->middleware('role:Admin,Module Tutor')->only('destroy');
        $this->middleware('role:Admin,Module Tutor')->only('update');
        $this->middleware('role:Admin,Module Tutor')->only('edit');
        $this->middleware('role:Admin,Module Tutor')->only('store');
    }

    /**
     * Returns the list of all textbooks based on the user role.
     * Only Admins have access to all textbooks
     * Students and Module Tutors have to be assigned to the module to see the textbook
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $user = Auth::user();
        $user_role = $user->role->name;
        if($user_role === 'Admin'){
            $textbooks = Textbook::all();
        }else{
            $textbooks = $user->modules()->has('textbooks')->
            with('textbooks')->get()->pluck('textbooks')->toArray();
            $textbooks = call_user_func_array('array_merge', $textbooks);
        }
        return response()->json($textbooks);
    }

    /**
     * Displays the textbook associated to the textbook_id passed.
     * Checks to see if the user has the permission to view the textbook.
     * Admins are allowed to view all textbooks.
     * Students and Module Tutors must have a module which has the textbook associated to the textbook_id.
     *
     * @param $textbook_id
     * @return \Illuminate\Http\Response
     */
    public function show($textbook_id)
    {
        $textbook = Textbook::find($textbook_id);

        if ($textbook == null) {
            return response()->json(['Error' => 'Textbook not found!']);
        }

        $checkIfUserHasPermissionToView = $this->checkPermission($textbook_id);

            if ($checkIfUserHasPermissionToView) {
                $texts = $textbook->texts()->get();
                if ($textbook->file != null) {
                    return response()->json([
                        'title' => $textbook->title,
                        'description' => $textbook->description,
                        'texts' => $texts,
                        'file' => true
                    ]);
                } else {
                    return response()->json([
                        'title' => $textbook->title,
                        'description' => $textbook->description,
                        'texts' => $texts,
                        'file' => false
                    ]);
                }
            } else {
                return response()->json(['Error' => 'Permission denied to view textbook!']);
            }
    }

    /**
     *
     * Returns a header for PDF download.
     * Downloads the PDF based on the textbook_id passed in.
     * Only authorised users can download.
     *
     * @param $textbook_id
     * @return mixed
     *
     */
    public function pdf($textbook_id)
    {
        $textbook = Textbook::find($textbook_id);

        if ($textbook == null) {
            return response()->json(['Error' => 'Textbook not found!']);
        }

        $checkIfUserHasPermissionToDownload = $this->checkPermission($textbook_id);

        if ($checkIfUserHasPermissionToDownload) {
            $file_contents = base64_decode($textbook->file);

            return response($file_contents)
                ->header('Cache-Control', 'no-cache private')
                ->header('Content-Description', 'File Transfer')
                ->header('Content-Type', 'application/x-pdf')
                ->header('Content-Length', strlen($file_contents))
                ->header('Content-Disposition', 'inline; filename="example.pdf"')
                ->header('Content-Transfer-Encoding', 'binary');
        } else {
            return response()->json(['Error' => 'Permission denied to download textbook!']);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param $textbook_id
     * @return \Illuminate\Http\Response
     */
    public function edit($textbook_id)
    {
        $textbook = Textbook::find($textbook_id);
        if($textbook != null) {
            $checkIfUserHasPermissionToEdit = $this->checkPermission($textbook_id);
            if ($checkIfUserHasPermissionToEdit){
                if($textbook->extensiveReadingCategories()->count()>0){
                    $selected = $textbook->extensiveReadingCategories()->first();
                    $section = 'extensiveReading';
                }else{
                    $selected = $textbook->modules()->get();
                    $section = 'module';
                }
                return response()->json([
                    'title' => $textbook->title,
                    'description' => $textbook->description,
                    'selected' => $selected,
                    'section' => $section,
                ]);
            } else {
                return response()->json(['Error' => 'Permission denied to edit textbook!']);
            }
        } else{
            return response()->json(['Error' => 'Textbook not found!']);
        }
    }

    /**
     * Displays the create textbook form based on the user role.
     * Only Admins can select all modules to put the textbook in.
     * Module Tutors can only select the modules that they are assigned to.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(){

        $user = Auth::user();
        $user_role = $user->role;
        $erc = ExtensiveReadingCategory::all();
        if($user_role->name === 'Admin'){
            $modules = Module::with('yearGroup')->get();
            return response()->json([
                'modules' => $modules,
                'extensiveReadingCategories' => $erc
            ]);
        }else {
            $modules = $user->modules()->with('yearGroup')->get();
            return response()->json([
                'modules' => $modules,
                'extensiveReadingCategories' => $erc
            ]);
        }
    }

    /**
     * Store a newly created textbook.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function store(Request $request)
    {
        $file = null;

        if ($request->file('file')) {
            $file = base64_encode(file_get_contents($request->file('file')));
        }

        //data that will be inserted into new row in textbook table
        $data = [
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'file' => $file,
        ];

        //create a textbook using the POST data
        $textbook =  Textbook::create($data);

        $selectedJson = $request->input('selected');

        $decoded_json = json_decode($selectedJson);

        if(str_contains($selectedJson,'[')){
            $collection = collect($decoded_json);
            $ids = $collection->pluck('id')->toArray();
        } else{
            $ids = collect($decoded_json->id);
        }

        if($request->input('section') === 'module'){
            $textbook->modules()->sync($ids);
        } else{
            $textbook->extensiveReadingCategories()->sync($ids);
        }
    }

    /**
     * Update the specified textbook based on the $textbook_id.
     *
     * @param  \Illuminate\Http\Request $request
     * @param $textbook_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $textbook_id)
    {
        $file = null;
        $textbook = Textbook::findOrFail($textbook_id);
        if ($request->file('file')) {
            $file = base64_encode(file_get_contents($request->file('file')));
            $textbook->file = $file;
        }

        $textbook->title = $request->input('title');
        $textbook->description = $request->input('description');
        $textbook->save();

        $selectedJson = $request->input('selected');

        $decoded_json = json_decode($selectedJson);

        if(str_contains($selectedJson,'[')){
            $collection = collect($decoded_json);
            $ids = $collection->pluck('id')->toArray();
        } else{
            $ids = collect($decoded_json->id);
        }

        if($request->input('section') === 'module'){
            $textbook->modules()->sync($ids);
            $textbook->extensiveReadingCategories()->detach();
        } else{
            $textbook->extensiveReadingCategories()->sync($ids);
            $textbook->modules()->detach();
        }

        return response()->json([
                'Success' => 'Successfully updated']
        );
    }

    /**
     * Remove the specified textbook based on the textbook_id.
     *
     * @param $textbook_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($textbook_id)
    {
        $textbook = Textbook::findOrFail($textbook_id);

        $checkIfUserHasPermissionToDelete = $this->checkPermission($textbook_id);

        if($checkIfUserHasPermissionToDelete){
            $textbook->delete();
        } else {
            return response()->json(['Error' => 'Permission denied to delete textbook!']);
        }

        return response()->json($textbook);
    }

    /**
     * Returns true or false if current user has the permission to perform an action.
     * Admins are allowed to have access to anything.
     * Module Tutors must have a module assigned to them to have access.
     *
     * @param $textbook_id
     * @return bool
     *
     */
    private function checkPermission($textbook_id)
    {
        $module = Module::whereHas(
        'textbooks', function($q) use($textbook_id) {
        $q->where('textbook_id', $textbook_id);
    })->first();

        $extensiveReading = ExtensiveReadingCategory::whereHas(
            'textbooks', function($q) use($textbook_id) {
            $q->where('textbook_id', $textbook_id);
        })->first();

        if(Auth::user()->role->name === 'Admin'){
            return true;
        } else {
            if(Auth::user()->modules()->find($module) !== null || $extensiveReading !== null){
                return true;
            }
        }
      return false;
    }
}
