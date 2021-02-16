<template >
    <div v-if="user && role" class="d-flex" id="wrapper">
      <!-- Sidebar -->
        <sidebar-layout />
            <div id="page-content-wrapper">
                <container-layout/>
            </div>
        </div>
    <div v-else>
        <div v-if="user && !role">
            <clip-loader color="black"></clip-loader>
        </div>
        <div v-else>
            <container-layout/>
        </div>
    </div>
</template>

<script>
//jquery, popper.js libraries for bootstrap
import { mapGetters } from 'vuex'
import ContainerLayout from "./container-layout";
import SidebarLayout from "./sidebar-layout";

export default {
  name: 'MainLayout',
    components: {
        SidebarLayout,
        ContainerLayout
    },
    computed: mapGetters({
        user: 'auth/user',
        role: 'auth/role',
    }),


    }

</script>

<style>

    #sidebar-wrapper {
        min-height: 100vh;
        margin-left: -15rem;
        -webkit-transition: margin .25s ease-out;
        -moz-transition: margin .25s ease-out;
        -o-transition: margin .25s ease-out;
        transition: margin .25s ease-out;
    }
    #sidebar-wrapper .sidebar-heading {
        padding: 0.875rem 1.25rem;
        font-size: 1.2rem;
    }
    #sidebar-wrapper .list-group {
        width: 15rem;
    }
    #page-content-wrapper {
        min-width: 100vw;
    }

    #wrapper.toggled #sidebar-wrapper {
        margin-left: 0;
    }
    #sidebar-wrapper {
        margin-left: 0;
    }
    #wrapper.toggled #sidebar-wrapper {
        margin-left: -15rem;
    }
    #page-content-wrapper {
        min-width: 0;
        width: 100%;
    }
    #wrapper{background-color: lightblue;}

</style>
