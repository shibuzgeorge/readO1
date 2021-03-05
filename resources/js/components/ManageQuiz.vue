<template>
    <form @submit.prevent="submitQuizzes()" @keydown="form.onKeydown($event)">
        <div v-if="text_id !== 'Please a text...'">
            <div v-if="isLoaded">
            <br/><button class="btn btn-success" type="button" v-if="role!=='Student'" v-on:click="addQuiz()">Add quiz</button>
            <div class="quiz"
                 v-for="(quiz, counter) in form.quizzes"
                 v-bind:key="counter">
                <span @click="removeQuiz(counter)">x</span>
                <h3><label>Quiz Number: {{counter+1}}.</label></h3>
                <div class="question"
                     v-for="(question, qcounter) in quiz.questions"
                     v-bind:key="qcounter">
                    <span @click="removeQuestion(quiz,qcounter)">x</span>
                    <label >Question Number: {{qcounter+1}}.</label>
                    <input type="text" v-model="question.question_text" required/><button class="btn btn-success" type="button" v-if="role!=='Student'" v-on:click="addOption(question)">Add a Option</button>
                    <div class="options"
                         v-for="(option, ocounter) in question.options"
                         v-bind:key="ocounter">
                        <span @click="removeOption(question,ocounter)">x</span>
                        <label >Option: {{ocounter+1}}.</label>
                        <input type="text" v-model="option.option_text" required/>
                        <label >Points:</label>
                        <input type="number" v-model="option.points" required/>
                    </div>
                </div>
                <button class="btn btn-success" type="button" v-if="role!=='Student'" v-on:click="addQuestion(quiz)">Add another question</button>
            </div><br/>
            <v-button class="form-control" :loading="form.busy" type="success">
                Update
            </v-button>
            </div>
            <div v-else>
                <clip-loader color="black"/>
            </div>
        </div>
    </form>
</template>

<script>
    import axios from 'axios'
    import Form from 'vform'
    import { mapGetters } from 'vuex'
    export default {
        name: "ManageQuiz",
        props:{
            text_id: [Number, String],
            parent: Number,
        },
        computed : {
            ...mapGetters({
                role: 'auth/role'
            }),
        },
        watch:{
            text_id: function(){
                this.isLoaded = false;
                if(typeof this.text_id === 'number'){
                    this.getQuizFromDatabase(this.text_id);
                }
            }
        },
        data: () => ({
            isLoaded: false,
            form: new Form({
                quizzes: [],
                busy: false,
            }),
            successMessage: '',
        }),
        methods: {
            addQuiz(){
                this.form.quizzes.push({
                    id: "new",
                    questions: [{
                        question_text: '',
                        options: [{
                            option_text: '',
                            points: '',
                        }],
                    }
                    ],
                })
            },
            addQuestion(quiz){
                quiz.questions.push({
                    id: "new",
                    question_text: '',
                    options: [{
                        option_text: '',
                        points: '',
                    }],
                })
            },
            removeQuestion(quiz,counter){
                quiz.questions.splice(counter,1);
            },
            addOption(question){
                question.options.push({
                    id: "new",
                    option_text: '',
                    points: '',
                })
            },
            removeOption(question,counter){
                question.options.splice(counter,1);
            },
            removeQuiz(counter){
                this.form.quizzes.splice(counter,1);
            },
            async submitQuizzes(){
                this.form.busy = true;
                await axios.post(`/api/quiz/${this.text_id}`, {
                    quizzes: JSON.stringify(this.form.quizzes),
                })
                    .then(response => {
                        if(this.parent === 3){
                            this.$parent.$parent.$parent.successMessage = response.data.Success;
                            this.$parent.$parent.$parent.$refs.manageQuiz.close();
                            this.$parent.$parent.$parent.$refs.success.open();
                        }else if(this.parent === 2){
                            this.$parent.$parent.successMessage = response.data.Success;
                            this.$parent.$parent.$refs.manageQuiz.close();
                            this.$parent.$parent.$refs.success.open();
                        }
                        this.form.busy = false;
                    })
                    .catch(error => {
                        console.log(error.response)
                    });
            },
            getQuizFromDatabase(text_id){
                axios.get(`/api/quiz/edit/${text_id}`)
                    .then(response => {
                        this.form.quizzes = response.data.quizzes;
                        this.isLoaded = true;
                    }).catch(error => {
                    console.log(error.response)
                });
            }
        }
    }
</script>

<style scoped>

</style>