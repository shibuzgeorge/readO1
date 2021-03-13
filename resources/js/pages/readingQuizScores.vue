<template>
    <card v-if="isLoaded">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 id="title">Full list of scores and reading times</h5>
        </div>
        <div>
            List of recent reading sessions:
            <div class="form-inline d-flex justify-content-between align-items-center">
            <input class="form-control col-lg-4" type="search" v-model="searchReadingSessionQuery" placeholder="Search..."/>
            <select class="form-control col-lg-3 ml-2" v-model="filterByTextbook">
                <option>Filter by textbook...</option>
                <option v-for="textbook in list_of_textbooks" :value="textbook.title" :key="textbook.title">
                    {{ textbook.title }}
                </option>
            </select>
            </div>
            <table class="table">
                <thead>
                <tr>
                    <th scope="col" v-if="role==='Admin' || role==='Module Tutor'">Student</th>
                    <th scope="col">Attempt Number</th>
                    <th scope="col">Textbook</th>
                    <th scope="col">Text</th>
                    <th scope="col">Time Taken</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="attempt1 in list_of_reading_attempts_paginated">
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
            <paginate style="display: flex; justify-content: center;" :items="resultQuery" :pageSize="sizePage" @changePage="onChangePage"></paginate>

            List of recent quiz attempts:
            <div class="form-inline d-flex justify-content-between align-items-center">
                <input class="form-control col-lg-4" type="search" v-model="searchQuizScoreQuery" placeholder="Search..."/>
                <select class="form-control col-lg-3 ml-2" v-model="filterByTextbookQuiz">
                    <option>Filter by textbook...</option>
                    <option v-for="textbook in list_of_textbooks_quiz" :value="textbook.title" :key="textbook.title">
                        {{ textbook.title }}
                    </option>
                </select>
            </div>
            <table class="table">
                <thead>
                <tr>
                    <th scope="col" v-if="role==='Admin' || role==='Module Tutor'">Student</th>
                    <th scope="col">Attempt Number</th>
                    <th scope="col">Textbook</th>
                    <th scope="col">Text</th>
                    <th scope="col">Score</th>
                    <th scope="col">View Results</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="attempt1 in list_of_quiz_scores_paginated">
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
            <paginate style="display: flex; justify-content: center;" :items="resultQuery2" :pageSize="sizePage" @changePage="onChangePage2"></paginate>

        </div>
        <sweet-modal ref="viewResults" width="100%">
            <PDFViewer :fileName="fileName" :path="path"/>
        </sweet-modal>
    </card>
    <div v-else>
        <clip-loader color="black"></clip-loader>
    </div>
</template>

<script>
    import axios from 'axios'
    import { mapGetters } from 'vuex'
    import PDFViewer from '~/components/PDFViewer';

    export default {
        middleware: 'auth',
        data: () => ({
             list_of_reading_attempts: [],
            list_of_reading_attempts_paginated: [],
            list_of_quiz_scores_paginated: [],
             list_of_quiz_scores: [],
             isLoaded: false,
            sizePage: 5,
            searchReadingSessionQuery: null,
            searchQuizScoreQuery: null,
            filterByTextbook: 'Filter by textbook...',
            filterByTextbookQuiz: 'Filter by textbook...',
            list_of_textbooks: [],
            list_of_textbooks_quiz: [],
            fileName: '',
            path: '/lib/pdf/web/viewer.html',
         }),
        components: {
            PDFViewer,
        },
        computed: {
            ...mapGetters({
                role: 'auth/role',
                user: 'auth/user'
            }),
        resultQuery() {
            if (this.searchReadingSessionQuery && this.filterByTextbook !== 'Filter by textbook...') {
                return  this.list_of_reading_attempts.filter(item => item.text.textbook.title === this.filterByTextbook &&
                    this.searchReadingSessionQuery.toLowerCase().split(' ').every(
                        v => item.text.textbook.title.toLowerCase().includes(v) ||
                            item.text.title.toLowerCase().includes(v))
                )
            } else if (this.searchReadingSessionQuery) {
                return this.list_of_reading_attempts.filter((item) => {
                    return this.searchReadingSessionQuery.toLowerCase().split(' ').every(
                        v => item.text.textbook.title.toLowerCase().includes(v) ||
                            item.text.title.toLowerCase().includes(v))
                })
            }
            else if (this.filterByTextbook !== 'Filter by textbook...') {
                return  this.list_of_reading_attempts.filter(item => item.text.textbook.title === this.filterByTextbook);
            }
            else {
                return this.list_of_reading_attempts;
            }
        },
            resultQuery2() {
                if (this.searchQuizScoreQuery && this.filterByTextbookQuiz !== 'Filter by textbook...') {
                    return  this.list_of_quiz_scores.filter(item => item.quiz.text.textbook.title === this.filterByTextbookQuiz &&
                        this.searchQuizScoreQuery.toLowerCase().split(' ').every(
                            v => item.quiz.text.textbook.title.toLowerCase().includes(v) ||
                                item.quiz.text.title.toLowerCase().includes(v))
                    )
                } else if (this.searchQuizScoreQuery) {
                    return this.list_of_quiz_scores.filter((item) => {
                        return this.searchQuizScoreQuery.toLowerCase().split(' ').every(
                            v => item.quiz.text.textbook.title.toLowerCase().includes(v) ||
                                item.quiz.text.title.toLowerCase().includes(v))
                    })
                }
                else if (this.filterByTextbookQuiz !== 'Filter by textbook...') {
                    return  this.list_of_quiz_scores.filter(item => item.quiz.text.textbook.title === this.filterByTextbookQuiz);
                }
                else {
                    return this.list_of_quiz_scores;
                }
            }
        },
        created() {
            axios.get(`/api/text/getAllAttemptsForCurrentUser/`)
                .then(response => {
                    if (Object.keys(response.data.attempts).length) {
                        this.list_of_reading_attempts = response.data.attempts;
                        this.list_of_textbooks = response.data.textbooks;
                    } else {
                        this.list_of_reading_attempts = [];
                    }

                }).catch(function (response) {
                //handle error
                console.log(response);
            });

        axios.get(`/api/quiz/getAllAttemptsForCurrentUser/`)
        .then(response => {
            if (Object.keys(response.data).length) {
                this.list_of_quiz_scores = response.data.attempts;
                this.list_of_textbooks_quiz = response.data.textbooks;
            } else {
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
                this.fileName ='/api/quiz/getResultPDF/'+id;
                this.$refs.viewResults.open()
            },
            onChangePage(pageOfItems) {
                this.list_of_reading_attempts_paginated = pageOfItems;
            },
            onChangePage2(pageOfItems) {
                this.list_of_quiz_scores_paginated = pageOfItems;
            },
        }
    }
</script>

<style scoped>

</style>