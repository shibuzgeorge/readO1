<template>
    <card :title="name" v-if="isLoaded">

        <div class="wrapper">
            <div class="row">
                <div v-for="textbook in textbooks" class="card col-sm-3 ml-4 mt-4">
                <div class="card-body">
                    <router-link :to="{ name: 'textbook.view.show', params: {id: textbook.id} }">
                    <h5 class="card-title">{{textbook.title}}</h5>
                    <h6 class="card-subtitle mb-2 text-muted">{{textbook.description }}</h6>
                    </router-link>
                    <router-link :to="{ name: 'textbook.view.edit', params: {id: textbook.id} }">
                        <a v-show="role==='Admin' || role==='Module Tutor'" href="edit" class="card-link">Edit</a>
                    </router-link>

                    <a @click="del(textbook.id)" v-show="role==='Admin' || role==='Module Tutor'" href="delete" class="card-link">Delete</a><br/>
                </div>
            </div>
            </div>
        </div>
    </card>
    <div v-else style="text-align: center;">
        <sweet-modal ref="error" v-on:close="$router.go(-1)" icon="error">
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
            name: '',
            module_code: '',
            module_year: '',
            textbooks: [],
            errorMessage: '',
        }),
        created() {

            axios.get(`/api/module/v/${this.$route.params.id}`)
                .then(response => {
                    if(response.data.Error !== undefined){
                        this.errorMessage = response.data.Error;
                        this.$refs.error.open();
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