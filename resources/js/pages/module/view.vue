<template>
    <card :title="name" v-if="isLoaded">

        <div class="wrapper">
            <div v-for="textbook in textbooks" class="card col-sm-4 ml-4 mt-4">
                <div class="card-body">
                    <h5 class="card-title">{{textbook.title}}</h5>
                    <h6 class="card-subtitle mb-2 text-muted">{{textbook.description }}</h6>
                    <a v-show="role==='Admin' || role==='Module Tutor'" href="edit" class="card-link">Edit</a>
                    <a @click="del(textbook.id)" v-show="role==='Admin' || role==='Module Tutor'" href="delete" class="card-link">Delete</a><br/>
                    <router-link :to="{ name: 'textbook.view.show', params: {id: textbook.id} }">
                        <a href="#" class="card-link">View</a>
                    </router-link>
                </div>
            </div>
        </div>
    </card>
    <div v-else style="text-align: center;">
        <clip-loader :color="loadingColor"/>
    </div>
</template>

<script>
    import axios from 'axios'
    import { mapGetters } from 'vuex'
    import ClipLoader from "vue-spinner/src/ClipLoader";
    export default {
        components: {ClipLoader},
        middleware: 'auth',
        computed: mapGetters({
            role: 'auth/role'
        }),
        data: () => ({
            isLoaded: false,
            name: '',
            module_code: '',
            module_year: '',
            textbooks: [],
            loadingColor: "black"
        }),
        created() {

            axios.get(`/api/module/v/${this.$route.params.id}`)
                .then(response => {
                    let self = this;
                    if(response.data === 'Error'){
                        self.$router.push({name: 'module.v.index'})
                    }else {
                        this.isLoaded = true;
                        this.name = response.data.name;
                        this.module_code = response.data.code;
                        this.module_year = response.data.year;
                        this.textbooks = response.data.textbooks;
                    }

                }).catch(error => {
                console.log(error.response)
            });
        },
        metaInfo () {
            return { title: this.$t('home') }
        }


    }
</script>

<style scoped>

</style>