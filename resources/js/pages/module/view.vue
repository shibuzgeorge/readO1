<template>
    <card class="text-center" v-if="isLoaded">
        <div class="card-header d-flex justify-content-between align-items-center">
        <h5 id="title">({{module_code}}) {{name}} - Year: {{module_year}}</h5>
            <button @click="$router.go(-1)" type="button" class="btn btn-sm btn-primary">Back</button>
        </div>
        <div class="wrapper mt-4">
            <div class="d-flex justify-content-between align-items-center">
                <div class="col-xs-3">
                    <input type="search" v-model="searchQuery" class="form-control" placeholder="Search by title or description..."
                           aria-label="Search" />
                </div>
                <router-link v-show="role==='Admin' || role==='Module Tutor'" :to="{ name: 'textbook.create' }">
                    <button class="btn btn-success" v-if="role!=='User'">+ Add textbook</button>
                </router-link>
            </div>
            <h1 style="text-align: center;" class="mt-4" v-if="!textbooks || !textbooks.length">
                This module has no textbooks!
            </h1>
            <div class="row justify-content-center">

                <div v-for="textbook in textbooksPaginated" class="card col-sm-5 ml-4 mt-4">
                    <div class="card-body" :class="{ 'row': textbook.thumbnail!==null }">
                        <div v-if="textbook.thumbnail!==null" class="col-sm-5 d-flex justify-content-center">
                            <img :src="'data:image/png;base64,'+textbook.thumbnail" width="150" height="200"/>
                        </div>
                        <div :class="{ 'col-sm-7': textbook.thumbnail!==null }">
                            <router-link :to="{ name: 'textbook.show', params: {id: textbook.id} }">
                                <h5 class="card-title">{{textbook.title}}</h5>
                                <h6 class="card-subtitle mb-2 text-muted">{{textbook.description }}</h6>
                            </router-link>
                            <router-link :to="{ name: 'textbook.edit', params: {id: textbook.id} }">
                                <a v-show="role==='Admin' || role==='Module Tutor'" href="edit" class="card-link">Edit</a>
                            </router-link>
                            <a @click="del(textbook.id)" v-show="role==='Admin' || role==='Module Tutor'" href="#" class="card-link">Delete</a><br/>
                        </div>
                    </div>
            </div>
            </div><br/>
            <paginate style="display: flex; justify-content: center;" :items="resultQuery" :pageSize="sizePage" @changePage="onChangePage"></paginate>
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
        data: () => ({
            isLoaded: false,
            name: '',
            module_code: '',
            module_year: '',
            textbooks: [],
            textbooksPaginated: [],
            errorMessage: '',
            searchQuery: null,
            sizePage: 9,
        }),
        computed : {
            ...mapGetters({
                role: 'auth/role'
            }),
            resultQuery() {
                if (this.searchQuery) {
                    return this.textbooks.filter((item) => {
                        return this.searchQuery.toLowerCase().split(' ').every(
                            v => item.title.toLowerCase().includes(v) ||
                                item.description.toLowerCase().includes(v))
                    })
                }else {
                    return this.textbooks;
                }
            }
        },
        created() {
            this.refreshData();
        },
        methods: {
            refreshData(){
                this.isLoaded = false;
                axios.get(`/api/module/${this.$route.params.id}`)
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
            async del(data) {
                let self = this;
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
                        axios.delete(`/api/textbook/${data}`).then(response => {
                            self.refreshData();
                        })
                    }
                });

            },
            onChangePage(pageOfItems) {
                this.textbooksPaginated = pageOfItems;
            },
        },
        metaInfo () {
            return { title: this.$t('home') }
        }


    }
</script>

<style scoped>
    .flexbox {
        display: flex;
        flex-direction: row;
        flex-wrap: wrap;
        justify-content: center;
        align-items: center;
    }
    .flex-item{
        margin: auto;
    }
</style>