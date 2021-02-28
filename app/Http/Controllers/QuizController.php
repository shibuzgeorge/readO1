<?php

namespace App\Http\Controllers;

use App\Option;
use App\Question;
use App\Quiz;
use App\Text;
use App\UserQuizScore;
use Barryvdh\DomPDF\Facade as PDF;
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

        foreach (json_decode($request->input('selected')) as $option){
            $o = Option::find($option);
            array_push($points,$o->points);
        }
        $totalPointsCollected = array_sum($points);

        $quiz_object = Quiz::where('id',$request->input('quiz_id'));

        $quiz = collect($quiz_object->with('questions','options')->get()
            ->map(function ($event) {
                return [
                    'questions' => $event->questions->map(function ($event2) {
                        return [
                            'question_text' => $event2->question,
                            'options' =>$event2->options->map(function ($event3) {
                                return [
                                    'option_text' => $event3->option,
                                    'points' => $event3->points
                                ];})
                        ];})
                ];})[0]);

        $pdf = PDF::loadView('pdf', compact('quiz', 'totalPointsCollected'));
        $pdf->save('testing.pdf');
        $base64 = base64_encode(file_get_contents('testing.pdf'));
        File::delete('testing.pdf');
        UserQuizScore::create([
            'user_id' => auth()->user()->id,
            'quiz_id' => $request->input('quiz_id'),
            'attempt_number' => $request->input('attempt_num'),
            'result' => $base64,
            'score' => $totalPointsCollected
            ]);

        return response()->json([
            'totalPointsCollected' => $totalPointsCollected,
        ]);
    }

    /**
     * Gets the last attempt for a particular quiz for a user to the database.
     * @param $quiz_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAttempt($quiz_id)
    {
        $last_attempt = UserQuizScore::where('quiz_id',$quiz_id)->where('user_id', auth()->user()->id)->orderBy('id', 'desc')->first();
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
    public function manage(Request $request, $text_id){

        $quizzes = collect(json_decode($request->quizzes));
        $text = Text::find($text_id);
        $text->quizzes()->delete();
        foreach ($quizzes as $quiz) {
            $quiz_object = Quiz::create(['text_id' => $text_id]);
            foreach ($quiz->questions as $question) {
                $create_question = Question::create(['question' => $question->question_text, 'quiz_id' => $quiz_object->id]);
                foreach ($question->options as $option) {
                 Option::create(['option' => $option->option_text, 'question_id' => $create_question->id, 'points' => $option->points]);
                }
            }
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
                'questions' => $event->questions->map(function ($event2) {
                    return [
                        'question_text' => $event2->question,
                        'options' =>$event2->options->map(function ($event3) {
                            return [
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
     *
     * Returns a header for PDF download.
     * Downloads the PDF of the results based on the quiz_id passed in.
     * Only authorised users can download.
     *
     * @param $quiz_id
     * @return mixed
     */
    public function getResultPDF($quiz_id)
    {
        $quiz = UserQuizScore::where('quiz_id', $quiz_id)->where('user_id', auth()->user()->id)->orderBy('id', 'desc')->first();

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
