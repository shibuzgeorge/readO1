<template xmlns:v-slot="http://www.w3.org/1999/XSL/Transform">
  <card v-if="isLoaded">
    <div class="card-header d-flex justify-content-between align-items-center">
    <h5 id="title">Dashboard</h5>
    </div>
    <div class="wrapper">
      <div class="container align-items-center">

        <h3>Welcome back - {{user.name}}</h3>
        <br>
          Check out these recent added textbooks:
          <vueper-slides
                  class="no-shadow"
                  :visible-slides="3"
                  slide-multiple
                  :gap="3"
                  :touchable="false"
                  :slide-ratio="1 / 4"
                  :dragging-distance="200"
                  :breakpoints="{ 800: { visibleSlides: 2, slideMultiple: 2 } }">
              <vueper-slide v-for="textbook in textbooks" :key="i" :title="textbook.title">
                  <template v-slot:content>
                      <div class="card-body">
                          <router-link :to="{ name: 'textbook.show', params: {id: textbook.id} }">
                              <h5 class="card-title">{{textbook.title}}</h5>
                              <h6 class="card-subtitle mb-2 text-muted">{{textbook.description }}</h6>
                          </router-link>
                          <router-link :to="{ name: 'textbook.edit', params: {id: textbook.id} }">
                              <a v-show="role==='Admin' || role==='Module Tutor'" href="edit" class="card-link">Edit</a>
                          </router-link>
                          <a @click="del(textbook.id)" v-show="role==='Admin' || role==='Module Tutor'" href="#" class="card-link">Delete</a><br/>

                      </div>
          </template>
              </vueper-slide>
          </vueper-slides>

          <GChart type="LineChart"
                  :data="quizChartData"
                  :options="quizChartOptions"/>
        <div class="row">
            <div class="col-md-6">
                Reading Sessions
                <table class="table">
                    <thead>
                    <tr>
                        <th v-if="role==='Admin' || role==='Module Tutor'" scope="col">User</th>
                        <th scope="col">Attempt Number</th>
                        <th scope="col">Textbook</th>
                        <th scope="col">Text</th>
                        <th scope="col">Time Taken</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="attempt1 in last5ReadingAttempts">
                        <td v-if="role==='Admin' || role==='Module Tutor'">{{attempt1.user.name}}</td>
                        <td>{{attempt1.attempt_number}}</td>
                        <td>
                            <router-link :to="{ name: 'textbook.show', params: {id: attempt1.text.textbook.id} }">
                                {{attempt1.text.textbook.title}}
                            </router-link>
                        </td>
                        <td>
                            <router-link :to="{ name: 'text.show', params: {id: attempt1.text.id} }">
                                {{attempt1.text.title}}
                            </router-link>
                        </td>
                        <td>{{attempt1.time_taken}}</td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <div class="col-md-6">
                Quiz Attempts
                <table class="table">
                    <thead>
                    <tr>
                        <th v-if="role==='Admin' || role==='Module Tutor'" scope="col">User</th>
                        <th scope="col">Attempt Number</th>
                        <th scope="col">Textbook</th>
                        <th scope="col">Text</th>
                        <th scope="col">Score</th>
                        <th scope="col">View Results</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="attempt1 in last5QuizAttempts">
                        <td v-if="role==='Admin' || role==='Module Tutor'">{{attempt1.user.name}}</td>
                        <td>{{attempt1.attempt_number}}</td>
                        <td>
                            <router-link :to="{ name: 'textbook.show', params: {id: attempt1.quiz.text.textbook.id} }">
                                {{attempt1.quiz.text.textbook.title}}
                            </router-link>
                        </td>
                        <td>
                            <router-link :to="{ name: 'text.show', params: {id: attempt1.quiz.text.id} }">
                            {{attempt1.quiz.text.title}}
                            </router-link>
                        </td>
                        <td>{{attempt1.score}}/{{attempt1.quiz.max_points}}</td>
                        <td><button class="btn btn-success" v-on:click="getResults(attempt1.id)">View results</button></td>
                    </tr>
                    </tbody>
                </table>
                <router-link :to="{ name: 'readingQuizScores' }">
                    View all time and quiz scores
                </router-link>
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
        last5QuizAttempts: [],
        last5ReadingAttempts: [],
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
        textbooks: [],

    }),
    created() {
        axios.get(`/api/quiz/getLast5attempts/`)
            .then(response => {
                    this.last5QuizAttempts = response.data;

            }).catch(function (response) {
            //handle error
            console.log(response);
        });

        axios.get(`/api/text/getLast5attempts/`)
            .then(response => {
                this.last5ReadingAttempts = response.data;
                this.isLoaded = true;
            }).catch(function (response) {
            //handle error
            console.log(response);
        });

        axios.get('api/textbook/getLast5recent/')
            .then(response => {
                this.isLoaded = true;
                this.textbooks = response.data.textbooks
            }).catch(error => {
            console.log(error.response)
        });
    },
    methods: {
        getResults(id){
            this.fileName ='/api/quiz/getResultPDF/'+id;
            this.$refs.viewResults.open()
        }
    }
}
</script>