<template>
    <card :title="title" v-if="isLoaded">
       {{description}}
        <br/>

        <iframe :src="file" height="500px" width="100%"></iframe>

    </card>
    <div v-else>
        <clip-loader :color="loadingColor"/>
    </div>
</template>

<script>
    import axios from 'axios'
    import ClipLoader from "vue-spinner/src/ClipLoader";
    export default {
        components: {ClipLoader},
        middleware: 'auth',
        data: () => ({
            isLoaded: false,
            loadingColor: "black",
            title: '',
            description: '',
            file: '',


        }),
        created() {
            let self = this
            axios.get(`/api/textbook/view/${this.$route.params.id}`)
                .then(response => {
                    this.isLoaded = true;
                    this.title = response.data.title;
                    this.description = response.data.description;
                    this.file = 'data:application/pdf;base64,' + response.data.file;


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