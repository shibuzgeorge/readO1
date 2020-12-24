<template>

  <card :title="$t('home')" v-if="isLoaded">


    <div class="search-wrapper"><br/>

      <label>Search textbook:</label>  <input type="text" class="" placeholder="Search title.."/><p></p>
    </div>
    <div class="wrapper">
      <div v-for="textbook in textbooks" class="card col-sm-4 ml-4 mt-4">
        <div class="card-body">
          <h5 class="card-title">{{textbook.title}}</h5>
          <h6 class="card-subtitle mb-2 text-muted">{{textbook.description }}</h6>
          <router-link :to="{ name: 'textbook.view.edit', params: {id: textbook.id} }">
            <a v-show="role==='Admin' || role==='Module Tutor'" href="edit" class="card-link">Edit</a>
          </router-link>
          <a @click="del(textbook.id)" v-show="role==='Admin' || role==='Module Tutor'" href="delete" class="card-link">Delete</a><br/>
          <router-link :to="{ name: 'textbook.view.show', params: {id: textbook.id} }">
          <a href="#" class="card-link">View</a>
          </router-link>
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
  metaInfo () {
    return { title: this.$t('home') }
  }


}
</script>
