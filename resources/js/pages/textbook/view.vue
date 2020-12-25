<template>

    <card :title="title" v-if="isLoaded">
       {{description}}
        <br/>

        <iframe :src="file" height="500px" width="100%"></iframe>
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
    export default {
        middleware: 'auth',
        data: () => ({
            isLoaded: false,
            title: '',
            description: '',
            file: '',
            errorMessage: '',


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
                        this.file = 'data:application/pdf;base64,' + response.data.file;
                    }

                }).catch(function (response) {
                //handle error
                self.$router.push({name: 'module.v.index'})

                console.log(response);
            });
        },


    }
</script>

<style scoped>

</style>