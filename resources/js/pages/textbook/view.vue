<template>

    <card :title="title" v-if="isLoaded">
       {{description}}
        <br/>
        <div style="width: 100%; text-align: center;">
        <label style="text-align: center;">View PDF: <input type="radio" v-model="selection" value="pdf"></label>
        <label>Text view: <input type="radio" v-model="selection" value="text"></label>
        <label>Speed reading: <input type="radio" v-model="selection" value="speedreading"></label>
        </div>
        <iframe v-show="selection === 'pdf'" :src="file" height="500px" width="100%"></iframe>
        <div v-show="selection === 'text'">
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


    export default {
        middleware: 'auth',
        components:{
            SpeedReader,
        },
        data: () => ({
            isLoaded: false,
            title: '',
            description: '',
            file: '',
            errorMessage: '',
            text: '',
            selection: 'pdf',
        }),
        created() {

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
                        this.file =  'data:application/pdf;base64,'+response.data.file;
                        this.text =  atob(response.data.text);

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

</style>