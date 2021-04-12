<template>
    <div v-if="isLoaded" class="text-center">
        <div class="flexbox">
            <div class="flex-item">
                <router-link :to="{ name: 'extensiveReading.index' }">
                    <button class="btn btn-primary">Category Home</button>
                </router-link>
            </div>
            <div class="flex-item">
                <h1 id="title">Extensive Reading Categories</h1>
            </div>
            <div class="flex-item">
                <router-link v-show="role==='Admin' || role==='Module Tutor'" :to="{ name: 'extensiveReading.create' }">
                    <button class="btn btn-success" v-if="role==='Admin' || role==='Module Tutor'">+ Category</button>
                </router-link>
            </div>
        </div>

        <div class="input-group mt-4 d-flex justify-content-center align-items-center">
            <input type="search" v-model="searchQuery" class="form-control col-lg-4" placeholder="Search for a category..."
                   aria-label="Search" />
            <span style="padding-left: 15px;">
            </span>
        </div>
        <h1 style="text-align: center;" class="mt-4" v-if="!categories || !categories.length">
            There are no categories listed
        </h1>
        <div class="row justify-content-center">

            <div v-for="category in categoriesPaginated" class=" card col-sm-3 ml-4 mt-4">
                <div class="card-body">
                    <router-link :to="{ name: 'extensiveReading.show', params: {id: category.id} }">
                        <h5 class="card-title">{{category.name}}</h5>
                        <h6 class="card-subtitle mb-2 text-muted">{{category.description}}</h6>
                    </router-link>
                    <router-link :to="{ name: 'extensiveReading.edit', params: {id: category.id} }">
                        <a v-show="role==='Admin' || role==='Module Tutor'" href="edit" class="card-link">Edit</a>
                    </router-link>
                        <a @click="del(category.id)" v-show="role==='Admin' || role==='Module Tutor'" href="#" class="card-link">Delete</a><br/>
                </div>
            </div>
        </div><br/>
        <paginate style="display: flex; justify-content: center;" :items="resultQuery" :pageSize="sizePage" @changePage="onChangePage"></paginate>
    </div>
    <div v-else>
        <clip-loader color="black"/>
    </div>

</template>

<script>
    import axios from 'axios'
    import { mapGetters } from 'vuex'
    import Swal from 'sweetalert2'
    import $ from 'jquery';
    import 'jquery/dist/jquery.min.js';
    import 'popper.js/dist/umd/popper.min.js'
    export default {
        name: "category",
        middleware: 'auth',
        data: () => ({
            categoriesPaginated: [],
            categories: [],
            isLoaded: false,
            loadingColor: "black",
            year_groups: '',
            searchQuery: null,
            sizePage: 9,

        }),
        computed : {
            ...mapGetters({
                role: 'auth/role'
            }),
            resultQuery() {
                if (this.searchQuery) {
                    return this.categories.filter((item) => {
                        return this.searchQuery.toLowerCase().split(' ').every(
                            v => item.name.toLowerCase().includes(v))
                    })
                } else {
                    return this.categories;
                }
            }
        },
        created() {
            this.refreshData();

        },
        methods: {
            refreshData(){
                this.isLoaded = false;
                axios.get('/api/extensiveReading/categories')
                    .then(response => {
                        this.categories = response.data;
                        this.isLoaded = true;
                    }).catch(function (response) {
                    //handle error
                    console.log(response);
                });
            },
            async del(data) {
                let self = this;
                Swal.fire({
                    title: 'Are you sure?',
                    text: "Do you really want to delete the category?\n" +
                    "      If there is textbooks inside, that will get deleted also",
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!',
                }).then(function (result) {
                    if (result.value) {
                        axios.delete(`/api/extensiveReading/${data}`);
                        Swal.fire(
                            'Deleted!',
                            'The category has been deleted.',
                            'success'
                        ).then(function () {
                            self.refreshData();
                        });
                    }
                })

            },
            onChangePage(pageOfItems) {
                this.categoriesPaginated = pageOfItems;
            },

        },

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