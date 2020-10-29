<template>
    <div>
    <li class="list-group" v-for="user in users">
        {{ user.name}}
    </li>
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