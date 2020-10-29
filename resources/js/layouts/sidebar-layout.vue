<template>

    <!-- Sidebar -->
    <div class="bg-light border-right" id="sidebar-wrapper">
        <div class="sidebar-heading">
            Read(0)1 <span style="float: right">
            <a v-on:click="toggleMenu" href="#" id="menu-toggle">
                <span >X</span></a></span>
        </div>

        <div class="list-group list-group-flush">
            <a href="#" class="list-group-item list-group-item-action bg-light">Dashboard</a>
            <a href="#" class="list-group-item list-group-item-action bg-light">Shortcuts</a>
            <a href="#" class="list-group-item list-group-item-action bg-light">Overview</a>
            <a href="#" class="list-group-item list-group-item-action bg-light">Events</a>
            <a href="#" class="list-group-item list-group-item-action bg-light">Profile</a>
            <a href="#" class="list-group-item list-group-item-action bg-light">Status</a>
        </div>
    </div>
</template>

<script>
    import MainContainer from "./container-layout";
    import 'jquery/dist/jquery.min.js';
    import 'popper.js/dist/umd/popper.min.js'
    import $ from 'jquery';
    export default {

        name: "sidebar-layout",
        components: {MainContainer},
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