<template>
    <div v-if="isLoaded" style="text-align: center;">
        <h1  v-if="role==='User'">My Modules</h1>
        <h1 v-else>All Modules</h1>
        <div class="row">
    <div v-for="module in modules" class="card col-sm-4 ml-4 mt-4">


        <div class="card-body">
            <router-link :to="{ name: 'module.v.show', params: {id: module.id} }">
            <h5 class="card-title">{{module.name}}</h5>
            <h6 class="card-subtitle mb-2 text-muted">{{module.module_code}} - {{module.module_year}}</h6>
            </router-link>
            <router-link :to="{ name: 'module.v.edit', params: {id: module.id} }">
                <a v-show="role==='Admin' || role==='Module Tutor'" href="edit" class="card-link">Edit</a>
            </router-link>
            <router-link :to="{ name: 'module.v.index' }">
            <a @click="del(module.id)" v-show="role==='Admin' || role==='Module Tutor'" href="delete" class="card-link">Delete</a><br/>
            </router-link>
            <router-link :to="{ name: 'module.assignModuleTutors' }">
            <a v-show="role==='Admin'" href="#" class="card-link">Assign Module Tutor</a>
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
    export default {
        name: "module",
        middleware: 'auth',
        computed: mapGetters({
            role: 'auth/role'
        }),
        data: () => ({
            modules: [],
            isLoaded: false,
            loadingColor: "black"

        }),
        created() {
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
                if(confirm("Do you really want to delete the module? If there is textbooks inside, that will get deleted")) {
                    // Submit the form.
                    axios.delete(`/api/module/v/${data}`).then(response => {
                        window.location.reload();
                    })
                }

            }
        },

    }
</script>

<style scoped>

</style>