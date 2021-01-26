<template>
    <div v-if="isLoaded" class="text-center">

        <h1 v-if="role==='Student'">My Modules</h1>
        <div class="flexbox">
            <div class="flex-item">
                <router-link v-show="role==='Admin'" :to="{ name: 'module.v.create' }">
                    <button class="btn btn-success" v-if="role==='Admin'">+ Create Module</button>
                </router-link>

            </div>
            <div class="flex-item">
                <h1 style="" v-if="role!=='Student'">All Modules</h1>
            </div>
            <div class="flex-item">
                <router-link v-show="role==='Admin'" :to="{ name: 'yearGroup' }">
                <button class="btn btn-success" v-if="role==='Admin'">+ Add Year Group</button>
                </router-link>
            </div>
        </div>

        <div class="input-group mt-4">
            <input type="search" id="form1" class="form-control" placeholder="Search..."
            aria-label="Search" />
            <span style="padding-left: 15px;">
                <select class="form-control">
                    <option>Filter by year...</option>
                    <option v-for="year in year_groups" :value="year.id" :key="year.id">
                        {{ year.name }}
                    </option>
                </select>
            </span>
        </div>
        <h1 style="text-align: center;" class="mt-4" v-if="!modules || !modules.length">
            You are not assigned to any modules!
        </h1>
        <div class="row justify-content-center">

         <div v-for="module in modules" class=" card col-sm-3 ml-4 mt-4">
           <div class="card-body">
            <router-link :to="{ name: 'module.v.show', params: {id: module.id} }">
            <h5 class="card-title">{{module.name}}</h5>
            <h6 class="card-subtitle mb-2 text-muted">{{module.module_code}} - {{module.year_group.name}}</h6>
            </router-link>
            <router-link :to="{ name: 'module.v.edit', params: {id: module.id} }">
                <a v-show="role==='Admin' || role==='Module Tutor'" href="edit" class="card-link">Edit</a>
            </router-link>
            <router-link :to="{ name: 'module.v.index' }">
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
        </div>
</div>

    <div v-else>
        <clip-loader color="black"/>
    </div>

</template>

<script>
    import axios from 'axios'
    import { mapGetters } from 'vuex'
    import Swal from 'sweetalert2'
    export default {
        name: "module",
        middleware: 'auth',
        computed: mapGetters({
            role: 'auth/role'
        }),
        data: () => ({
            modules: [],
            isLoaded: false,
            loadingColor: "black",
            year_groups: '',

        }),
        created() {
            axios.get('/api/yearGroup/')
                .then(response => {
                    this.year_groups = response.data;
                }).catch(function (response) {
                //handle error
                console.log(response);
            });
            axios.get('/api/module/v')
                .then(response => {
                    this.isLoaded = true;
                    this.modules = response.data;

                }).catch(function (response) {
                //handle error
                console.log(response);
            });

        },
        methods: {
            async del(data) {
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
                        axios.delete(`/api/module/v/${data}`);
                        Swal.fire(
                            'Deleted!',
                            'The module has been deleted.',
                            'success'
                        ).then(function () {
                            window.location.reload();
                        });
                    }
                })

            }
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