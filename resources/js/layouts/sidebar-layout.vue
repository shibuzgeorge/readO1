<template>

    <!-- Sidebar -->
    <div class="bg-light border-right" id="sidebar-wrapper">
        <div class="sidebar-heading">
            ReadO(1) <span style="float: right">
            <a v-on:click="toggleMenu" href="#" id="menu-toggle">
                <span >X</span></a></span>
        </div>

        <div class="list-group list-group-flush">

            <router-link v-show="role==='Admin' || role==='Module Tutor'" :to="{ name: 'upload' }" class="list-group-item list-group-item-action bg-light" active-class="active-class">
                Upload textbook
            </router-link>
            <router-link v-show="role==='Admin'" :to="{ name: 'admin.users' }" class="list-group-item list-group-item-action bg-light" active-class="active-class">
                User Management
            </router-link>
            <router-link v-show="role==='Admin'" :to="{ name: 'admin.users' }" class="list-group-item list-group-item-action bg-light" active-class="active-class">
                Add modules
            </router-link>
            <router-link v-show="role==='Admin'" :to="{ name: 'admin.users' }" class="list-group-item list-group-item-action bg-light" active-class="active-class">
               Assign modules to module tutors
            </router-link>
            <router-link v-show="role==='User'" :to="{ name: 'home' }" class="list-group-item list-group-item-action bg-light" active-class="active-class">
                Dashboard
            </router-link>
            <router-link v-show="role==='User'" :to="{ name: 'home' }" class="list-group-item list-group-item-action bg-light" active-class="active-class">
                Library
            </router-link>
            <router-link v-show="role==='User'" :to="{ name: 'home' }" class="list-group-item list-group-item-action bg-light" active-class="active-class">
                My Modules
            </router-link>
        </div>
    </div>
</template>

<script>
    import MainContainer from "./container-layout";
    import { mapGetters } from 'vuex'
    import 'jquery/dist/jquery.min.js';
    import 'popper.js/dist/umd/popper.min.js'
    import $ from 'jquery';
    export default {

        name: "sidebar-layout",
        components: {MainContainer},
        computed: mapGetters({
            user: 'auth/user',
            role: 'auth/role'
        }),
        methods: {

            toggleMenu : function(event){
                event.preventDefault();
                $("#wrapper").toggleClass("toggled");
                $("#menu-toggle").toggle();
                $("#menu-expand").toggle();

            },


    }}
</script>

<style scoped>

</style>