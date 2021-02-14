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
            Quiz: {{numOfQuestions++}}
            <div v-for="question in quiz.questions">
                <b>{{question.question}}</b>
                <div v-for="op in allOptions(quiz.options, question.id)">
                    {{op.option}}<input type="radio" name="op"/>
                </div>
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