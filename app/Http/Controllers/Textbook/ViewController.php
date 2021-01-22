<?php

namespace App\Http\Controllers\Textbook;

use App\Http\Controllers\Controller;
use App\Module;
use App\Textbook;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Spatie\PdfToText;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class ViewController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();
        $user_role = $user->roles()->first();
        if($user_role->name === 'Admin'){
            $textbooks = Textbook::all();
        }else{
            $textbooks = $user->modules()->has('textbooks')->
            with('textbooks')->get()->pluck('textbooks')->toArray();
            $textbooks = call_user_func_array('array_merge', $textbooks);
        }
        return response()->json($textbooks);
    }

    /**
     * Display the specified resource.
     *
     * @param
     * @return \Illuminate\Http\Response
     */
    public function show(int $textbook)
    {
        $m = Textbook::find($textbook);

        $textbooks = Auth::user()->modules()->has('textbooks')->
        with('textbooks')->get()->pluck('textbooks')->toArray();

        $textbooks = call_user_func_array('array_merge', $textbooks);
        $t = collect($textbooks);

        if(Auth::user()->roles()->first()->name != 'Admin'){
            $checkIfUserHasPermissionToView = $t->where('id',$textbook)->first();
            if($checkIfUserHasPermissionToView != null){

                return response()->json([
                    'title' => $m->title,
                    'description' => $m->description,
                    'file' => $m->file,
                ]);

            }else{
                return response()->json(['Error' => 'Permission not allowed to view']);
            }
        }else {

            if ($m != null) {

                $decoded = base64_decode($m->file);
                file_put_contents('file.pdf',$decoded);
                $path = 'c:/Program Files/Git/mingw64/bin/pdftotext';
                $text = base64_encode(PdfToText\Pdf::getText('file.pdf', $path));
                File::delete('file.pdf');
                return response()->json([
                    'title' => $m->title,
                    'description' => $m->description,
                    'file' => $m->file,
                    'text' => $text,
                ]);

            } else {
                return response()->json(['Error' => 'Textbook not found!']);
            }

        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function edit(int $textbook)
    {
        $t = Textbook::find($textbook);
        if($t != null) {
            $module = $t->modules()->first();
            return response()->json([
                'title' => $t->title,
                'description' => $t->description,
                'file' => $t->file,
                'module' => $module,
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
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, int $textbook_id)
    {
        $file = null;
        $t = Textbook::find($textbook_id);
        if ($request->file('file')) {
            $file = base64_encode(file_get_contents($request->file('file')));
            $t->file = $file;
        }

        $t->title = $request->input('title');
        $t->description = $request->input('description');

        $t->save();
        return response()->json([
            'Success' => 'Successfully updated']
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(int $textbook)
    {
        $t = Textbook::find($textbook);
        $t->delete();

        return response()->json($t);
    }
}
