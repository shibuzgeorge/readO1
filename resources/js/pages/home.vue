<template>
  <card v-if="isLoaded">
    <div class="card-header d-flex justify-content-between align-items-center">
    <h5 id="title">Dashboard</h5>
    </div>
    <div class="wrapper">
      <div class="container align-items-center">

        <h3>Welcome back - {{user.name}}</h3>
        <br>
        <div v-if="list_of_attempts.length !==0">
        <h5>Current Reading Sessions:</h5>
        <div v-for="attempt in list_of_attempts">
          <li>
            Text: {{attempt.text_id}}
            Attempt Number: {{attempt.attempt_number}}
            Time taken: {{attempt.time_taken}}
          </li>
        </div>
        </div>

      </div>

    </div>
  </card>
  <div v-else style="text-align: center;">
    <clip-loader color="black"/>
  </div>

</template>

<script>
    import axios from 'axios'
    import { mapGetters } from 'vuex'
    export default {
    middleware: 'auth',
    computed: mapGetters({
        role: 'auth/role',
        user: 'auth/user'
    }),
    data: () => ({
        isLoaded: true,
        list_of_attempts: [],
    }),
    created() {
        axios.get(`/api/text/getAllAttemptsForCurrentUser/`)
            .then(response => {
                if(Object.keys(response.data).length){
                    this.list_of_attempts = response.data;
                }else{
                    this.list_of_attempts = [];
                }

            }).catch(function (response) {
            //handle error
            console.log(response);
        });
    },
    methods: {

    }
}
</script>
