<template>
    <card v-if="isLoaded">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 id="title">Create a new user</h5>
            <button @click="$router.go(-1)" type="button" class="btn btn-sm btn-primary">Back</button>
        </div>

        <form @submit.prevent="submit" @keydown="form.onKeydown($event)">
            <div class="form-check mt-4">
                <label>Name:</label>
                <input class="form-control" v-model="form.name" :class="{ 'is-invalid': form.errors.has('name') }" type="text" value="" required/>
                <has-error :form="form" field="name" /><br/>
                <label>Email:</label>
                <input class="form-control" v-model="form.email" :class="{ 'is-invalid': form.errors.has('email') }" type="text" value="" required/>
                <has-error :form="form" field="email" /><br/>
                Choose which role you'd like to assign<p></p>
                <div class="form-check" v-for="role in roles">
                    <input v-model="form.role" type="radio" :value="role.id"/>
                    <label>{{role.name}}</label>
                </div>
                <v-button :loading="form.busy" type="success">Create</v-button>
            </div>

            <sweet-modal ref="success" v-on:close="$router.go(-1)" icon="success">
                Successfully created a new user!<br/>
                The new user will receive an email to verify their account and receive
                a reset password link in which they can reset their password to access their account.
            </sweet-modal>
        </form>
    </card>
    <div v-else>
        <clip-loader color="black"/>
    </div>

</template>

<script>
    import axios from 'axios'
    import Form from 'vform'

    export default {
        middleware: 'admin',
        name: "edit",
        data: () => ({
            isLoaded: false,
            roles: [],
            form: new Form({
                role: '',
                name: '',
                email: ''
            }),
            remember: false,

        }),
        created(){
            let self = this;
            axios.get(`/api/admin/users/create`)
                .then(response => {
                    this.isLoaded = true;
                    this.roles = response.data

                }).catch(function (response) {
                //handle error
                self.$router.push({name: 'admin.users'})

                console.log(response);
            });
        },
        methods: {
            async submit() {
                // Submit the form.
                await this.form.post(`/api/admin/users`)
                .then(response => {
                    console.log(response);
                    this.$refs.success.open();
                }).catch(error => {
                    console.log(error.response)
                });

            }
        }

    }
</script>

<style scoped>

</style>