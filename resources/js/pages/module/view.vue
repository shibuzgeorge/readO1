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

                    <a @click="del(textbook.id)" v-show="role==='Admin' || role==='Module Tutor'" class="card-link">Delete</a><br/>

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
    import Swal from 'sweetalert2'
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
                this.$router.go(-1)
            });
        },
        methods: {
            async del(data) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: "Do you really want to delete the textbook?",
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!',
                }).then(function (result) {
                    if (result.value) {
                        Swal.fire(
                            'Deleted!',
                            'The textbook has been deleted.',
                            'success'
                        );
                        axios.delete(`/api/textbook/view/${data}`).then(response => {
                            window.location.reload();
                        })
                    }
                });

            }
        },
        metaInfo () {
            return { title: this.$t('home') }
        }


    }
</script>

<style scoped>

</style>