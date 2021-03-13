<?php

namespace App\Http\Controllers;

use App\Option;
use App\Question;
use App\Quiz;
use App\Text;
use App\UserQuizScore;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    /**
     *
     * Applies middleware to some of the methods to restrict access.
     *
     * QuizController constructor.
     *
     */
    public function __construct()
    {
        $this->middleware('role:Admin,Module Tutor')->only(['manage', 'edit']);
    }

    /**
     * Displays at random a quiz for a particular text based on the text_id.
     *
     * @param $text_id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($text_id){
        $quiz = Quiz::where('text_id', $text_id)->with('questions','options')->inRandomOrder()->first();
        return response()->json([
            'quiz' => $quiz,
        ]);
    }

    /**
     * Returns the points the user collected from answering the quiz.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function result(Request $request){
        $points = [];
        $selectedOptions = [];

        foreach (json_decode($request->input('selected')) as $option){
            $o = Option::find($option);
            array_push($selectedOptions,$o->option);
            array_push($points,$o->points);
        }
        $totalPointsCollected = array_sum($points);

        $quiz_object = Quiz::where('id',$request->input('quiz_id'));

        $quiz = collect($quiz_object->with('questions','options')->get()
            ->map(function ($event) {
                return [
                    'max_points' => $event->max_points,
                    'questions' => $event->questions->map(function ($event2) {
                        return [
                            'max_points' => $event2->max_points,
                            'question_text' => $event2->question,
                            'options' =>$event2->options->map(function ($event3) {
                                return [
                                    'option_text' => $event3->option,
                                    'points' => $event3->points
                                ];})
                        ];})
                ];})[0]);

        $attempt_number = $request->input('attempt_num');
        $pdf = PDF::loadView('pdf', compact('quiz', 'totalPointsCollected', 'selectedOptions', 'attempt_number'));
        $pdf->save('quiz_result.pdf');
        $base64 = base64_encode(file_get_contents('quiz_result.pdf'));
        File::delete('quiz_result.pdf');

        $user_score = UserQuizScore::create([
            'user_id' => auth()->user()->id,
            'quiz_id' => $request->input('quiz_id'),
            'attempt_number' => $request->input('attempt_num'),
            'result' => $base64,
            'score' => $totalPointsCollected
            ]);

        return response()->json([
            'totalPointsCollected' => $totalPointsCollected,
            'user_score_id' => $user_score->id
        ]);
    }

    /**
     * Gets all the attempts for a user
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllAttemptsForCurrentUser()
    {
        if(Auth::user()->role->name === 'Admin'){
            $attempts = UserQuizScore::with('quiz.text.textbook')->with('user')
                ->get();
            $textbooks = UserQuizScore::with('quiz.text.textbook')->get()->unique('quiz.text.textbook')->pluck('quiz.text.textbook');
        }else if(Auth::user()->role->name === 'Module Tutor') {

            $textbooks = Auth::user()->modules()->has('textbooks')->
            with('textbooks')->get()->pluck('textbooks')->toArray();
            $textbooks = call_user_func_array('array_merge', $textbooks);

            $test = collect($textbooks)->pluck('id');
            $attempts = UserQuizScore::with('quiz.text.textbook')->with('user')
                ->whereHas('quiz.text.textbook', function($query) use ($test){
                    $query->whereIn('id',$test);
                })->get();

        } else{
            $attempts = UserQuizScore::where('user_id', auth()->user()->id)->with('quiz.text.textbook')->get();
            $textbooks = UserQuizScore::where('user_id', auth()->user()->id)->with('quiz.text.textbook')->get()->unique('quiz.text.textbook')->pluck('quiz.text.textbook');


        }

        return response()->json([
            'attempts' => $attempts,
            'textbooks' => $textbooks
        ]);

    }

    /**
     * Gets the last attempt for a particular quiz for a text for a user to the database.
     * @param $quiz_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAttempt($quiz_id)
    {
        $last_attempt = UserQuizScore::whereHas('quiz', function ($query) use($quiz_id) {
        return $query->where('text_id', Quiz::where('id', $quiz_id)->first()->text_id);
        })->where('user_id', auth()->user()->id)->orderBy('id', 'desc')->first();

        if($last_attempt!= null){
            return response()->json([
                'attempt' => $last_attempt->attempt_number,
            ]);
        }else{
            return response()->json(null);
        }
    }

    /**
     * Stores and updates all the quizzes, questions, options and points based on the text_id.
     *
     * @param Request $request
     * @param $text_id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function manage(Request $request, $text_id)
    {
        //Collection of quizzes from from
        $quizzes = collect(json_decode($request->quizzes));

        //Array of quizzesId, questionsId and optionsId
        $quizzesId = [];
        $questionsId = [];
        $optionsId = [];

        $text = Text::find($text_id);

        //list of ids in the database for the quiz, questions, and options currently
        $quiz_ids = $text->quizzes()->get()->pluck('id')->toArray();
        $question_ids = Question::with('quiz')->get()->where('quiz.text_id', $text_id)->pluck('id')->toArray();
        $option_ids = Option::with('question.quiz')->get()->where('question.quiz.text_id', $text_id)->pluck('id')->toArray();

        foreach ($quizzes as $quiz) {
            if(isset($quiz->id) && $quiz->id !== "new"){
                $quiz_object = Quiz::where('id', $quiz->id)->first();
            }else{
                $quiz_object = Quiz::create(['text_id' => $text_id, 'max_points' => 0]);
            }
            array_push($quizzesId, $quiz_object->id);
            foreach ($quiz->questions as $question) {
                if(isset($question->id) && $question->id !== "new"){
                    $question_object = Question::where('id', $question->id)->first();
                    $question_object->question = $question->question_text;
                    $question_object->quiz_id = $quiz_object->id;
                    $question_object->save();
                }else{
                    $question_object = Question::create(['question' => $question->question_text, 'quiz_id' => $quiz_object->id, 'max_points' => 0]);
                }
                array_push($questionsId, $question_object->id);
                foreach ($question->options as $option) {
                    if(isset($option->id) && $option->id !== "new"){
                        $option_object = Option::where('id', $option->id)->first();
                        $option_object->option = $option->option_text;
                        $option_object->question_id = $question_object->id;
                        $option_object->points = $option->points;
                        $option_object->save();
                    }else{
                        $option_object = Option::create(['option' => $option->option_text, 'question_id' => $question_object->id, 'points' => $option->points]);
                    }
                    array_push($optionsId, $option_object->id);
                }
                $question_object->max_points = Question::where('id', $question_object->id)->with('options')->get()->max('options')->pluck('points')->max();
                $question_object->save();
            }
            $quiz_object->max_points = Quiz::where('id', $quiz_object->id)->with('questions')->get()->max('questions')->pluck('max_points')->sum();
            $quiz_object->save();
        }

        $deletedOptions = array_diff($option_ids,$optionsId);
        if(!empty($deletedOptions)){
            Option::whereIn('id', $deletedOptions)->delete();
        }

        $deletedQuestions = array_diff($question_ids,$questionsId);

        if(!empty($deletedQuestions)){
            Question::whereIn('id', $deletedQuestions)->delete();
            //Update max_points
            $question_ids = Question::with('quiz')->get()->where('quiz.text_id', $text_id)->pluck('id')->toArray();
            foreach($question_ids as $question_id){
                $question_object = Question::where('id', $question_id)->first();

                $question_object->max_points = Question::where('id', $question_id)->with('options')->get()->max('options')->pluck('points')->max();
                $question_object->save();
            }

            $quiz_ids = $text->quizzes()->get()->pluck('id')->toArray();
            foreach($quiz_ids as $quiz_id){
                $quiz_object = Question::where('id', $quiz_id)->first();

                $quiz_object->max_points = Quiz::where('id', $quiz_id)->with('questions')->get()->max('questions')->pluck('max_points')->sum();
                $quiz_object->save();
            }
        }

        $deletedQuizzes = array_diff($quiz_ids,$quizzesId);
        if(!empty($deletedQuizzes)){
            Quiz::whereIn('id', $deletedQuizzes)->delete();
        }

        return response()->json([
            'Success' => 'Successfully updated your quizzes!'
        ]);
    }

    /**
     * Displays all quizzes, questions, options and points for a particular text based on the text_id.
     *
     * @param $text_id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit($text_id)
    {
        $text = Text::find($text_id);
        $allQuiz = $text->quizzes()->with('questions','options')->get()
            ->map(function ($event) {
            return [
                'id' => $event->id,
                'questions' => $event->questions->map(function ($event2) {
                    return [
                        'id' => $event2->id,
                        'question_text' => $event2->question,
                        'options' =>$event2->options->map(function ($event3) {
                            return [
                                'id' => $event3->id,
                                'option_text' => $event3->option,
                                'points' => $event3->points
                            ];})
                    ];})
            ];});

        return response()->json([
            'quizzes' => $allQuiz
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
            $last5 = UserQuizScore::with('quiz.text.textbook')->with('user')
                ->orderBy('id', 'desc')->take(5)->get();
        }else if(Auth::user()->role->name === 'Module Tutor') {
            $textbooks = Auth::user()->modules()->has('textbooks')->
            with('textbooks')->get()->pluck('textbooks')->toArray();
            $textbooks = call_user_func_array('array_merge', $textbooks);
            $test = collect($textbooks)->pluck('id');
            $last5 = UserQuizScore::with('quiz.text.textbook')->with('user')
                ->whereHas('quiz.text.textbook', function($query) use ($test){
                    $query->whereIn('id',$test);
                })
                ->orderBy('id', 'desc')->take(5)->get();
        }else{
            $last5 = UserQuizScore::with('quiz.text.textbook')
                ->where('user_id', auth()->user()->id)
                ->orderBy('id', 'desc')->take(5)->get();
        }

        return response()->json($last5);
    }

    /**
     *
     * Returns a header for PDF download.
     * Downloads the PDF of the results based on the quiz_id passed in.
     * Only authorised users can download.
     *
     * @param $id
     * @return mixed
     */
    public function getResultPDF($id)
    {
        if(Auth::user()->role->name === 'Admin' ){
            $quiz = UserQuizScore::where('id', $id)->first();
        }else{
            $quiz = UserQuizScore::where('id', $id)->where('user_id', auth()->user()->id)->first();
        }

        if ($quiz == null) {
            return response()->json(['Error' => 'Quiz results not found!']);
        }

            $file_contents = base64_decode($quiz->result);

            return response($file_contents)
                ->header('Cache-Control', 'no-cache private')
                ->header('Content-Description', 'File Transfer')
                ->header('Content-Type', 'application/x-pdf')
                ->header('Content-Length', strlen($file_contents))
                ->header('Content-Disposition', 'inline; filename="result.pdf"')
                ->header('Content-Transfer-Encoding', 'binary');

    }
}
