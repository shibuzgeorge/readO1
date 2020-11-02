<template>
    <card :title="$t('Admin, User Management')">

        <div class="wrapper">
        <fetch-users/>
        </div>
    </card>
</template>

<script>
    import FetchUsers from "../../components/FetchUsers";
    export default {
        middleware: 'auth',
        name: "users",
        components: {FetchUsers}
    }
</script>

<style scoped>

</style>