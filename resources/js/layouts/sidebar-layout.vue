<template>

    <!-- Sidebar -->
    <div class="bg-light border-right" id="sidebar-wrapper">
        <div class="sidebar-heading">
            ReadO(1) <span style="float: right">
            <a v-on:click="toggleMenu" href="#" id="menu-toggle">
                <span >X</span></a></span>
        </div>

        <div class="list-group list-group-flush">
            <router-link :to="{ name: 'home' }"
                         id="dashboard-menu"
                         class="list-group-item list-group-item-action bg-light"
                         active-class="active-class">Dashboard
            </router-link>
            <router-link :to="{ name: 'library' }"
                         id="library-menu"
                         class="list-group-item list-group-item-action bg-light"
                         active-class="active-class">Library
            </router-link>

            <a id="extensive-reading-menu"
               class="btn collapsed list-group-item list-group-item-action bg-light"
               data-toggle="collapse" href="#extensive-reading-expanded" role="button" aria-expanded="true"
               aria-controls="extensive-reading-expanded">Extensive Reading<span class="mr-3"></span>
            </a>
            <div class="collapse" id="extensive-reading-expanded">
                <router-link :to="{ name: 'extensiveReading.index' }"
                             id="extensive-reading-home-menu"
                             class="list-group-item list-group-item-action text-white bg-dark"
                             active-class="active-class">Home
                </router-link>
                <router-link :to="{ name: 'extensiveReading.categories' }"
                             id="view-all-categories-menu"
                             class="list-group-item list-group-item-action text-white bg-dark"
                             active-class="active-class">View all categories
                </router-link>
                <router-link v-show="role==='Admin' || role==='Module Tutor'" :to="{ name: 'extensiveReading.create' }"
                             id="create-a-category-menu"
                             class="list-group-item list-group-item-action text-white bg-dark"
                             active-class="active-class">Create a category
                </router-link>
            </div>
            <a v-show="role==='Admin' || role==='Module Tutor'"
               id="textbooks-menu"
               class="btn collapsed list-group-item list-group-item-action bg-light"
               data-toggle="collapse" href="#textbooks-expanded" role="button" aria-expanded="true"
               aria-controls="textbooks-expanded">Textbooks<span class="mr-3"></span>
            </a>
            <div class="collapse" id="textbooks-expanded">
                <router-link :to="{ name: 'textbook.create' }"
                             id="view-all-textbooks-menu"
                             class="list-group-item list-group-item-action text-white bg-dark"
                             active-class="active-class">View all textbooks
                </router-link>
                <router-link v-show="role==='Admin' || role==='Module Tutor'" :to="{ name: 'textbook.create' }"
                             id="create-textbook-menu"
                             class="list-group-item list-group-item-action text-white bg-dark"
                             active-class="active-class">Create a textbook
                </router-link>
            </div>

            <a id="texts-menu"
               class="btn collapsed list-group-item list-group-item-action bg-light"
               data-toggle="collapse" href="#texts-expanded" role="button" aria-expanded="true"
               aria-controls="texts-expanded">Texts<span class="mr-3"></span>
            </a>
            <div class="collapse" id="texts-expanded">
                <router-link :to="{ name: 'textbook.create' }"
                             id="view-all-texts-menu"
                             class="list-group-item list-group-item-action text-white bg-dark"
                             active-class="active-class">View all texts
                </router-link>
                <router-link v-show="role==='Admin' || role==='Module Tutor'" :to="{ name: 'text.create' }"
                             id="add-a-text-menu"
                             class="list-group-item list-group-item-action text-white bg-dark"
                             active-class="active-class">Add a text
                </router-link>
            </div>

            <a class="btn collapsed list-group-item list-group-item-action bg-light"
               id="modules-menu"
               data-toggle="collapse" href="#modules-expanded" role="button" aria-expanded="true"
               aria-controls="modules-expanded">Modules<span class="mr-3"></span>
            </a>
            <div class="collapse" id="modules-expanded">
                <router-link v-show="role==='Admin' || role==='Module Tutor'" :to="{ name: 'module.index' }"
                             id="all-modules-menu" class="list-group-item list-group-item-action text-white bg-dark"
                             active-class="active-class">All Modules
                </router-link>

                <router-link v-show="role==='Student'" :to="{ name: 'module.index' }"
                             id="my-modules-menu"
                             class="list-group-item list-group-item-action text-white bg-dark"
                             active-class="active-class">My Modules</router-link>

                <router-link v-show="role==='Admin'" :to="{ name: 'module.create' }"
                             id="create-module-menu" class="list-group-item list-group-item-action text-white bg-dark"
                             active-class="active-class">Create Module
                </router-link>

                <router-link v-show="role==='Admin'" :to="{ name: 'module.assignModuleTutors' }"
                             id="assign-module-tutor-menu"
                             class="list-group-item list-group-item-action text-white bg-dark"
                             active-class="active-class">Assign Module Tutors
                </router-link>

                <router-link v-show="role==='Admin' || role==='Module Tutor'" :to="{ name: 'module.assignStudents' }"
                             id="assign-students-menu"
                             class="list-group-item list-group-item-action text-white bg-dark"
                             active-class="active-class">Assign Students
                </router-link>
            </div>
            <a v-show="role==='Admin'" class="btn collapsed list-group-item list-group-item-action bg-light"
               id="users-menu"
               data-toggle="collapse" href="#users-expanded" role="button" aria-expanded="true"
               aria-controls="users-expanded">Users<span class="mr-3"></span>
            </a>
            <div class="collapse" v-show="role==='Admin'" id="users-expanded">
                <router-link v-show="role==='Admin'" :to="{ name: 'admin.users' }"
                             id="view-all-users-menu"
                             class="list-group-item list-group-item-action text-white bg-dark"
                             active-class="active-class">View all users
                </router-link>
                <router-link v-show="role==='Admin'" :to="{ name: 'home' }"
                             id="create-a-user-menu"
                             class="list-group-item list-group-item-action text-white bg-dark"
                             active-class="active-class">Create a user
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