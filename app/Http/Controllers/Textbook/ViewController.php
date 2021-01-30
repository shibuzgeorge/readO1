<?php

namespace App\Http\Controllers\Textbook;

use App\Http\Controllers\Controller;
use App\Module;
use App\Textbook;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

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
     * Display the specified resource.
     *
     * @param  \App\Module  $module
     * @return \Illuminate\Http\Response
     */
    public function show($textbook)
    {
        $t = Textbook::find($textbook);

        $module = Module::whereHas(
            'textbooks', function($q) use($textbook) {
            $q->where('textbook_id', $textbook);
        })->first();

        if(Auth::user()->role->name != 'Admin'){
            $checkIfUserHasPermissionToView = Auth::user()->modules()->find($module);
            if($checkIfUserHasPermissionToView != null){
                $texts = $t->texts()->get();
                if($t->file != null) {
                    return response()->json([
                        'title' => $t->name,
                        'description' => $t->description,
                        'texts' => $texts,
                        'file' => true
                    ]);
                }else{
                    return response()->json([
                        'title' => $t->name,
                        'description' => $t->description,
                        'texts' => $texts,
                        'file' => false
                    ]);
                }
            }else{
                return response()->json(['Error' => 'Permission denied to view textbook!']);
            }
        }else{

            if($t != null){
                $texts = $t->texts()->get();
                if($t->file != null) {
                    return response()->json([
                        'title' => $t->title,
                        'description' => $t->description,
                        'texts' => $texts,
                        'file' => true
                    ]);
                }else{
                    return response()->json([
                        'title' => $t->title,
                        'description' => $t->description,
                        'texts' => $texts,
                        'file' => false
                    ]);
                }
            }else{
                return response()->json(['Error' => 'Textbook not found!']);
            }

        }

    }

    public function pdf($id)
    {
        //find textbook
        $textbook = Textbook::find($id);

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

        $module = Module::find($request->input('module_id'));
        $t->modules()->detach();
        $t->modules()->attach($module);

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
