<template>
    <card v-if="isLoaded">
        <h5 class="card-header">
            <div id="title" class="d-flex justify-content-between align-items-center">
                Title: {{title}}
               <div>Textbook: <router-link :to="{ name: 'textbook.show', params: {id: textbook.id} }">{{textbook.title}}</router-link></div>
                <button @click="$router.go(-1)" type="button" class="btn btn-sm btn-primary">Back</button>
            </div>
            <br/>
            <div class="d-flex justify-content-between align-items-center">
                Description: {{description}}
                <a style="float: right;" @click="manageQuiz()"><button class="btn btn-success" v-if="role==='Admin'|| role==='Module Tutor'">Edit Quiz</button></a>
                <router-link :to="{ name: 'text.edit', params: {id: $route.params.id} }">
                    <button class="btn btn-success" v-if="role==='Admin'|| role==='Module Tutor'">Edit</button>
                </router-link>
            </div>
        </h5>

        <br/>
        <div v-if="file">
            <div v-if="role==='Student'" class="d-flex justify-content-between align-content-between">
                 <button class="btn btn-sm btn-primary" @click="openGuide()" v-if="role==='Student'">Guide</button>
                <h5>Attempt Number: {{attempt_num+1}}</h5> <div v-if="attempt_num===0">No previous attempts</div><div v-if="attempt_num!==0">Last time taken: {{last_time_taken}}</div>
            </div>

            <div v-if="role==='Student' && timer===undefined" style="margin-top: 100px; margin-bottom: 70px; margin-right: 80px;" class="d-flex justify-content-center align-items-center">
                <button class="btn btn-success" @click="start" v-if="role==='Student'">Start</button>
            </div>

            <div v-if="timer !== undefined || role==='Admin' || role==='Module Tutor'">
            <div style="text-align: center; margin-right: 100px;">
                <label style="text-align: center;">Skimming: <input type="radio" v-model="selection" value="pdf"></label>
                <label>Scanning: <input type="radio" v-model="selection" value="text"></label>
                <label>Speed reading (wpm): <input type="radio" v-model="selection" value="speedreading"></label>
            </div>

            <PDFViewer :fileName="fileName" :path="path" v-show="selection === 'pdf'"/>
            <div v-show="selection === 'text'" style="font-family: sans-serif; font-size: 25px;">
            <pre>
                {{text}}
            </pre>

            </div>
            <div v-show="selection === 'speedreading'">
                <SpeedReader :text=text />

            </div>
            <div class="card-footer fixed-bottom">
                <div class="justify-content-between align-items-center">
                <button class="btn btn-success" style="float:right;" @click="finishedReading(formattedElapsedTime)" v-if="role==='Student'">Finished Reading?</button>
                <b v-if="role==='Student'">Time taken: {{formattedElapsedTime}}</b>
                </div>
            </div>
        </div>
        </div>
        <div v-else>
            No file has been uploaded for this text
        </div>
        <sweet-modal ref="success" icon="success">
            {{successMessage}}
        </sweet-modal>
        <sweet-modal ref="manageQuiz">
                <manage-quiz :text_id="text_id" :parent="3"></manage-quiz>
        </sweet-modal>
        <sweet-modal ref="tutorial" v-on:close="closeGuide()" title="ReadO(1) Guide" width="100%">
            <sweet-modal-tab title="Skimming" id="tab1"><skimming></skimming></sweet-modal-tab>
            <sweet-modal-tab title="Scanning" id="tab2"><scanning></scanning></sweet-modal-tab>
            <sweet-modal-tab title="Speed read words per minute" id="tab3"><words-per-minute></words-per-minute></sweet-modal-tab>
            <sweet-modal-tab title="Quiz" id="tab4"><quiz-guide></quiz-guide></sweet-modal-tab>
        </sweet-modal>
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
    import SpeedReader from '~/components/SpeedReader';
    import PDFViewer from '~/components/PDFViewer';
    import ManageQuiz from "../../components/ManageQuiz";
    import Skimming from "../../components/Skimming";
    import Scanning from "../../components/Scanning";
    import { mapGetters } from 'vuex'
    import Swal from 'sweetalert2';
    import WordsPerMinute from "../../components/WordsPerMinute";
    import QuizGuide from "../../components/QuizGuide";
    export default {
        middleware: 'auth',
        components:{
            QuizGuide,
            WordsPerMinute,
            Scanning,
            ManageQuiz,
            SpeedReader,
            PDFViewer,
            Skimming,
        },
        computed: {
            ...mapGetters({
                           role: 'auth/role'
            }),
            formattedElapsedTime() {
                const date = new Date(null);
                date.setSeconds(this.elapsedTime / 1000);
                const utc = date.toUTCString();
                return utc.substr(utc.indexOf(":") - 2, 8);
            }
        },
        data: () => ({
            isLoaded: false,
            title: '',
            description: '',
            fileName: '',
            errorMessage: '',
            text: '',
            file: true,
            textbook: '',
            selection: 'pdf',
            path: '/lib/pdf/web/viewer.html',
            successMessage: '',
            text_id: '',
            elapsedTime: 0,
            timer: undefined,
            attempt_num: 0,
            last_time_taken: '',
        }),
        created() {
            this.fileName =`/api/text/pdf/${this.$route.params.id}`;
            let self = this;
            axios.get(`/api/text/${this.$route.params.id}`)
                .then(response => {
                    if(response.data.Error !== undefined){
                        this.errorMessage = response.data.Error;
                        this.$refs.error.open();
                    }else{
                        this.isLoaded = true;
                        this.title = response.data.title;
                        this.textbook = response.data.textbook;
                        this.description = response.data.description;
                        if(response.data.text !== undefined){
                            this.text = atob(response.data.text);
                        }else{
                            this.file = false;
                        }

                    }
                }).catch(function (response) {
                //handle error
                console.log(response);
            });

            //Gets attempt number of previous attempts for a user for a particular text.
            axios.get(`/api/text/getAttempt/${this.$route.params.id}`)
                .then(response => {
                    if(Object.keys(response.data).length){
                        this.attempt_num = response.data.attempt;
                        this.last_time_taken = response.data.time_taken;
                    }else{
                        this.attempt_num = 0;
                    }

                }).catch(function (response) {
                //handle error
                console.log(response);
            });

        },
        methods:{
            start(){
                this.timer = setInterval(() => {
                    this.elapsedTime += 1000;
                }, 1000);
            },
            openGuide(){
                this.$refs.tutorial.open();
                let self = this;
                clearInterval(self.timer);
            },
            closeGuide(){
                let self = this;
                if(self.timer!=undefined){
                    self.timer = setInterval(() => {
                        self.elapsedTime += 1000;
                    }, 1000);
                }
            },
            manageQuiz(){
                this.text_id = parseInt(this.$route.params.id);
                this.$refs.manageQuiz.open();
            },
            async finishedReading(data) {
                let self = this;
                clearInterval(self.timer);
                Swal.fire({
                    title: 'Are you sure you\'ve finished reading?',
                    html: "Time taken: "+ data + "<br /> Continue to access the quiz or otherwise continue reading",
                    type: 'warning',
                    showDenyButton: true,
                    showCancelButton: true,
                    confirmButtonText: 'Enter Quiz',
                    denyButtonText: `Continue Reading`,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                }).then(function (result) {
                    if (result.value) {
                        self.$router.push({ name: 'quiz.show', params: {id: self.$route.params.id} });
                        axios.post('/api/text/saveAttempt', {
                            time: data,
                            attempt_num: self.attempt_num +1,
                            text_id: self.$route.params.id,
                        }).then(response =>{
                            console.log(response);
                        })
                            .catch(error => {
                                console.log(error.response)
                            });
                    }else{
                        self.timer = setInterval(() => {
                            self.elapsedTime += 1000;
                        }, 1000);
                    }
                });

            },
        }
    }
</script>

<style scoped>
    pre {
        white-space: -moz-pre-wrap; /* Mozilla, supported since 1999 */
        white-space: -pre-wrap; /* Opera */
        white-space: -o-pre-wrap; /* Opera */
        white-space: pre-wrap; /* CSS3 - Text module (Candidate Recommendation) http://www.w3.org/TR/css3-text/#white-space */
        word-wrap: break-word; /* IE 5.5+ */
    }
</style>