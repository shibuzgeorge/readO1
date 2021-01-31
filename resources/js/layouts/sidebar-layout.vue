<template>

    <!-- Sidebar -->
    <div class="bg-light border-right" id="sidebar-wrapper">
        <div class="sidebar-heading">
            ReadO(1) <span style="float: right">
            <a v-on:click="toggleMenu" href="#" id="menu-toggle">
                <span >X</span></a></span>
        </div>

        <div class="list-group list-group-flush">
            <router-link :to="{ name: 'home' }" class="list-group-item list-group-item-action bg-light" active-class="active-class">
                Dashboard
            </router-link>
            <router-link :to="{ name: 'library' }" class="list-group-item list-group-item-action bg-light" active-class="active-class">
                Library
            </router-link>
            <a v-show="role==='Admin' || role==='Module Tutor'" class="btn collapsed list-group-item list-group-item-action bg-light" data-toggle="collapse" href="#textbooks" role="button" aria-expanded="true" aria-controls="textbooks">
                Textbooks <span class="mr-3"></span>
            </a>
            <div class="collapse" id="textbooks">
                <router-link v-show="role==='Admin' || role==='Module Tutor'" :to="{ name: 'upload' }" class="list-group-item list-group-item-action bg-light" active-class="active-class">
                    Upload textbook
                </router-link>
            </div>

            <a class="btn collapsed list-group-item list-group-item-action bg-light" data-toggle="collapse" href="#modules" role="button" aria-expanded="true" aria-controls="module">
                Modules<span class="mr-3"></span>
            </a>
            <div class="collapse" id="modules">
                <router-link v-show="role==='Admin' || role==='Module Tutor'" :to="{ name: 'module.index' }" class="list-group-item list-group-item-action bg-light" active-class="active-class">
                    All Modules
                </router-link>
                <router-link v-show="role==='Student'" :to="{ name: 'module.index' }" class="list-group-item list-group-item-action bg-light" active-class="active-class">
                    My Modules
                </router-link>
                <router-link v-show="role==='Admin'" :to="{ name: 'module.create' }" class="list-group-item list-group-item-action bg-light" active-class="active-class">
                    Create Module
                </router-link>
                <router-link v-show="role==='Admin'" :to="{ name: 'module.assignModuleTutors' }" class="list-group-item list-group-item-action bg-light" active-class="active-class">
                    Assign Module Tutors
                </router-link>
                <router-link v-show="role==='Admin' || role==='Module Tutor'" :to="{ name: 'module.assignStudents' }" class="list-group-item list-group-item-action bg-light" active-class="active-class">
                    Assign Students
                </router-link>
            </div>
            <a v-show="role==='Admin'" class="btn collapsed list-group-item list-group-item-action bg-light" data-toggle="collapse" href="#management" role="button" aria-expanded="true" aria-controls="management">
                Management<span class="mr-3"></span>
            </a>
            <div class="collapse" v-show="role==='Admin'" id="management">
                <router-link v-show="role==='Admin'" :to="{ name: 'admin.users' }" class="list-group-item list-group-item-action bg-light" active-class="active-class">
                    User Management
                </router-link>
                <router-link v-show="role==='Admin'" :to="{ name: 'home' }" class="list-group-item list-group-item-action bg-light" active-class="active-class">
                    Create a user
                </router-link>
            </div>
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
    .btn {
        box-shadow: none !important;
        outline: 0;
    }

    a:link,
    a:visited {
        color: #222;
    }

    a:hover,
    a:focus {
        color: dodgerblue;
    }

    .list-group-item span {
        border: solid #222;
        border-width: 0 1px 1px 0;
        display: inline;
        cursor: pointer;
        padding: 3px;
        position: absolute;
        right: 0;
        margin-top: 10px;
    }

    .list-group-item a.btn.collapsed span {
        transform: rotate(40deg);
        -webkit-transform: rotate(40deg);
        transition: .3s transform ease-in-out;
    }


    .list-group-item a.btn span {
        transform: rotate(-140deg);
        -webkit-transform: rotate(-140deg);
        transition: .3s transform ease-in-out;
    }
</style>