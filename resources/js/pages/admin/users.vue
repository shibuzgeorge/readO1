<template>
    <card v-if="isLoaded">
        <h5 class="card-header d-flex justify-content-between align-items-center">
            Admin User Management
            <button @click="$router.go(-1)" type="button" class="btn btn-sm btn-primary">Back</button>
        </h5>
        <div class="wrapper">
            <div>
                <table class="table" >

                    <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Name</th>
                        <th scope="col">Email</th>
                        <th scope="col">Role</th>
                        <th scope="col">Action</th>
                    </tr>
                    </thead>
                    <tbody>

                    <tr v-for="user in users.data">
                        <th scope="row">{{ user.id}}</th>
                        <td>{{ user.name}}</td>
                        <td>{{ user.email}}</td>
                        <td>{{ user.role.name}}</td>

                        <td>
                            <router-link :to="{ name: 'admin.users.edit', params: {id: user.id} }">
                                <button type="button" class="btn btn-primary">Edit</button>

                            </router-link>
                        </td>
                        <td>
                            <form @submit.prevent="del(user.id)">
                                <v-button class="btn btn-warning" type="success">
                                    {{ $t('Delete') }}
                                </v-button>
                            </form>

                        </td>
                    </tr>
                    </tbody>
                </table>
                <paginate :data="users" align="center" @pagination-change-page="getResults"></paginate>
            </div>
        </div>
    </card>
    <div v-else style="text-align: center;">
        <clip-loader color="black"/>
    </div>
</template>

<script>
    import axios from 'axios'
    export default {
        data: () => ({
            users: [],
            isLoaded: false,

        }),
        created() {
            // Fetch initial results
            this.getResults();

        },
        methods: {
            async del(data) {
                // Submit the form.
                axios.delete(`/api/admin/users/${data}`).then(response => {
                    window.location.reload();
                })

            },
            getResults(page = 1){
                axios.get('/api/users?page=' + page)
                    .then(response => {
                        this.isLoaded = true;
                        this.users = response.data;

                    }).catch(error => {
                    console.log(error.response)
                });
            },
        },

        middleware: 'admin',
        name: "users",
    }
</script>

<style scoped>

</style>