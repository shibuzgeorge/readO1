<template>

    <!-- Sidebar -->
    <div class="bg-light border-right" id="sidebar-wrapper">
        <div class="sidebar-heading">Read(0)1 </div>
        <div class="list-group list-group-flush">
            <router-link v-if="role!=='User'" :to="{ name: 'admin.upload' }" class="list-group-item list-group-item-action bg-light" active-class="active">
                 Upload textbook
            </router-link>
            <router-link v-if="role==='Admin'" :to="{ name: 'admin.users' }" class="list-group-item list-group-item-action bg-light" active-class="active">
                Admin User Management
            </router-link>
            <router-link v-if="role==='User'" :to="{ name: 'home' }" class="list-group-item list-group-item-action bg-light" active-class="active">
                Dashboard
            </router-link>
            <router-link v-if="role==='User'" :to="{ name: 'home' }" class="list-group-item list-group-item-action bg-light" active-class="active">
                Library
            </router-link>
            <router-link v-if="role==='User'" :to="{ name: 'home' }" class="list-group-item list-group-item-action bg-light" active-class="active">
                My Modules
            </router-link>

        </div>
    </div>
    <!-- /#sidebar-wrapper -->
</template>

<script>
import { mapGetters } from 'vuex'
export default {
        name: "Sidebar",
        middleware: 'auth',
    computed: mapGetters({
        user: 'auth/user',
        role: 'auth/role'
    }),
}
</script>

<style scoped>

</style>