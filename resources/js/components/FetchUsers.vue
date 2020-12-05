<template>
    <div>
        <table class="table">

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

            <tr v-for="user in users">
                <th scope="row">{{ user.id}}</th>
                <td>{{ user.name}}</td>
                <td>{{ user.email}}</td>
                <td>{{ user.roles[0].name}}</td>

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

    </div>
</template>

<script>
import axios from 'axios'

    export default {
        data: () => ({
            users: [],

        }),
        name: "FetchUsers",
        created() {
            axios.get('/api/users')
                .then(response => {
                    this.users = response.data;

                }).catch(function (response) {
                //handle error
                console.log(response);
            });

        },
        methods: {
            async del(data) {
                // Submit the form.
                axios.delete(`/api/admin/users/${data}`).then(response => {
                    window.location.reload();
                })

            }
        }

    }
</script>

<style scoped>

</style>