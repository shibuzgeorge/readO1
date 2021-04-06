<template>
        <div>
            <h1>Quiz</h1>

            After you've finished reading a text, you will be presented with a quiz based on the text you’ve read (if available). <br/>
            You’ll have options to select for each question and once you’ve completed quiz,
            a score will be calculated based on points. <br/>
            Results of the quiz can be viewed, downloaded and emailed to you as a PDF attachment <br/>
            In the dashboard you can see your score for each text (PDF result feedback) you’ve completed, the time taken
            and the number of attempts on that text.<br/><br/>

            <vueper-slides
                    arrows-outside
                    bullets-outside
                    :slide-ratio="1 / 2">

                <vueper-slide key="1" title="Test yourself">
                    <template v-slot:content>
                        <div>
                            <img src="/screenshots/quiz.png"/>
                        </div>
                    </template>
                </vueper-slide>
                <vueper-slide key="2" title="Multiple choice question">
                    <template v-slot:content>
                        <div>
                            <img src="/screenshots/quiz_result_1.png"/>
                        </div>
                    </template>
                </vueper-slide>
                <vueper-slide key="3" title="Feedback given instantly and emailed to you">
                    <template v-slot:content>
                        <div>
                            <img src="/screenshots/quiz_result_2.png"/>
                        </div>
                    </template>
                </vueper-slide>
            </vueper-slides>
        </div>
</template>


<script>
    export default {
        name: "QuizGuide"
    }
</script>

<style scoped>

</style>