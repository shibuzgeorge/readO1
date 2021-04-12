<?php

namespace App\Http\Controllers;

use App\ExtensiveReadingCategory;
use App\ReadingSession;
use App\YearGroup;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Spatie\PdfToText;
use Illuminate\Support\Facades\File;
use App\Text;
use App\Module;
use App\Textbook;

class TextController extends Controller
{
    /**
     *
     * Applies middleware to some of the methods to restrict access.
     *
     * TextController constructor.
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
     * Show the form for creating a new text.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        $user = Auth::user();
        $user_role = $user->role->name;
        if($user_role === 'Admin'){
            $yearGroup = YearGroup::has('module.textbooks')->get();
        }else{
            $module_ids = $user->modules()->get()->pluck('id');
            $yearGroup = YearGroup::has('module.textbooks')->
           whereHas('module', function ($query) use ($module_ids){
                $query->whereIn('modules.id', $module_ids);
            })->get();
        }
        $extensiveReadingCategories = ExtensiveReadingCategory::has('textbooks')->with('textbooks')->get();

        return response()->json([
            'yearGroup' => $yearGroup,
            'extensiveReadingCategories' => $extensiveReadingCategories
        ]);
    }

    /**
     * Store a newly created text in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return void
     */
    public function store(Request $request)
    {
        $file = null;
        $request->validate([
            'title' => 'required',
            'description' => 'required',
            'selected' => ["required" , "not_in:[]"],
            'section' => 'required',
            'file' => 'required|mimes:pdf',
        ],
            [
                'selected.required' => 'You have to choose a textbook to upload a text into!',
                'selected.not_in' => 'You have to choose a textbook to upload a text into!',
            ]);

        if ($request->file('file')) {
            $file = base64_encode(file_get_contents($request->file('file')));
        }
        $selectedJson = $request->input('selected');

        $decoded_json = json_decode($selectedJson);

        $id = collect($decoded_json->id)[0];

        //data that will be inserted into new row in texts table
        $data = [
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'file' => $file,
            'textbook_id' => $id,
        ];

        //create a text using the POST data
        Text::create($data);
    }

    /**
     * Check if the current user can download the pdf.
     * Get's the texts base64 and decodes and reproduces the pdf version
     *
     * @param $id
     * @return mixed
     *
     */
    public function pdf($id)
    {
        //find text
        $text = Text::find($id);

        if ($text == null) {
            return response()->json(['Error' => 'Text not found!']);
        }

        $checkIfUserHasPermissionToDownload = $this->checkPermission($text->textbook_id);

        if ($checkIfUserHasPermissionToDownload) {
            $file_contents = base64_decode($text->file);

            return response($file_contents)
                ->header('Cache-Control', 'no-cache private')
                ->header('Content-Description', 'File Transfer')
                ->header('Content-Type', 'application/x-pdf')
                ->header('Content-Length', strlen($file_contents))
                ->header('Content-Disposition', 'inline; filename="example.pdf"')
                ->header('Content-Transfer-Encoding', 'binary');
        } else {
            return response()->json(['Error' => 'Permission denied to download the text!']);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $text = Text::find($id);

        if ($text == null) {
            return response()->json(['Error' => 'Text not found!']);
        }
            $checkIfUserHasPermissionToView = $this->checkPermission($text->textbook_id);

            if ($checkIfUserHasPermissionToView) {
                    $decoded = base64_decode($text->file);
                    file_put_contents(public_path('file.pdf'), $decoded);
                //Checks operating system if it is windows or not
                if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                    $pdftext = base64_encode(PdfToText\Pdf::getText(public_path('file.pdf'), public_path('pdftotext/pdftotext.exe')));
                } else {
                    $pdftext = base64_encode(PdfToText\Pdf::getText(public_path('file.pdf')));
                }
                    File::delete('file.pdf');
                    return response()->json([
                        'title' => $text->title,
                        'description' => $text->description,
                        'textbook' => $text->textbook()->first(),
                        'text' => $pdftext,
                    ]);
            } else {
                return response()->json(['Error' => 'Permission denied to view the text!']);
            }

    }

