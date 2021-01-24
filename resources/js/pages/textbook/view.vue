<template>

    <card :title="title" v-if="isLoaded">
       {{description}}
        <br/>
        <div style="width: 100%; text-align: center;">
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

    export default {
        middleware: 'auth',
        components:{
            SpeedReader,
            PDFViewer,
        },
        data: () => ({
            isLoaded: false,
            title: '',
            description: '',
            fileName: '',
            errorMessage: '',
            text: '',
            selection: 'pdf',
            path: '/lib/pdf/web/viewer.html',

        }),
        created() {
            this.fileName =`/api/textbook/view/${this.$route.params.id}/pdf`
            let self = this
            axios.get(`/api/textbook/view/${this.$route.params.id}`)
                .then(response => {
                    if(response.data.Error !== undefined){
                        this.errorMessage = response.data.Error;
                        this.$refs.error.open();
                    }else{
                        this.isLoaded = true;
                        this.title = response.data.title;
                        this.description = response.data.description;
                        this.text = atob(response.data.text);
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