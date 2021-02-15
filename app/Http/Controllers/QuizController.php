<?php

namespace App\Http\Controllers;

use App\Option;
use App\Question;
use App\Quiz;
use App\Text;
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
        return response()->json([
            'totalPointsCollected' => $totalPointsCollected,
        ]);
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
}
