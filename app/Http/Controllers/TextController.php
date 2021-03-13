<?php

namespace App\Http\Controllers;

use App\ExtensiveReadingCategory;
use App\ModuleTextbook;
use App\ReadingSession;
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
        $user_role = $user->role;

        if($user_role->name === 'Admin'){
            $textbooks = Textbook::all();
            return response()->json($textbooks);
        }else {
            $textbooks = $user->modules()->has('textbooks')->
            with('textbooks')->get()->pluck('textbooks')->toArray();
            $textbooks = call_user_func_array('array_merge', $textbooks);
            return response()->json($textbooks);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return void
     */
    public function store(Request $request)
    {

        $file = null;

        if ($request->file('file')) {
            $file = base64_encode(file_get_contents($request->file('file')));
        }


        //data that will be inserted into new row in document table
        $data = [
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'file' => $file,
            'textbook_id' => $request->input('textbook_id')

        ];

        //create a document using the POST data
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
        //find textbook
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
            return response()->json(['Error' => 'Permission denied to download textbook!']);
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
                if ($text->file != null) {
                    $decoded = base64_decode($text->file);
                    file_put_contents('file.pdf', $decoded);
                    $path = 'c:/Program Files/Git/mingw64/bin/pdftotext';
                    $pdftext = base64_encode(PdfToText\Pdf::getText('file.pdf', $path));
                    File::delete('file.pdf');
                    return response()->json([
                        'title' => $text->title,
                        'description' => $text->description,
                        'text' => $pdftext,
                    ]);
                } else {
                    return response()->json([
                        'title' => $text->title,
                        'description' => $text->description,
                    ]);
                }
            } else {
                return response()->json(['Error' => 'Permission not allowed to view the text']);
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
        $text = Text::find($id);
        if($text != null) {
            $checkIfUserHasPermissionToEdit = $this->checkPermission($text->textbook_id);
            if($checkIfUserHasPermissionToEdit){
                $textbook = Textbook::find($text->textbook_id);
                return response()->json([
                    'title' => $text->title,
                    'description' => $text->description,
                    'file' => $text->file,
                    'textbook' => $textbook,
                ]);
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
        $t = Text::find($id);
        if ($request->file('file')) {
            $file = base64_encode(file_get_contents($request->file('file')));
            $t->file = $file;
        }

        $t->title = $request->input('title');
        $t->description = $request->input('description');
        $t->textbook_id = $request->input('textbook_id');
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
     */
    public function saveAttempt(Request $request){

            //data that will be inserted into new row in reading sessions table
            $data = [
                'text_id' => $request->input('text_id'),
                'user_id' => Auth::user()->id,
                'attempt_number' => $request->input('attempt_num'),
                'time_taken' =>$request->input('time')
            ];
            //create a reading session using the POST data
            ReadingSession::create($data);

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

            $tb = Auth::user()->modules()->has('textbooks')->
            with('textbooks')->get()->pluck('textbooks')->toArray();
            $tb = call_user_func_array('array_merge', $tb);

            $test = collect($tb)->pluck('id');
            $attempts = ReadingSession::with('text.textbook')
                ->whereHas('text.textbook', function($query) use ($test){
                    $query->whereIn('id',$test);
                })
                ->with('user')->get();
            $textbooks = $tb;
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
            $textbooks = Auth::user()->modules()->has('textbooks')->
            with('textbooks')->get()->pluck('textbooks')->toArray();
            $textbooks = call_user_func_array('array_merge', $textbooks);
            $test = collect($textbooks)->pluck('id');
            $last5 = ReadingSession::with('text.textbook')->with('user')
                ->whereHas('text.textbook', function($query) use ($test){
                    $query->whereIn('id',$test);
                })
                ->orderBy('id', 'desc')->take(5)->get();
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
