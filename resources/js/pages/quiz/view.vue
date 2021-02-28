<template>
    <card v-if="isLoaded">
        <h5 class="card-header">
            <div id="title" class="d-flex justify-content-between align-items-center">Quiz
                <button @click="$router.go(-1)" type="button" class="btn btn-sm btn-primary">Back</button>
            </div>
            <br/>
        </h5>
        <br/>
        Attempt Number: {{attempt_num+1}}
        <div v-if="form.quiz || form.quiz.length">
            <form @submit.prevent="submit" @keydown="form.onKeydown($event)">
        <div v-for="(question, index) in form.quiz.questions">
            <table class="table table-bordered table-question">
                <thead>
                <th width="20%" class="text-left text-bold align-top">Question: {{index+1}}</th>
                <th class="text-left text-bold">
                    <span>{{question.question}}</span></th></thead>
                <tbody><tr>
                    <td class="text-left">Options</td> <td class="text-left">
                    <div v-for="op in allOptions(form.quiz.options, question.id)" class="form-check">
                    <input type="radio" :name="question.id" :value="op.id" v-model="form.selected[index]">
                        <label>{{op.option}}</label>
                    </div>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
            <v-button class="form-control" :loading="form.busy" type="success">Submit</v-button>
            </form>
            <sweet-modal ref="success" icon="success">
                You've scored: {{totalPointsCollected}} out of a possible {{totalAvailablePoints}}<br/>
                We've emailed you the results as a PDF and you can view and download right now. <br/><br/>
                <button class="btn btn-success" v-on:click="$refs.viewResults.open()">View results</button>
                <button class="btn btn-success">Download PDF results</button>
                <button class="btn btn-success">Go to dashboard</button>
            </sweet-modal>
            <sweet-modal ref="viewResults">
                <PDFViewer :fileName="fileName" :path="path"/>
            </sweet-modal>
        </div>
        <div v-else>
            There is no quiz is available for this text.
            <router-link :to="{ name: 'home' }">
                <button class="btn btn-success">Go back to dashboard</button>
            </router-link>
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
    import Form from 'vform'
    import PDFViewer from '~/components/PDFViewer';
    export default {
        middleware: 'auth',
        computed: mapGetters({
            role: 'auth/role'
        }),
        components:{
            PDFViewer,
        },
        data: () => ({
            isLoaded: false,
            form: new Form({
                quiz: [],
                busy: false,
                selected: [],
            }),
            fileName: '',
            quiz_id: 0,
            path: '/lib/pdf/web/viewer.html',
            errorMessage: '',
            attempt_num: 0,
            successMessage: '',
            numOfQuestions: 1,
            totalPointsCollected: '',
            totalAvailablePoints: 0,
        }),
        created() {
            let self = this;
            axios.get(`/api/quiz/${this.$route.params.id}`)
                .then(response => {
                    if(response.data.Error !== undefined){
                        this.errorMessage = response.data.Error;
                        this.$refs.error.open();
                    }else{
                        if(response.data.quiz !== null){
                            this.form.quiz = response.data.quiz;
                            this.getAttempt(self);
                            this.getTotal(self);
                        }else{
                            this.form.quiz = '';
                        }

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
            },
            async submit(){
                this.form.busy = true;
                let formData = new FormData();
                formData.append('selected', JSON.stringify(this.form.selected));
                formData.append('quiz_id', JSON.stringify(this.form.quiz.id));
                formData.append('attempt_num', JSON.stringify(this.attempt_num+1));
                await axios.post('/api/quiz/result',formData,
                    {})
                    .then(response => {
                        console.log(response);
                        this.totalPointsCollected = response.data.totalPointsCollected;
                        this.fileName =`/api/quiz/getResultPDF/${this.form.quiz.id}`
                        this.$refs.success.open();
                    })
                    .catch(error => {
                        console.log(error.response)
                    });
                this.form.busy = false;
            },
            getTotal(self){
                const qpoints = [];
                const max = [];
                this.form.quiz.questions.forEach(function(b){
                    let t = [];
                    self.allOptions(self.form.quiz.options, b.id).forEach(function(a){
                        t.push(a.points)
                    });
                    qpoints.push(t);
                });
                qpoints.forEach(g => max.push(Math.max.apply(Math, g)));
                self.totalAvailablePoints = max.reduce((a, b) => a + b, 0);
            },
            getAttempt(self){
                //Gets attempt number of previous attempts for a user for a particular quiz.
                axios.get('/api/quiz/getAttempt/'+self.form.quiz.id)
                    .then(response => {
                        if(Object.keys(response.data).length){
                            self.attempt_num = response.data.attempt;
                        }else{
                            self.attempt_num = 0;
                        }
                        this.isLoaded = true;
                    }).catch(function (response) {
                    //handle error
                    console.log(response);
                });
            }
        }
    }
</script>

<style scoped>
</style>