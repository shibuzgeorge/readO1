<template>
  <card v-if="isLoaded">
    <h5 class="card-header d-flex justify-content-between align-items-center">
      Dashboard
    </h5>
    <div class="wrapper">
      <div class="container align-items-center">

        <h3>Welcome back - {{user.name}}</h3>
      </div>

    </div>
  </card>
  <div v-else style="text-align: center;">
    <clip-loader color="black"/>
  </div>

</template>

<script>

    import { mapGetters } from 'vuex'
    export default {
    middleware: 'auth',
    computed: mapGetters({
        role: 'auth/role',
        user: 'auth/user'
    }),
    data: () => ({
        isLoaded: true,
    }),
    created() {

    },
    methods: {

    }
}
</script>
