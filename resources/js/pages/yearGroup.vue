<template>
    <div class="container-fluid" v-if="isLoaded">
        <div class="row">
    <card class="col-6 d-flex flex-column h-100">
        <div class="card-header d-flex justify-content-between align-items-center">
        <h5 id="title">Add Year Group</h5>
            <button @click="$router.go(-1)" type="button" class="btn btn-sm btn-primary">Back</button>
        </div>
        <form @submit.prevent="submit" @keydown="form.onKeydown($event)">
            <div class="form-check mt-4">
                <label>Add Year Group:           </label> <input class="form-control" v-model="form.name" :class="{ 'is-invalid': form.errors.has('name') }" type="text" value=""/>
                <has-error :form="form" field="name" />
                <br/>
            </div>

            <v-button class="form-control" :loading="form.busy" type="success">
                Add
            </v-button>
        </form>
        <sweet-modal ref="success" v-on:close="$router.go($router.currentRoute)" icon="success">
            {{successMessage}}
        </sweet-modal>

        <sweet-modal ref="test" v-on:close="form.errors.clear()">
            <form @submit.prevent="editYearGroup()" @keydown="form.onKeydown($event)">
                <div class="form-check mt-4">
                    <label>Edit year group name:           </label> <input class="form-control" v-model="form.edit_name" type="text" :class="{ 'is-invalid': form.errors.has('edit_name') }" value=""/>
                    <has-error :form="form" field="edit_name" /><br/>
                </div>

                <v-button class="form-control" :loading="form.busy" type="success">
                    Edit
                </v-button>
            </form>
        </sweet-modal>
    </card>
            <card class="col-4 ml-4 d-flex flex-column h-100">
                <h6 class="card-header d-flex justify-content-between align-items-center">
                    Current Year Groups:
                </h6>
                <li class="mt-2" v-for="year in current_year_groups">
                    {{year.name}}
                    <a @click="edit(year.id, year.name)"><button type="button" class="btn btn-primary btn-sm">Edit</button></a>
                    <a @click="del(year.id, year.name)"><button type="button" class="btn btn-warning btn-sm">Delete</button></a>

                </li>
            </card>
        </div>
    </div>
    <div v-else>
        <clip-loader color="black"/>
    </div>
</template>

<script>
    import axios from 'axios'
    import Form from 'vform'
    import Swal from 'sweetalert2'
    export default {
        name: "addYearGroup",
        middleware: 'admin',
        data: () => ({
            isLoaded: false,
            form: new Form({
                name: '',
                edit_name: '',

            }),
            successMessage: '',
            current_year_groups: '',
            edit_id: '',

        }),
        created(){
            axios.get('/api/yearGroup')
                .then(response => {
                    this.current_year_groups = response.data;
                    this.isLoaded = true;
                }).catch(function (response) {
                //handle error
                console.log(response);
            });
        },
        methods: {
            async submit() {
                await this.form.post('/api/yearGroup')
                    .then(response => {
                        this.successMessage = response.data.Success;
                        this.$refs.success.open();
                    })
                    .catch(error => {
                        console.log(error.response)
                    });
            },
            async edit(id, name) {
                this.edit_id = id;
                this.form.edit_name = name;
                this.$refs.test.open();
            },
            async editYearGroup() {
                await this.form.patch(`/api/yearGroup/${this.edit_id}`)
                    .then(response => {
                        this.successMessage = response.data.Success;
                        this.$refs.test.close();
                        this.$refs.success.open();
                    })
                    .catch(error => {
                        console.log(error.response)
                    });
            },
            async del(id, name) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: "Do you really want to delete the year group - ("+name+") ?\n" +
                    "      If there is modules inside, that will get deleted also",
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!',
                }).then(function (result) {
                    if (result.value) {
                        axios.delete(`/api/yearGroup/${id}`);
                        Swal.fire(
                            'Deleted!',
                            'The year group has been deleted.',
                            'success'
                        ).then(function () {
                            window.location.reload();
                        });
                    }
                });
            },
        }
    }
</script>

<style scoped>

</style>