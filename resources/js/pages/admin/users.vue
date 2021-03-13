<template>
    <card v-if="isLoaded">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 id="title">All Users</h5>
            <button @click="$router.go(-1)" type="button" class="btn btn-sm btn-primary">Back</button>
        </div>
        <div class="wrapper">
            <div>
                <div class="form-inline d-flex justify-content-between align-items-center">
                    <input class="form-control col-lg-4" type="search" v-model="searchQuery" placeholder="Search by Name or Email...."/>
                    <select class="form-control col-lg-3 ml-2" v-model="filterRoles">
                        <option>Filter by role...</option>
                        <option v-for="role in roles" :value="role.name" :key="role.name">
                            {{ role.name }}
                        </option>
                    </select>
                </div>
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

                    <tr v-for="user in usersPaginated">
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
                                    Delete
                                </v-button>
                            </form>

                        </td>
                    </tr>
                    </tbody>
                </table>
                <paginate style="display: flex; justify-content: center;" :items="resultQuery" :pageSize="sizePage" @changePage="onChangePage"></paginate>
            </div>
        </div>
    </card>
    <div v-else style="text-align: center;">
        <clip-loader color="black"/>
    </div>
</template>

<script>
    import axios from 'axios'
    import Swal from 'sweetalert2'
    export default {
        data: () => ({
            usersPaginated: [],
            users: [],
            roles: [],
            isLoaded: false,
            searchQuery: null,
            sizePage: 5,
            filterRoles: 'Filter by role...'
        }),
        computed: {
            resultQuery() {
                if (this.searchQuery && this.filterRoles !== 'Filter by role...') {
                    return  this.users.filter(obj => obj.role.name === this.filterRoles &&
                        this.searchQuery.toLowerCase().split(' ').every(
                        v => obj.email.toLowerCase().includes(v) ||
                            obj.name.toLowerCase().includes(v))
                    )
                }
                else if (this.searchQuery) {
                    return this.users.filter((item) => {
                        return this.searchQuery.toLowerCase().split(' ').every(
                            v => item.email.toLowerCase().includes(v) ||
                                item.name.toLowerCase().includes(v))
                    })
                }
                else if (this.filterRoles !== 'Filter by role...') {
                    return this.users.filter(obj => obj.role.name === this.filterRoles)
                } else {
                    return this.users;
                }
            }

        },
        created() {
            this.refreshData();
        },
        methods: {
            async del(data) {
                let self = this;
                Swal.fire({
                    title: 'Are you sure?',
                    html: "Do you really want to delete the user?<br />" +
                    "All information and data related to the user will be deleted also",
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!',
                }).then(function (result) {
                    if (result.value) {
                        // Submit the form to delete a user.
                        axios.delete(`/api/admin/users/${data}`).then(response => {
                            Swal.fire(
                                'Deleted!',
                                'The user has been deleted.',
                                'success'
                            ).then(function () {
                                self.refreshData();
                            });
                        })
                    }
                })
            },
            refreshData(){
                this.isLoaded = false;
                axios.get('/api/users')
                    .then(response => {
                        this.users = response.data.users;
                        this.roles = response.data.roles;
                        this.isLoaded = true;

                    }).catch(error => {
                    console.log(error.response)
                });
            },
            onChangePage(pageOfItems) {
                this.usersPaginated = pageOfItems;
            },
        },
        middleware: 'admin',
        name: "users",
    }
</script>

<style scoped>

</style>