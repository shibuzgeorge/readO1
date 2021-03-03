<template>
  <card v-if="isLoaded">
    <div class="card-header d-flex justify-content-between align-items-center">
    <h5 id="title">Dashboard</h5>
    </div>
    <div class="wrapper">
      <div class="container align-items-center">

        <h3>Welcome back - {{user.name}}</h3>
        <br>
          <GChart type="LineChart"
                  :data="quizChartData"
                  :options="quizChartOptions"/>
        <div class="row">
            <div class="col-md-6">
                <div v-if="list_of_reading_attempts.length !==0">
                <h5>Current Reading Sessions:</h5>
                <ul v-for="(textbooks, index) in list_of_reading_attempts">
                    <li> Textbook:  {{index}}</li>
                        <ul v-for="(texts, index) in textbooks">
                            <li>Text: {{index}}</li>
                            <ul>
                                <li v-for="(attempt, index) in texts">
                                    Attempt Number: {{attempt.attempt_number}}
                                    Time taken: {{attempt.time_taken}}
                                </li>
                            </ul>
                        </ul>
                  </ul>
                </div>
            </div>
            <div class="col-md-6">
                <div v-if="list_of_quiz_scores.length !==0">
                    <h5>Attempted Quiz Scores:</h5>
                    <ul v-for="(textbooks, index) in list_of_quiz_scores">
                        <li> Textbook:  {{index}}</li>
                        <ul v-for="(texts, index1) in textbooks">
                            <li>Text: {{index1}}</li>
                            <ul v-for="(quizzes, quiz_id, index) in texts">
                            <li>Quiz Version: {{index+1}}</li>
                                <ul v-for="user_attempt in quizzes">
                                    <li>
                                        Attempt Number: {{user_attempt.attempt_number}}
                                        Score: {{user_attempt.score}}
                                        <button class="btn btn-success" v-on:click="getResults(user_attempt.id)">View results</button>
                                    </li>
                                </ul>
                           </ul>
                        </ul>
                    </ul>
                </div>
            </div>
        </div>

      </div>

    </div>
      <sweet-modal ref="viewResults" width="100%">
          <PDFViewer :fileName="fileName" :path="path"/>
      </sweet-modal>
  </card>
  <div v-else style="text-align: center;">
    <clip-loader color="black"/>
  </div>

</template>

<script>
    import axios from 'axios'
    import { mapGetters } from 'vuex'
    import PDFViewer from '~/components/PDFViewer';

    export default {
    middleware: 'auth',
        components:{
            PDFViewer,
        },
    computed: {
        ...mapGetters({
            role: 'auth/role',
            user: 'auth/user'
        }),
    },
    data: () => ({
         isLoaded: false,
        fileName: '',
        path: '/lib/pdf/web/viewer.html',
        list_of_reading_attempts: [],
        list_of_quiz_scores: [],
         quizChartData: [
             ['Attempt', 'Quiz Score', 'Time taken'],
             ['1', 1, [0,1,13]],
             ['2', 7, [0,1,3]],
             ['3', 8, [0,0,50]],
             ['4', 3, [0,0,25]]
         ],
        quizChartOptions: {
            title: 'Latest quiz scores',
            series: {
                0: {targetAxisIndex: 0},
                1: {targetAxisIndex: 1}
            },
            vAxes: {
                // Adds titles to each axis.
                0: {title: 'Score'},
                1: {title: 'Time taken'}
            },
            hAxis: {
               title: 'Attempt Number'
            },
        },

    }),
    created() {
        axios.get(`/api/text/getAllAttemptsForCurrentUser/`)
            .then(response => {
                if(Object.keys(response.data).length){
                    this.list_of_reading_attempts = response.data;
                }else{
                    this.list_of_reading_attempts = [];
                }

            }).catch(function (response) {
            //handle error
            console.log(response);
        });

        axios.get(`/api/quiz/getAllAttemptsForCurrentUser/`)
            .then(response => {
                if(Object.keys(response.data).length){
                    this.list_of_quiz_scores = response.data;
                }else{
                    this.list_of_quiz_scores = [];
                }
                this.isLoaded = true;
            }).catch(function (response) {
            //handle error
            console.log(response);
        });
    },
    methods: {
        getResults(id){
            this.fileName ='/api/quiz/getResultPDF/'+id
            this.$refs.viewResults.open()
        }
    }
}
</script>