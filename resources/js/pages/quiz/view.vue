<template>
    <card v-if="isLoaded">
        <h5 class="card-header">
            <div id="title" class="d-flex justify-content-between align-items-center">Quiz
                <button @click="$router.go(-1)" type="button" class="btn btn-sm btn-primary">Back</button>
            </div>
            <br/>
        </h5>

        <br/>
        <div v-if="quizzes || quizzes.length" v-for="quiz in quizzes">
            <h2 class="text-center">Quiz: {{numOfQuizzes++}}</h2>
        <div v-for="question in quiz.questions">
            <table class="table table-bordered table-question">
                <thead>
                <th width="20%" class="text-left text-bold align-top">Question: {{numOfQuestions++}}</th>
                <th class="text-left text-bold">
                    <span>{{question.question}}</span></th></thead>
                <tbody><tr>
                    <td class="text-left">Options</td> <td class="text-left">
                    <div v-for="op in allOptions(quiz.options, question.id)" class="form-check">
                    <input type="radio" :name="question.id" id="option642" class="form-check-input" value="642"> <label for="option642" class="form-check-label">
                        {{op.option}}
                </label>
                    </div>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        </div>
        <div v-else>
            No quiz is available for this textbook.
        </div>
    </card>
    <div v-else>
        <sweet-modal ref="error" v-on:close="$router.push({name: 'home'})" icon="error">
            {{errorMessage}}
        </sweet-modal>
        <clip-loader color="black"/>
    </div>
</template>

<script>
    import axios from 'axios'
    import { mapGetters } from 'vuex'
    export default {
        middleware: 'auth',
        computed: mapGetters({
            role: 'auth/role'
        }),
        data: () => ({
            isLoaded: false,
            quizzes: [],
            errorMessage: '',
            numOfQuestions: 1,
            numOfQuizzes: 1,
            selected: '',
        }),
        created() {
            let self = this
            axios.get(`/api/quiz/${this.$route.params.id}`)
                .then(response => {
                    if(response.data.Error !== undefined){
                        this.errorMessage = response.data.Error;
                        this.$refs.error.open();
                    }else{
                        this.isLoaded = true;
                        this.quizzes = response.data.quizzes;
                    }
                }).catch(function (response) {
                //handle error
                console.log(response);
            });
        },
        methods:{
            allOptions(options, question_id){
                return options.filter(item => {
                    return item.question_id === question_id
                });
            }
        }
    }
</script>

<style scoped>
</style>