<template>

  <card :title="$t('home')" v-if="isLoaded">


    <div class="search-wrapper"><br/>

      <label>Search textbook:</label>  <input type="text" class="" placeholder="Search title.."/><p></p>
    </div>
    <div class="wrapper">
      <div class="row">
      <div v-for="textbook in textbooks" class="card col-sm-3 ml-4 mt-4">
        <div class="card-body">
          <router-link :to="{ name: 'textbook.view.show', params: {id: textbook.id} }">
          <h5 class="card-title">{{textbook.title}}</h5>
          <h6 class="card-subtitle mb-2 text-muted">{{textbook.description }}</h6>
          </router-link>
          <router-link :to="{ name: 'textbook.view.edit', params: {id: textbook.id} }">
            <a v-show="role==='Admin' || role==='Module Tutor'" href="edit" class="card-link">Edit</a>
          </router-link>
          <a @click="del(textbook.id)" v-show="role==='Admin' || role==='Module Tutor'" href="#" class="card-link">Delete</a><br/>

        </div>
      </div>
    </div>
    </div>
  </card>
  <div v-else style="text-align: center;">
    <clip-loader :color="loadingColor"/>
  </div>

</template>

<script>
    import axios from 'axios'
    import { mapGetters } from 'vuex'
    import ClipLoader from "vue-spinner/src/ClipLoader";
    import Swal from 'sweetalert2';
    export default {
    components: {ClipLoader},
    middleware: 'auth',
    computed: mapGetters({
        role: 'auth/role'
    }),
    data: () => ({
        isLoaded: false,
        textbooks: [],
        loadingColor: "black"
    }),
    created() {

        axios.get('api/textbook/view')
            .then(response => {
                this.isLoaded = true;
                this.textbooks = response.data

            }).catch(error => {
            console.log(error.response)
        });
    },
    methods: {
        async del(data) {
            Swal.fire({
                title: 'Are you sure?',
                text: "Do you really want to delete the textbook?",
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!',
            }).then(function (result) {
                if (result.value) {
                    Swal.fire(
                        'Deleted!',
                        'The textbook has been deleted.',
                        'success'
                    );
                    axios.delete(`/api/textbook/view/${data}`).then(response => {
                        window.location.reload();
                    })
                }
            });

        }
    },
  metaInfo () {
    return { title: this.$t('home') }
  }


}
</script>
