<?php

namespace Tests\Feature;

use App\ExtensiveReadingCategory;
use App\Notifications\QuizResult;
use App\Question;
use App\Quiz;
use App\Text;
use App\UserQuizScore;
use App\Module;
use App\User;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class QuizTest extends TestCase
{
    /** @var \App\User */
    protected $adminUser;

    /** @var \App\User */
    protected $moduleTutorUser;

    /** @var \App\User */
    protected $studentUser;

    public function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
        $this->adminUser = User::whereHas('role', function($q){
            $q->where('name', 'Admin');
        })->first();
        $this->moduleTutorUser = User::whereHas('role', function($q){
            $q->where('name', 'Module Tutor');
        })->first();
        $this->studentUser = User::whereHas('role', function($q){
            $q->where('name', 'Student');
        })->first();
    }

    /**
     * A test to view a quiz authorised by logging in as an Student
     *
     * @test
     * @return void
     */
    public function test_show_a_quiz_student_authorised()
    {
        $text = Module::has('textbooks.texts')->whereHas('users', function ($query){
            $query->where('user_id', $this->studentUser->id);
        })->first()->textbooks()->first()
            ->texts()->first();

        $quiz = Quiz::where('text_id', $text->id)->with('questions','options')->inRandomOrder()->first();


        $this->actingAs($this->studentUser)
            ->getJson('/api/quiz/'.$text->id)
            ->assertStatus(200)
            ->assertJsonFragment([$quiz->toArray()]);

    }

    /**
     * A test to view a quiz unauthorised permission denied by logging in as an Student
     *
     * @test
     * @return void
     */
    public function test_show_a_quiz_student_permission_denied()
    {
        $text = Module::has('textbooks.texts')->whereDoesntHave('users', function ($query){
            $query->where('user_id', $this->studentUser->id);
        })->first()->textbooks()->first()
            ->texts()->first();

        $this->actingAs($this->studentUser)
            ->getJson('/api/quiz/'.$text->id)
            ->assertJsonFragment(['Error'=>'Permission denied to view the quiz']);

    }

    /**
     * A test to view a quiz, text quiz not found.
     *
     * @test
     * @return void
     */
    public function test_show_quiz_not_found_student_authorised()
    {
        $this->actingAs($this->studentUser)
            ->getJson('/api/quiz/'.rand(9999,10000))
            ->assertJsonFragment([
                'Error' => 'Text quiz not found'
            ]);
    }

    /**
     * A test for getAllAttemptsForCurrentUser as Admin authorised
     *
     * @test
     * @return void
     */
    public function test_getAllAttemptsForCurrentUser_admin_authorised()
    {
        $attempts = UserQuizScore::with('quiz.text.textbook')->with('user')->get();
        $textbooks = UserQuizScore::with('quiz.text.textbook')->get()->unique('quiz.text.textbook')->pluck('quiz.text.textbook');

        $this->actingAs($this->adminUser)
            ->getJson('/api/quiz/getAllAttemptsForCurrentUser')
            ->assertJson([
                'attempts' => $attempts->toArray(),
                'textbooks' => $textbooks->toArray()
            ])
            ->assertStatus(200);
    }

    /**
     * A test for getAllAttemptsForCurrentUser as Module Tutor authorised
     *
     * @test
     * @return void
     */
    public function test_getAllAttemptsForCurrentUser_module_tutor_authorised()
    {
        $moduleTextbooks = $this->moduleTutorUser->modules()->has('textbooks')->
        with('textbooks')->get()->pluck('textbooks');

        $extensiveReadingTextbooks = ExtensiveReadingCategory::has('textbooks')->
        with('textbooks')->get()->pluck('textbooks');
        $textbooks = $moduleTextbooks->merge($extensiveReadingTextbooks);
        $textbooks = call_user_func_array('array_merge', $textbooks->toArray());
        $allTextbookIds = collect($textbooks)->pluck('id');
        $attempts = UserQuizScore::with('quiz.text.textbook')->with('user')
        ->whereHas('quiz.text.textbook', function($query) use ($allTextbookIds){
                 $query->whereIn('id',$allTextbookIds);
              })->get();
        $textbooks = $attempts->unique('quiz.text.textbook')->pluck('quiz.text.textbook');

        $this->actingAs($this->moduleTutorUser)
            ->getJson('/api/quiz/getAllAttemptsForCurrentUser')
            ->assertJson([
                'attempts' => $attempts->toArray(),
                'textbooks' => $textbooks->toArray()
            ])
            ->assertStatus(200);
    }

    /**
     * A test for getAllAttemptsForCurrentUser as a Student authorised
     *
     * @test
     * @return void
     */
    public function test_getAllAttemptsForCurrentUser_student_authorised()
    {
        $attempts = UserQuizScore::where('user_id', $this->studentUser->id)->with('quiz.text.textbook')->get();
        $textbooks = UserQuizScore::where('user_id', $this->studentUser->id)->with('quiz.text.textbook')->get()->unique('quiz.text.textbook')->pluck('quiz.text.textbook');

        $this->actingAs($this->studentUser)
            ->getJson('/api/quiz/getAllAttemptsForCurrentUser')
            ->assertJson([
                'attempts' => $attempts->toArray(),
                'textbooks' => $textbooks->toArray()
            ])
            ->assertStatus(200);
    }

    /**
     * A test to create quiz as an Admin authorised
     *
     * @test
     * @param $changeText
     * @return void
     */
    public function test_create_quiz_admin_authorised($changeText = null)
    {
        $text = ($changeText !== null ? $changeText : Text::whereDoesntHave('quizzes')->inRandomOrder()->first());

        $quiz_array = [
         [
            'id' => "new",
            'questions' => [
            [
                'id' => "new",
                'question_text' => 'Question 1',
                'options' => [
                    [
                        'id' => "new",
                        'option_text' => 'Option 1',
                        'points' => 0
                    ],
                    [
                        'id' => "new",
                        'option_text' => 'Option 2',
                        'points' => 1
                    ],
                    [
                        'id' => "new",
                        'option_text' => 'Option 3',
                        'points' => 0
                    ],
                ]
            ],
            [
                'id' => "new",
                'question_text' => 'Question 2',
                'options' => [
                        [
                            'id' => "new",
                            'option_text' => 'Option 1',
                            'points' => 1
                        ],
                        [
                            'id' => "new",
                            'option_text' => 'Option 2',
                            'points' => 0
                        ],
                        [
                            'id' => "new",
                            'option_text' => 'Option 3',
                            'points' => 0
                        ],
                ]
            ]
         ]
         ]
        ];

        $this->actingAs($this->adminUser)
            ->postJson('/api/quiz/'.$text->id, ['quizzes' => json_encode($quiz_array)])
            ->assertJson([
                'Success' => 'Successfully updated your quizzes!'
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('quizzes', [
            'id' =>  Quiz::orderBy('id', 'desc')->first()->id,
            'max_points' => 2,
        ]);

        //Question 1
        $this->assertDatabaseHas('questions', [
            'question' => 'Question 1',
            'max_points' => 1,
        ]);

        $this->assertDatabaseHas('options', [
            'option' => 'Option 1',
            'question_id' => Question::where('question', 'Question 1')->first()->id,
            'points' => 0
        ]);

        $this->assertDatabaseHas('options', [
            'option' => 'Option 2',
            'question_id' => Question::where('question', 'Question 1')->first()->id,
            'points' => 1
        ]);

        $this->assertDatabaseHas('options', [
            'option' => 'Option 3',
            'question_id' => Question::where('question', 'Question 1')->first()->id,
            'points' => 0
        ]);

        //Question 2
        $this->assertDatabaseHas('questions', [
            'question' => 'Question 2',
            'max_points' => 1,
        ]);

        $this->assertDatabaseHas('options', [
            'option' => 'Option 1',
            'question_id' => Question::where('question', 'Question 2')->first()->id,
            'points' => 1
        ]);

        $this->assertDatabaseHas('options', [
            'option' => 'Option 2',
            'question_id' => Question::where('question', 'Question 2')->first()->id,
            'points' => 0
        ]);

        $this->assertDatabaseHas('options', [
            'option' => 'Option 3',
            'question_id' => Question::where('question', 'Question 2')->first()->id,
            'points' => 0
        ]);
    }

    /**
     * A test to create quiz as a Module Tutor authorised
     *
     * @test
     * @param null $changeText
     * @return void
     */
    public function test_create_quiz_module_tutor_authorised($changeText = null)
    {
        $text = ($changeText !== null ? $changeText : Module::has('textbooks.texts')->whereHas('users', function ($query){
            $query->where('user_id', $this->moduleTutorUser->id);
        })->first()->textbooks()->first()
            ->texts()->whereDoesntHave('quizzes')->first());

        $quiz_array = [
            [
                'id' => "new",
                'questions' => [
                    [
                        'id' => "new",
                        'question_text' => 'Question 1',
                        'options' => [
                            [
                                'id' => "new",
                                'option_text' => 'Option 1',
                                'points' => 0
                            ],
                            [
                                'id' => "new",
                                'option_text' => 'Option 2',
                                'points' => 1
                            ],
                            [
                                'id' => "new",
                                'option_text' => 'Option 3',
                                'points' => 0
                            ],
                        ]
                    ],
                    [
                        'id' => "new",
                        'question_text' => 'Question 2',
                        'options' => [
                            [
                                'id' => "new",
                                'option_text' => 'Option 1',
                                'points' => 1
                            ],
                            [
                                'id' => "new",
                                'option_text' => 'Option 2',
                                'points' => 0
                            ],
                            [
                                'id' => "new",
                                'option_text' => 'Option 3',
                                'points' => 0
                            ],
                        ]
                    ]
                ]
            ]
        ];

        $this->actingAs($this->moduleTutorUser)
            ->postJson('/api/quiz/'.$text->id, ['quizzes' => json_encode($quiz_array)])
            ->assertJson([
                'Success' => 'Successfully updated your quizzes!'
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('quizzes', [
            'id' =>  Quiz::orderBy('id', 'desc')->first()->id,
            'max_points' => 2,
        ]);

        //Question 1
        $this->assertDatabaseHas('questions', [
            'question' => 'Question 1',
            'max_points' => 1,
        ]);

        $this->assertDatabaseHas('options', [
            'option' => 'Option 1',
            'question_id' => Question::where('question', 'Question 1')->first()->id,
            'points' => 0
        ]);

        $this->assertDatabaseHas('options', [
            'option' => 'Option 2',
            'question_id' => Question::where('question', 'Question 1')->first()->id,
            'points' => 1
        ]);

        $this->assertDatabaseHas('options', [
            'option' => 'Option 3',
            'question_id' => Question::where('question', 'Question 1')->first()->id,
            'points' => 0
        ]);

        //Question 2
        $this->assertDatabaseHas('questions', [
            'question' => 'Question 2',
            'max_points' => 1,
        ]);

        $this->assertDatabaseHas('options', [
            'option' => 'Option 1',
            'question_id' => Question::where('question', 'Question 2')->first()->id,
            'points' => 1
        ]);

        $this->assertDatabaseHas('options', [
            'option' => 'Option 2',
            'question_id' => Question::where('question', 'Question 2')->first()->id,
            'points' => 0
        ]);

        $this->assertDatabaseHas('options', [
            'option' => 'Option 3',
            'question_id' => Question::where('question', 'Question 2')->first()->id,
            'points' => 0
        ]);
    }

    /**
     * A test to create quiz unauthorised as a Student
     *
     * @test
     * @return void
     */
    public function test_create_quiz_student_unauthorised()
    {
        $text = Module::has('textbooks.texts')->whereHas('users', function ($query) {
            $query->where('user_id', $this->studentUser->id);
        })->first()->textbooks()->first()
            ->texts()->whereDoesntHave('quizzes')->first();

        $quiz_array = [
            [
                'id' => "new",
                'questions' => [
                    [
                        'id' => "new",
                        'question_text' => 'Question 1',
                        'options' => [
                            [
                                'id' => "new",
                                'option_text' => 'Option 1',
                                'points' => 0
                            ],
                            [
                                'id' => "new",
                                'option_text' => 'Option 2',
                                'points' => 1
                            ],
                            [
                                'id' => "new",
                                'option_text' => 'Option 3',
                                'points' => 0
                            ],
                        ]
                    ],
                    [
                        'id' => "new",
                        'question_text' => 'Question 2',
                        'options' => [
                            [
                                'id' => "new",
                                'option_text' => 'Option 1',
                                'points' => 1
                            ],
                            [
                                'id' => "new",
                                'option_text' => 'Option 2',
                                'points' => 0
                            ],
                            [
                                'id' => "new",
                                'option_text' => 'Option 3',
                                'points' => 0
                            ],
                        ]
                    ]
                ]
            ]
        ];

        $this->actingAs($this->studentUser)
            ->postJson('/api/quiz/' . $text->id, ['quizzes' => json_encode($quiz_array)])
            ->assertJson([
                'error' => 'Unauthorized'
            ])
            ->assertStatus(403);
    }

    /**
     * A test to update quiz as an Admin authorised
     *
     * @test
     * @return void
     */
    public function test_update_quiz_admin_authorised()
    {
        $text = Text::whereHas('quizzes.questions.options')->inRandomOrder()->first();

        $original_quiz = Quiz::where('text_id', $text->id)->first();

        $quiz_array = [
            [
                'id' => $original_quiz->id,
                'questions' => [
                    [
                        'id' => $original_quiz->questions()->first()->id,
                        'question_text' => 'New Question 1',
                        'options' => [
                            [
                                'id' => $original_quiz->options()->first()->id,
                                'option_text' => 'New Option 1',
                                'points' => 5
                            ],
                            [
                                'id' => 'new',
                                'option_text' => 'New Option 2',
                                'points' => 7
                            ],
                        ]
                    ],
                ]
            ]
        ];

        $this->actingAs($this->adminUser)
            ->postJson('/api/quiz/'.$text->id, ['quizzes' => json_encode($quiz_array)])
            ->assertJson([
                'Success' => 'Successfully updated your quizzes!'
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('quizzes', [
            'id' =>  $original_quiz->id,
            'max_points' => 7,
        ]);

        //Question 1
        $this->assertDatabaseHas('questions', [
            'id' => $original_quiz->questions()->first()->id,
            'question' => 'New Question 1',
            'max_points' => 7,
        ]);

        $this->assertDatabaseHas('options', [
            'id' => $original_quiz->options()->first()->id,
            'option' => 'New Option 1',
            'question_id' => $original_quiz->questions()->first()->id,
            'points' => 5
        ]);

        $this->assertDatabaseHas('options', [
            'option' => 'New Option 2',
            'question_id' => $original_quiz->questions()->first()->id,
            'points' => 7
        ]);
    }

    /**
     * A test to update quiz by deleting that quiz as an Admin authorised
     *
     * @test
     * @return void
     */
    public function test_update_quiz_admin_delete_a_quiz_authorised()
    {
        $text = Text::whereHas('quizzes.questions.options')->inRandomOrder()->first();

        $original_quiz = Quiz::where('text_id', $text->id)->first();

        $quiz_array = [];

        $this->actingAs($this->adminUser)
            ->postJson('/api/quiz/'.$text->id, ['quizzes' => json_encode($quiz_array)])
            ->assertJson([
                'Success' => 'Successfully updated your quizzes!'
            ])
            ->assertStatus(200);

        $this->assertDatabaseMissing('quizzes', [
            'id' =>  $original_quiz->id,
        ]);

    }

    /**
     * A test to update quiz as a Module Tutor authorised
     *
     * @test
     * @return void
     */
    public function test_update_quiz_module_tutor_authorised()
    {
        $text = Module::has('textbooks.texts')->whereHas('users', function ($query){
            $query->where('user_id', $this->moduleTutorUser->id);
        })->first()->textbooks()->first()
            ->texts()->whereHas('quizzes.questions.options')->first();

        $original_quiz = Quiz::where('text_id', $text->id)->first();

        $quiz_array = [
            [
                'id' => $original_quiz->id,
                'questions' => [
                    [
                        'id' => $original_quiz->questions()->first()->id,
                        'question_text' => 'New Question 1',
                        'options' => [
                            [
                                'id' => $original_quiz->options()->first()->id,
                                'option_text' => 'New Option 1',
                                'points' => 5
                            ],
                            [
                                'id' => 'new',
                                'option_text' => 'New Option 2',
                                'points' => 7
                            ],
                        ]
                    ],
                ]
            ]
        ];

        $this->actingAs($this->moduleTutorUser)
            ->postJson('/api/quiz/'.$text->id, ['quizzes' => json_encode($quiz_array)])
            ->assertJson([
                'Success' => 'Successfully updated your quizzes!'
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('quizzes', [
            'id' =>  $original_quiz->id,
            'max_points' => 7,
        ]);

        //Question 1
        $this->assertDatabaseHas('questions', [
            'id' => $original_quiz->questions()->first()->id,
            'question' => 'New Question 1',
            'max_points' => 7,
        ]);

        $this->assertDatabaseHas('options', [
            'id' => $original_quiz->options()->first()->id,
            'option' => 'New Option 1',
            'question_id' => $original_quiz->questions()->first()->id,
            'points' => 5
        ]);

        $this->assertDatabaseHas('options', [
            'option' => 'New Option 2',
            'question_id' => $original_quiz->questions()->first()->id,
            'points' => 7
        ]);
    }

       /**
     * A test to update quiz unauthorised as a Student
     *
     * @test
     * @return void
     */
    public function test_update_quiz_student_unauthorised()
    {
        $text = Module::has('textbooks.texts')->whereHas('users', function ($query){
            $query->where('user_id', $this->studentUser->id);
        })->first()->textbooks()->first()
            ->texts()->whereHas('quizzes')->first();

        $original_quiz = Quiz::where('text_id', $text->id)->first();

        $quiz_array = [
            [
                'id' => $original_quiz->id,
                'questions' => [
                    [
                        'id' => $original_quiz->questions()->first()->id,
                        'question_text' => 'New Question 1',
                        'options' => [
                            [
                                'id' => $original_quiz->options()->first()->id,
                                'option_text' => 'New Option 1',
                                'points' => 5
                            ],
                            [
                                'id' => 'new',
                                'option_text' => 'New Option 2',
                                'points' => 7
                            ],
                        ]
                    ],
                ]
            ]
        ];

        $this->actingAs($this->studentUser)
            ->postJson('/api/quiz/'.$text->id, ['quizzes' => json_encode($quiz_array)])
            ->assertJson([
                'error' => 'Unauthorized'
            ])
            ->assertStatus(403);
    }

    /**
     *
     * A test to get the result of the quiz as a Student.
     * Parameter to choose a text to put a result in.
     * @test
     * @param null $changeText
     * @param null $changeStudent
     * @return void
     */
     public function test_result_student_authorised($changeText = null, $changeStudent = null)
     {
         Notification::fake();

         $text = ($changeText !== null ? $changeText : Module::has('textbooks.texts.quizzes.questions.options')->whereHas('users', function ($query){
             $query->where('user_id', $this->studentUser->id);
         })->first()->textbooks()->first()
             ->texts()->whereHas('quizzes')->first());

         $quiz_object = Quiz::where('text_id', $text->id)->first();

         $selectedOptionsIds = [];
         $points = [];

         foreach ($quiz_object->questions()->get() as $question){
             $selectedO = $question->options()->inRandomOrder()->first();
             array_push($selectedOptionsIds, $selectedO->id);
             array_push($points, $selectedO->points);
         }

         $array = [
             'selected' => json_encode($selectedOptionsIds),
             'quiz_id' => $quiz_object->id,
             'attempt_num' => 2
         ];

         $totalPointsCollected = array_sum($points);

         $user = ($changeStudent !== null ? $user = $changeStudent : $user = $this->studentUser);
         $this->actingAs($user)
             ->postJson('/api/quiz/result', $array)
             ->assertStatus(200)
             ->assertJsonFragment([
                 'totalPointsCollected' => $totalPointsCollected,
                 'user_score_id' => UserQuizScore::orderBy('id', 'desc')->first()->id
             ]);

         $this->assertDatabaseHas('user_quiz_score', [
             'user_id' => $user->id,
             'quiz_id' => $quiz_object->id,
             'attempt_number' => 2,
            'score' => $totalPointsCollected
         ]);

         Notification::assertSentTo($user, QuizResult::class);

     }

     /**
      * A test to get the last attempt of a quiz for a text for a Student.
      *
      * @test
      * @return void
      */
     public function test_getLastAttempt_student_authorised()
     {
         $this->test_result_student_authorised();

         $text = Module::has('textbooks.texts.quizzes')->whereHas('users', function ($query){
             $query->where('user_id', $this->studentUser->id);
         })->first()->textbooks()->first()
             ->texts()->whereHas('quizzes')->first();

         $quiz_id = Quiz::where('text_id', $text->id)->first()->id;

         $this->actingAs($this->studentUser)
             ->getJson('/api/quiz/getAttempt/'.$quiz_id)
             ->assertStatus(200)
             ->assertJsonFragment([
                 'attempt' => '2'
             ]);
     }

    /**
     * A test to get the last attempt of a quiz as empty (no previous attempt) for a text for a Student.
     *
     * @test
     * @return void
     */
    public function test_getLastAttempt_as_empty_student_authorised()
    {
        $text = Module::has('textbooks.texts.quizzes')->whereHas('users', function ($query){
            $query->where('user_id', $this->studentUser->id);
        })->first()->textbooks()->first()
            ->texts()->whereHas('quizzes')->first();

        $quiz_id = Quiz::where('text_id', $text->id)->first()->id;

        $this->actingAs($this->studentUser)
            ->getJson('/api/quiz/getAttempt/'.$quiz_id)
            ->assertStatus(200)
            ->assertJson([]);
    }

    /**
     * A test to get the last attempt of a quiz as permission denied for a text for a Student.
     *
     * @test
     * @return void
     */
    public function test_getLastAttempt_permission_denied_student_authorised()
    {
        $text = Module::has('textbooks.texts')->whereDoesntHave('users', function ($query){
            $query->where('user_id', $this->studentUser->id);
        })->inRandomOrder()->first()->textbooks()->inRandomOrder()->first()
            ->texts()->inRandomOrder()->first();

        $textToUse = $text;
        $this->test_create_quiz_admin_authorised($textToUse);
        $this->test_result_student_authorised($textToUse);

        $quiz_id = Quiz::where('text_id', $textToUse->id)->first()->id;

        $this->actingAs($this->studentUser)
            ->getJson('/api/quiz/getAttempt/'.$quiz_id)
            ->assertJsonFragment([
                'Error' => 'Permission denied to get attempt'
            ]);
    }

    /**
     * A test to get the last attempt of a quiz, quiz not found.
     *
     * @test
     * @return void
     */
    public function test_getLastAttempt_quiz_not_found_student_authorised()
    {
        $this->actingAs($this->studentUser)
            ->getJson('/api/quiz/getAttempt/'.rand(9999,10000))
            ->assertJsonFragment([
                'Error' => 'Quiz not found'
            ]);
    }

    /**
     * A test to view edit page for an Admin authorised
     *
     * @test
     * @return void
     */
    public function test_edit_quiz_admin_authorised()
    {
        $text = Text::has('quizzes.questions.options')->inRandomOrder()->first();

        $allQuiz = $text->quizzes()->with('questions', 'options')->get()
            ->map(function ($event) {
                return [
                    'id' => $event->id,
                    'questions' => $event->questions->map(function ($event2) {
                        return [
                            'id' => $event2->id,
                            'options' => $event2->options->map(function ($event3) {
                                return [
                                    'id' => $event3->id,
                                    'option_text' => $event3->option,
                                    'points' => $event3->points
                                ];
                            }),
                            'question_text' => $event2->question,
                        ];
                    })
                ];
            });

        $this->actingAs($this->adminUser)
            ->getJson('/api/quiz/edit/'.$text->id)
            ->assertSuccessful()
            ->assertJsonFragment(['quizzes' => $allQuiz->toArray()]);
    }


    /**
     * A test to view edit page for an Module Tutor authorised
     *
     * @test
     * @return void
     */
    public function test_edit_quiz_module_tutor_authorised()
    {
        $text = Module::has('textbooks.texts')->whereHas('users', function ($query){
            $query->where('user_id', $this->studentUser->id);
        })->inRandomOrder()->first()->textbooks()->inRandomOrder()->first()
            ->texts()->inRandomOrder()->first();

        $textToUse = $text;
        $this->test_create_quiz_admin_authorised($textToUse);

        $allQuiz = $textToUse->quizzes()->with('questions', 'options')->get()
            ->map(function ($event) {
                return [
                    'id' => $event->id,
                    'questions' => $event->questions->map(function ($event2) {
                        return [
                            'id' => $event2->id,
                            'options' => $event2->options->map(function ($event3) {
                                return [
                                    'id' => $event3->id,
                                    'option_text' => $event3->option,
                                    'points' => $event3->points
                                ];
                            }),
                            'question_text' => $event2->question,
                        ];
                    })
                ];
            });

        $this->actingAs($this->moduleTutorUser)
            ->getJson('/api/quiz/edit/'.$textToUse->id)
            ->assertSuccessful()
            ->assertJsonFragment(['quizzes' => $allQuiz->toArray()]);
    }

    /**
     * A test to view edit page for an Module Tutor permission denied
     *
     * @test
     * @return void
     */
    public function test_edit_quiz_module_tutor_permission_denied_authorised()
    {
        $text = Module::has('textbooks.texts')->whereDoesntHave('users', function ($query){
            $query->where('user_id', $this->studentUser->id);
        })->inRandomOrder()->first()->textbooks()->inRandomOrder()->first()
            ->texts()->inRandomOrder()->first();

        $textToUse = $text;
        $this->test_create_quiz_admin_authorised($textToUse);

        $this->actingAs($this->moduleTutorUser)
            ->getJson('/api/quiz/edit/'.$textToUse->id)
            ->assertSuccessful()
            ->assertJsonFragment(['Error' => 'Permission denied to edit quiz']);
    }

    /**
     * A test to view edit page for a Student unauthorised
     *
     * @test
     * @return void
     */
    public function test_edit_quiz_student_unauthorised()
    {
        $text = Module::has('textbooks.texts')->whereHas('users', function ($query){
            $query->where('user_id', $this->studentUser->id);
        })->inRandomOrder()->first()->textbooks()->inRandomOrder()->first()
            ->texts()->inRandomOrder()->first();

        $this->actingAs($this->studentUser)
            ->getJson('/api/quiz/edit/'.$text->id)
            ->assertStatus(403)
            ->assertJsonFragment(['error' => 'Unauthorized']);
    }

    /**
     * A test to edit page text quiz not found.
     *
     * @test
     * @return void
     */
    public function test_edit_quiz_not_found()
    {
        $this->actingAs($this->adminUser)
            ->getJson('/api/quiz/edit/'.rand(9999,1000))
            ->assertJsonFragment(['Error' => 'Text quiz not found']);

        $this->actingAs($this->moduleTutorUser)
            ->getJson('/api/quiz/edit/'.rand(9999,1000))
            ->assertJsonFragment(['Error' => 'Text quiz not found']);
    }

    /**
     * A test to check data for getLast5attempts for Admin
     * @test
     * @return void
     */
      public function test_getLast5attempts_admin_authorised()
      {
          $last5 = UserQuizScore::with('quiz.text.textbook')->with('user')
                ->orderBy('id', 'desc')->take(5)->get();

          $this->actingAs($this->adminUser)
              ->getJson('/api/quiz/getLast5attempts')
              ->assertJson($last5->toArray());
      }

    /**
     * A test to check data for getLast5attempts for Module Tutor
     * @test
     * @return void
     */
    public function test_getLast5attempts_module_tutor_authorised()
    {
        $moduleTextbooks = $this->moduleTutorUser->modules()->has('textbooks')->
        with('textbooks')->get()->pluck('textbooks');

        $extensiveReadingTextbooks = ExtensiveReadingCategory::has('textbooks')->
        with('textbooks')->get()->pluck('textbooks');

        $textbooks = $moduleTextbooks->merge($extensiveReadingTextbooks);
        $textbooks = call_user_func_array('array_merge', $textbooks->toArray());
        $allTextbookIds = collect($textbooks)->pluck('id');

        $last5 = UserQuizScore::with('quiz.text.textbook')->with('user')
            ->whereHas('quiz.text.textbook', function($query) use ($allTextbookIds){
                $query->whereIn('id',$allTextbookIds);
            })->orderBy('id', 'desc')->take(5)->get();

        $this->actingAs($this->moduleTutorUser)
            ->getJson('/api/quiz/getLast5attempts')
            ->assertJson($last5->toArray());
    }

    /**
     * A test to check data for getLast5attempts for Student
     * @test
     * @return void
     */
     public function test_getLast5attempts_student_authorised()
     {
        $last5 = UserQuizScore::with('quiz.text.textbook')
                ->where('user_id', $this->studentUser->id)
                ->orderBy('id', 'desc')->take(5)->get();

        $this->actingAs($this->studentUser)
            ->getJson('/api/quiz/getLast5attempts')
            ->assertJson($last5->toArray());
     }

    /**
     * A test to download the quiz result pdf as an Admin
     * @test
     * @return void
     */
    public function test_download_getResultPDF_admin()
    {
        $this->test_result_student_authorised();

        $quizScore = UserQuizScore::inRandomOrder()->first();

        $file_contents = base64_decode($quizScore->result);

        $this->actingAs($this->adminUser)
            ->getJson('/api/quiz/getResultPDF/'.$quizScore->id)
            ->assertSuccessful()
            ->header('Cache-Control', 'no-cache private')
            ->header('Content-Description', 'File Transfer')
            ->header('Content-Type', 'application/x-pdf')
            ->header('Content-Length', strlen($file_contents))
            ->header('Content-Disposition', 'inline; filename="result.pdf"')
            ->header('Content-Transfer-Encoding', 'binary');
    }

    /**
     * A test to download the quiz result pdf as an Module Tutor
     * @test
     * @return void
     */
    public function test_download_getResultPDF_module_tutor()
    {
        $this->test_result_student_authorised();

        $quizScore = UserQuizScore::where('user_id', $this->studentUser->id)->first();

        $file_contents = base64_decode($quizScore->result);

        $this->actingAs($this->moduleTutorUser)
            ->getJson('/api/quiz/getResultPDF/'.$quizScore->id)
            ->assertSuccessful()
            ->header('Cache-Control', 'no-cache private')
            ->header('Content-Description', 'File Transfer')
            ->header('Content-Type', 'application/x-pdf')
            ->header('Content-Length', strlen($file_contents))
            ->header('Content-Disposition', 'inline; filename="result.pdf"')
            ->header('Content-Transfer-Encoding', 'binary');
    }

    /**
     * A test to download the quiz result pdf as a Student
     * @test
     * @return void
     */
    public function test_download_getResultPDF_student()
    {
        $this->test_result_student_authorised();

        $quizScore = UserQuizScore::where('user_id', $this->studentUser->id)->first();

        $file_contents = base64_decode($quizScore->result);

        $this->actingAs($this->studentUser)
            ->getJson('/api/quiz/getResultPDF/'.$quizScore->id)
            ->assertSuccessful()
            ->header('Cache-Control', 'no-cache private')
            ->header('Content-Description', 'File Transfer')
            ->header('Content-Type', 'application/x-pdf')
            ->header('Content-Length', strlen($file_contents))
            ->header('Content-Disposition', 'inline; filename="result.pdf"')
            ->header('Content-Transfer-Encoding', 'binary');
    }

    /**
     * A test to download the quiz result pdf permission denied as Module Tutor
     * @test
     * @return void
     */
    public function test_download_getResultPDF_permission_denied_module_tutor()
    {
        $text = Module::has('textbooks.texts')->whereDoesntHave('users', function ($query){
            $query->where('user_id', $this->moduleTutorUser->id);
        })->inRandomOrder()->first()->textbooks()->inRandomOrder()->first()
            ->texts()->inRandomOrder()->first();

        $textToUse = $text;
        $this->test_create_quiz_admin_authorised($textToUse);

        $this->test_result_student_authorised($textToUse);

        $quiz_id = $textToUse->quizzes()->where('text_id',$textToUse->id)->first()->id;
        $quizScore = UserQuizScore::where('quiz_id', $quiz_id)->first();

        $this->actingAs($this->moduleTutorUser)
            ->getJson('/api/quiz/getResultPDF/'.$quizScore->id)
            ->assertJsonFragment([
                'Error' => 'Permission denied to download quiz results'
            ]);
    }

    /**
     * A test to download the quiz result pdf permission denied as Student not in text module
     * @test
     * @return void
     */
    public function test_download_getResultPDF_permission_denied_student_not_in_module()
    {
        $text = Module::has('textbooks.texts')->whereDoesntHave('users', function ($query){
            $query->where('user_id', $this->studentUser->id);
        })->inRandomOrder()->first()->textbooks()->inRandomOrder()->first()
            ->texts()->inRandomOrder()->first();

        $textToUse = $text;
        $this->test_create_quiz_admin_authorised($textToUse);

        $this->test_result_student_authorised($textToUse);

        $quiz_id = $textToUse->quizzes()->where('text_id',$textToUse->id)->first()->id;
        $quizScore = UserQuizScore::where('quiz_id', $quiz_id)->first();

        $this->actingAs($this->studentUser)
            ->getJson('/api/quiz/getResultPDF/'.$quizScore->id)
            ->assertJsonFragment([
                'Error' => 'Permission denied to download quiz results'
            ]);
    }

    /**
     * A test to download the quiz result pdf permission denied as Student not completed the quiz
     * @test
     * @return void
     */
    public function test_download_getResultPDF_permission_denied_student_not_completed_quiz()
    {
        $text = Module::has('textbooks.texts')->whereHas('users', function ($query){
            $query->where('user_id', $this->studentUser->id);
        })->inRandomOrder()->first()->textbooks()->inRandomOrder()->first()
            ->texts()->inRandomOrder()->first();

        $textToUse = $text;
        $this->test_create_quiz_admin_authorised($textToUse);

        $anotherUser = User::whereHas('role', function($q){
            $q->where('name', 'Student');
        })->where('id', '!=', $this->studentUser->id)->first();

        $this->test_result_student_authorised($textToUse, $anotherUser);

        $quiz_id = $textToUse->quizzes()->where('text_id',$textToUse->id)->first()->id;
        $quizScore = UserQuizScore::where('quiz_id', $quiz_id)->first();

        $this->actingAs($this->studentUser)
            ->getJson('/api/quiz/getResultPDF/'.$quizScore->id)
            ->assertJsonFragment([
                'Error' => 'Permission denied to download quiz results as you did not complete that quiz'
            ]);
    }

    /**
     * A test to download the quiz result pdf quiz not found.
     * @test
     * @return void
     */
    public function test_download_getResultPDF_quiz_not_found()
    {

        $this->actingAs($this->adminUser)
            ->getJson('/api/quiz/getResultPDF/'.rand(9999,10000))
            ->assertJsonFragment([
                'Error' => 'Quiz results not found!'
            ]);

        $this->actingAs($this->moduleTutorUser)
            ->getJson('/api/quiz/getResultPDF/'.rand(9999,10000))
            ->assertJsonFragment([
                'Error' => 'Quiz results not found!'
            ]);

        $this->actingAs($this->studentUser)
            ->getJson('/api/quiz/getResultPDF/'.rand(9999,10000))
            ->assertJsonFragment([
                'Error' => 'Quiz results not found!'
            ]);
    }

}
