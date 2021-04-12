<template>
    <div v-if="isLoaded" class="text-center">

        <h1 id="title-my-modules" v-if="role==='Student'">My Modules</h1>
        <div class="flexbox">
            <div class="flex-item">
                <router-link v-show="role==='Admin'" :to="{ name: 'module.create' }">
                    <button id="create" class="btn btn-success" v-if="role==='Admin'">+ Create Module</button>
                </router-link>

            </div>
            <div class="flex-item">
                <h1 id="title-all-modules" v-if="role!=='Student'">All Modules</h1>
            </div>
            <div class="flex-item">
                <router-link v-show="role==='Admin'" :to="{ name: 'yearGroup' }">
                <button class="btn btn-success" v-if="role==='Admin'">+ Add Year Group</button>
                </router-link>
            </div>
        </div>

        <div class="input-group mt-4 d-flex justify-content-center align-items-center">
            <input type="search" name="search" v-model="searchQuery" class="form-control col-lg-4" placeholder="Search by name or module code..."
            aria-label="Search" />
            <span style="padding-left: 15px;">
                <select class="form-control" v-model="filterYear">
                    <option>Filter by year...</option>
                    <option v-for="year in year_groups" :value="year.name" :key="year.name">
                        {{ year.name }}
                    </option>
                </select>
            </span>
        </div>
        <h1 style="text-align: center;" class="mt-4" v-if="!modules || !modules.length">
            You are not assigned to any modules!
        </h1>
        <div class="row justify-content-center">

         <div v-for="module in modulesPaginated" class=" card col-sm-3 ml-4 mt-4">
           <div class="card-body">
            <router-link :to="{ name: 'module.show', params: {id: module.id} }">
            <h5 class="card-title">{{module.name}}</h5>
            <h6 class="card-subtitle mb-2 text-muted">{{module.module_code}} - {{module.year_group.name}}</h6>
            </router-link>
            <router-link :to="{ name: 'module.edit', params: {id: module.id} }">
                <a v-show="role==='Admin' || role==='Module Tutor'" href="edit" class="card-link">Edit</a>
            </router-link>
            <router-link :to="{ name: 'module.index' }">
            <a @click="del(module.id)" v-show="role==='Admin'" href="delete" class="card-link">Delete</a><br/>
            </router-link>
            <router-link :to="{ name: 'module.assignModuleTutors' }">
            <a v-show="role==='Admin'" href="edit" class="card-link">Assign Module Tutor</a>
            </router-link>
            <router-link :to="{ name: 'module.assignStudents'}">
            <a v-show="role==='Admin' || role==='Module Tutor'" href="edit" class="card-link">Assign Students</a>
            </router-link>
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
        name: "module",
        middleware: 'auth',
        data: () => ({
            modulesPaginated: [],
            modules: [],
            isLoaded: false,
            loadingColor: "black",
            year_groups: '',
            filterYear: 'Filter by year...',
            searchQuery: null,
            sizePage: 6,

        }),
        computed : {
            ...mapGetters({
                role: 'auth/role'
            }),
            resultQuery() {
                if (this.searchQuery && this.filterYear !== 'Filter by year...') {
                    return this.modules.filter(item => item.year_group.name === this.filterYear &&
                        this.searchQuery.toLowerCase().split(' ').every(
                            v => item.module_code.toLowerCase().includes(v) ||
                                item.name.toLowerCase().includes(v))
                    )
                }
                else if (this.searchQuery) {
                    return this.modules.filter((item) => {
                        return this.searchQuery.toLowerCase().split(' ').every(
                            v => item.module_code.toLowerCase().includes(v) ||
                                item.name.toLowerCase().includes(v))
                    })
                }
                else if (this.filterYear !== 'Filter by year...') {
                    return this.modules.filter(item => item.year_group.name === this.filterYear)
                } else {
                    return this.modules;
                }
            }
        },
        created() {
            this.refreshData()
        },
        mounted: function () {
            this.$nextTick(function () {
                //Change to page to last page if a new module is created.
                if (this.$route.query.page === 'last') {
                        setTimeout(function(){
                            let last_page = document.getElementsByClassName('page-link').length-1;
                            document.getElementsByClassName('page-link')[last_page].click();
                        }, 1000);
                }
            })
        },
        methods: {
            refreshData(){
                this.isLoaded = false;
                axios.get('/api/yearGroup/')
                    .then(response => {
                        this.year_groups = response.data;
                    }).catch(function (response) {
                    //handle error
                    console.log(response);
                });
                axios.get('/api/module/')
                    .then(response => {
                        this.isLoaded = true;
                        this.modules = response.data;
                    }).catch(function (response) {
                    //handle error
                    console.log(response);
                });
            },
            async del(data) {
                let self = this;
                Swal.fire({
                    title: 'Are you sure?',
                    text: "Do you really want to delete the module?\n" +
                    "      If there is textbooks inside, that will get deleted also",
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!',
                }).then(function (result) {
                    if (result.value) {
                        axios.delete(`/api/module/${data}`);
                        Swal.fire(
                            'Deleted!',
                            'The module has been deleted.',
                            'success'
                        ).then(function () {
                            self.filterYear = 'Filter by year...';
                            self.refreshData();
                        });
                    }
                })

            },
            onChangePage(pageOfItems) {
                this.modulesPaginated = pageOfItems;
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