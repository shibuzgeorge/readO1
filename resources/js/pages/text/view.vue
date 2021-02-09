<template>
    <card v-if="isLoaded">
        <h5 class="card-header">
            <div id="title" class="d-flex justify-content-between align-items-center">Title: {{title}}
                <button @click="$router.go(-1)" type="button" class="btn btn-sm btn-primary">Back</button>
            </div>
            <br/>
            <div class="d-flex justify-content-between align-items-center">
                Description: {{description}}
                <router-link :to="{ name: 'text.edit', params: {id: $route.params.id} }">
                    <button class="btn btn-success" v-if="role==='Admin'|| role==='Module Tutor'">Edit</button>
                </router-link>
            </div>
        </h5>

        <br/>
        <div v-if="file">
            <div style="width: 100%; text-align: center;" >
                <label style="text-align: center;">View PDF: <input type="radio" v-model="selection" value="pdf"></label>
                <label>Text view: <input type="radio" v-model="selection" value="text"></label>
                <label>Speed reading: <input type="radio" v-model="selection" value="speedreading"></label>
            </div>

            <PDFViewer :fileName="fileName" :path="path" v-show="selection === 'pdf'"/>
            <div v-show="selection === 'text'" style="font-family: sans-serif; font-size: 35px;">
            <pre>
                {{text}}
            </pre>

            </div>
            <div v-show="selection === 'speedreading'">
                <SpeedReader :text=text />

            </div>
        </div>
        <div v-else>
            No file has been uploaded for this text
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
    import SpeedReader from '~/components/SpeedReader';
    import PDFViewer from '~/components/PDFViewer';
    import { mapGetters } from 'vuex'
    export default {
        middleware: 'auth',
        components:{
            SpeedReader,
            PDFViewer,
        },
        computed: mapGetters({
            role: 'auth/role'
        }),
        data: () => ({
            isLoaded: false,
            title: '',
            description: '',
            fileName: '',
            errorMessage: '',
            text: '',
            file: true,
            selection: 'pdf',
            path: '/lib/pdf/web/viewer.html',

        }),
        created() {
            this.fileName =`/api/text/pdf/${this.$route.params.id}`
            let self = this
            axios.get(`/api/text/${this.$route.params.id}`)
                .then(response => {
                    if(response.data.Error !== undefined){
                        this.errorMessage = response.data.Error;
                        this.$refs.error.open();
                    }else{
                        this.isLoaded = true;
                        this.title = response.data.title;
                        this.description = response.data.description;
                        if(response.data.text !== undefined){
                            this.text = atob(response.data.text);
                        }else{
                            this.file = false;
                        }

                    }
                }).catch(function (response) {
                //handle error
                self.$router.push({name: 'module.v.index'})
                console.log(response);
            });
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