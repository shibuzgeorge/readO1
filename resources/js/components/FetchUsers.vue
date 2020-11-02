<template>
    <div>
        <table class="table">
            <thead>
            <tr>
                <th scope="col">#</th>
                <th scope="col">Name</th>
                <th scope="col">Email</th>
                <th scope="col">Action</th>
            </tr>
            </thead>
            <tbody>

            <tr v-for="user in users">
                <th scope="row">{{ user.id}}</th>
                <td>{{ user.name}}</td>
                <td>{{ user.email}}</td>

                <td>
                    <router-link :to="{ name: 'admin.users.edit', params: {id: user.id} }">
                      <button type="button" class="btn btn-primary">Edit</button>

                    </router-link>
                </td>
                <td>
                    <router-link :to="{ name: 'admin.users.destroy', params: {id: user.id} }">
                        <button type="button" class="btn btn-warning">Delete</button>

                    </router-link>

                </td>
            </tr>
            </tbody>
        </table>

    </div>
</template>

<script>
import axios from 'axios'

    export default {
        data: function (){
            return {
                users: []
            }
        },
        name: "FetchUsers",
        created() {
            axios.get('/api/users')
                .then(response => {
                    this.users = response.data;

                })

        }
    }
</script>

<style scoped>

</style>