    /**
     * Show the form for editing the text.
     * Checks permission if the user can actually edit.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $user = Auth::user();
        $user_role = $user->role->name;

        $text = Text::find($id);
        if($text != null) {
            $checkIfUserHasPermissionToEdit = $this->checkPermission($text->textbook_id);
            if($checkIfUserHasPermissionToEdit){

                if($user_role === 'Admin'){
                    $yearGroup = YearGroup::has('module.textbooks')->get();
                }else{
                    $module_ids = $user->modules()->get()->pluck('id');
                    $yearGroup = YearGroup::has('module.textbooks')->
                    whereHas('module', function ($query) use ($module_ids){
                        $query->whereIn('modules.id', $module_ids);
                    })->get();
                }
                $extensiveReadingCategories = ExtensiveReadingCategory::has('textbooks')->with('textbooks')->get();

                $textbook = Textbook::find($text->textbook_id);
                if($textbook->extensiveReadingCategories()->count()>0){

                    return response()->json([
                        'title' => $text->title,
                        'description' => $text->description,
                        'selected' => $textbook,
                        'section' => 'extensiveReadingTextbook',
                        'selectedExtensiveReadingCategory' => $textbook->extensiveReadingCategories()->with('textbooks')->first(),
                        'textbook' => $textbook,
                        'yearGroup' => $yearGroup,
                        'extensiveReadingCategories' => $extensiveReadingCategories
                    ]);
                }else{

                    $year_group = $textbook->modules()->first()->yearGroup()->first();
                    return response()->json([
                        'title' => $text->title,
                        'description' => $text->description,
                        'selected' => $textbook,
                        'section' => 'moduleTextbook',
                        'year_group' => $year_group,
                        'selectedModule' => $textbook->modules()->with('textbooks')->first(),
                        'textbook' => $textbook,
                        'yearGroup' => $yearGroup,
                        'extensiveReadingCategories' => $extensiveReadingCategories
                    ]);
                }


            }else{
                return response()->json(['Error' => 'Permission denied to edit the text!']);
            }
        }
        else{
            return response()->json(['Error' => 'Textbook not found!']);
        }
    }

    /**
     * Update the text based on the text id.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $file = null;

        $request->validate([
            'title' => 'required',
            'description' => 'required',
            'selected' => ["required" , "not_in:[]"],
            'section' => 'required',
            'file' => 'nullable|mimes:pdf',
        ],
            [
                'selected.required' => 'You have to choose a textbook to upload a text into!',
                'selected.not_in' => 'You have to choose a textbook to upload a text into!',
            ]);

        $t = Text::find($id);
        if ($request->file('file')) {
            $file = base64_encode(file_get_contents($request->file('file')));
            $t->file = $file;
        }

        $selectedJson = $request->input('selected');

        $decoded_json = json_decode($selectedJson);

        $id = collect($decoded_json->id)[0];

        $t->title = $request->input('title');
        $t->description = $request->input('description');
        $t->textbook_id = $id;
        $t->save();

        return response()->json([
                'Success' => 'Successfully updated']
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $text = Text::findOrFail($id);
        $checkIfUserHasPermissionToDelete = $this->checkPermission($text->textbook_id);

        if($checkIfUserHasPermissionToDelete) {
            $text->delete();
        }else{
            return response()->json(['Error' => 'Permission denied to delete text!']);
        }
        return response()->json($text);
    }

    /**
     * Saves the attempt of the reading to the database.
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveAttempt(Request $request){

        $text = Text::find($request->input('text_id'));

        if ($text == null){
            return response()->json(['Error' => 'Text not found!']);
        }

        $checkIfUserHasPermissionToSaveAttempt = $this->checkPermission($text->textbook_id);

        if($checkIfUserHasPermissionToSaveAttempt) {
            //data that will be inserted into new row in reading sessions table
            $data = [
                'text_id' => $request->input('text_id'),
                'user_id' => Auth::user()->id,
                'attempt_number' => $request->input('attempt_num'),
                'time_taken' =>$request->input('time')
            ];
            //create a reading session using the POST data
            ReadingSession::create($data);
            return response()->json(['Success' => 'Successfully saved the attempt!']);
        } else {
            return response()->json(['Error' => 'Permission denied to delete text!']);
        }
    }

    /**
     * Gets the attempt of the reading for a particular text for a user to the database.
     * @param $text_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAttempt($text_id)
    {
        $last_attempt = ReadingSession::where('text_id',$text_id)->where('user_id', auth()->user()->id)->orderBy('id', 'desc')->first();

        if($last_attempt!= null){
            return response()->json([
                'attempt' => $last_attempt->attempt_number,
                'time_taken' => $last_attempt->time_taken
            ]);
        }else{
            return response()->json(null);
        }
    }

    /**
     * Gets all the attempts for a user
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllAttemptsForCurrentUser()
    {
        if(Auth::user()->role->name === 'Admin'){
            $attempts = ReadingSession::with('text.textbook')->with('user')->get();
            $textbooks = ReadingSession::with('text.textbook')->get()->unique('text.textbook')->pluck('text.textbook');

        }else if(Auth::user()->role->name === 'Module Tutor') {
            $moduleTextbooks = Auth::user()->modules()->has('textbooks')->
            with('textbooks')->get()->pluck('textbooks');

            $extensiveReadingTextbooks = ExtensiveReadingCategory::has('textbooks')->
            with('textbooks')->get()->pluck('textbooks');

            $textbooks = $moduleTextbooks->merge($extensiveReadingTextbooks);
            $textbooks = call_user_func_array('array_merge', $textbooks->toArray());
            $allTextbookIds = collect($textbooks)->pluck('id');

            $attempts = ReadingSession::with('text.textbook')->with('user')
                ->whereHas('text.textbook', function($query) use ($allTextbookIds){
                    $query->whereIn('id',$allTextbookIds);
                })->get();
            $textbooks = $attempts->unique('text.textbook')->pluck('text.textbook');
        } else{
            $attempts = ReadingSession::where('user_id', auth()->user()->id)->with('text.textbook')->get();
            $textbooks = ReadingSession::where('user_id', auth()->user()->id)->with('text.textbook')->get()->unique('text.textbook')->pluck('text.textbook');
        }

        return response()->json([
            'attempts' => $attempts,
            'textbooks' => $textbooks
        ]);

    }

    /**
     * Gets the last 5 attempts for the user.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     */
    public function getLast5attempts()
    {
        if(Auth::user()->role->name === 'Admin'){
            $last5 = ReadingSession::with('text.textbook')->with('user')
                ->orderBy('id', 'desc')->take(5)->get();
        }else if(Auth::user()->role->name === 'Module Tutor') {
            $moduleTextbooks = Auth::user()->modules()->has('textbooks')->
            with('textbooks')->get()->pluck('textbooks');

            $extensiveReadingTextbooks = ExtensiveReadingCategory::has('textbooks')->
            with('textbooks')->get()->pluck('textbooks');

            $textbooks = $moduleTextbooks->merge($extensiveReadingTextbooks);
            $textbooks = call_user_func_array('array_merge', $textbooks->toArray());
            $allTextbookIds = collect($textbooks)->pluck('id');

            $last5 = ReadingSession::with('text.textbook')->with('user')
                ->whereHas('text.textbook', function($query) use ($allTextbookIds){
                    $query->whereIn('id',$allTextbookIds);
                })->orderBy('id', 'desc')->take(5)->get();
        }else{
            $last5 = ReadingSession::with('text.textbook')
                ->where('user_id', auth()->user()->id)
                ->orderBy('id', 'desc')->take(5)->get();
        }
        return response()->json($last5);
    }

    /**
     * Returns true or false if current user has the permission to perform an action.
     * Admins are allowed to have access to anything.
     * Module Tutors must have a text assigned to them to have access to CRUD.
     * Students can only view data.
     *
     * @param $textbook_id
     * @return bool
     *
     */
    private function checkPermission($textbook_id)
    {
        $module = Auth::user()->modules()->whereHas(
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
            if($module !== null || $extensiveReading !== null){
                return true;
            }
        }
        return false;
    }
}
