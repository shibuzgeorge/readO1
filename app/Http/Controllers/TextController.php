<?php

namespace App\Http\Controllers;

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
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
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
     * Get's the texts base64 and decodes and reproduces the pdf version.
     *
     * @param $id
     * @return mixed
     *
     */
    public function pdf($id)
    {
        //find textbook
        $textbook = Text::find($id);

        $file_contents = base64_decode($textbook->file);

        return response($file_contents)
            ->header('Cache-Control', 'no-cache private')
            ->header('Content-Description', 'File Transfer')
            ->header('Content-Type', 'application/x-pdf')
            ->header('Content-Length', strlen($file_contents))
            ->header('Content-Disposition', 'inline; filename="example.pdf"')
            ->header('Content-Transfer-Encoding', 'binary');
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

        $module = Module::whereHas(
            'textbooks', function($q) use($text) {
            $q->where('textbook_id', $text->textbook_id);
        })->first();


        if(Auth::user()->role->name != 'Admin'){
            $checkIfUserHasPermissionToView = Auth::user()->modules()->find($module);
            if($checkIfUserHasPermissionToView != null){
                if($text->file != null){
                    $decoded = base64_decode($text->file);
                    file_put_contents('file.pdf',$decoded);
                    $path = 'c:/Program Files/Git/mingw64/bin/pdftotext';
                    $pdftext = base64_encode(PdfToText\Pdf::getText('file.pdf', $path));
                    File::delete('file.pdf');
                    return response()->json([
                        'title' => $text->title,
                        'description' => $text->description,
                        'text' => $pdftext,
                    ]);
                }else{
                    return response()->json([
                        'title' => $text->title,
                        'description' => $text->description,
                    ]);
                }


            }else{
                return response()->json(['Error' => 'Permission not allowed to view the text']);
            }
        }else {

            if ($text != null) {
                if($text->file != null) {
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
                }else{
                    return response()->json([
                        'title' => $text->title,
                        'description' => $text->description,
                    ]);
                }
            } else {
                return response()->json(['Error' => 'Text not found!']);
            }

        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $t = Text::find($id);
        if($t != null) {
            $textbook = Textbook::find($t->textbook_id);
            return response()->json([
                'title' => $t->title,
                'description' => $t->description,
                'file' => $t->file,
                'textbook' => $textbook,
            ]);
        }
        else{
            return response()->json(['Error' => 'Textbook not found!']);
        }
    }

    /**
     * Update the specified resource in storage.
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
        $t = Text::find($id);
        $t->delete();

        return response()->json($t);
    }
}
