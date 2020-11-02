<template>
    <card :title="$t('Edit - '+user.name+'  as admin')">
        Choose which user you'd like to give admin access<p></p>

        <div class="form-check" v-for="role in roles">
            <input type="checkbox" name="roles[]" :value="role.id"/>
           <label>{{role.name}}</label>
        </div>
    <button type="submit" class="btn btn-primary">
        Submit
    </button>
    </card>

</template>

<script>
import axios from 'axios'
    export default {

        name: "edit",
        data: function (){
            return {
                user: [],
                roles: []
            }
        },
        created() {
            axios.get(`/api/admin/users/${this.$route.params.id}/edit`)
                .then(response => {
                    this.user = response.data.user;
                        this.roles = response.data.roles;

                })
        },

    }
</script>

<style scoped>

</style>