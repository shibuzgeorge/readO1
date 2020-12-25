<template>
    <card :title="'Edit - '+user.name+'  as '+user_role.name" v-if="isLoaded">
        Choose which role you'd like to change permission<p></p>
        <form @submit.prevent="submit" @keydown="form.onKeydown($event)">
        <div class="form-check" v-for="role in roles">
            <input v-model="form.checked" type="radio" :value="role.id" :checked="user_role.id === role.id"/>
           <label>{{role.name}}</label>

        </div>
            <sweet-modal ref="success" v-on:close="$router.go(-1)" icon="success">
                Successfully Updated
            </sweet-modal>
            <v-button :loading="form.busy" type="success">
                {{ $t('update') }}
            </v-button>
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
            user: [],
            roles: [],
            user_role: [],
            form: new Form({
                checked: '',
            }),
            remember: false,

        }),
        created() {
            let self = this
            axios.get(`/api/admin/users/${this.$route.params.id}/edit`)
                .then(response => {
                    this.isLoaded = true;
                    this.user = response.data.user;
                    this.roles = response.data.roles;
                    this.user_role = response.data.user_role;
                    this.form.checked = response.data.user_role.id;

                }).catch(function (response) {
                //handle error
                self.$router.push({name: 'admin.users'})

                console.log(response);
            });
        },
        methods: {
            async submit() {
                // Submit the form.

                await this.form.patch(`/api/admin/users/${this.$route.params.id}`)
                // Fetch the role.
                await this.$store.dispatch('auth/fetchRole')
                this.$refs.success.open();
            }
        }

    }
</script>

<style scoped>

</style>