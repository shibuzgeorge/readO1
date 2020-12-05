<template>

  <card :title="$t('home')">


    <div class="search-wrapper"><br/>

      <label>Search textbook:</label>  <input type="text" class="" placeholder="Search title.."/><p></p>
    </div>
    <div class="wrapper">

    </div>
  </card>


</template>

<script>
export default {

  middleware: 'auth',

  metaInfo () {
    return { title: this.$t('home') }
  }


}
</script>
