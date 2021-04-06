<template>
  <div>
    <div class="text-center">
      <div class="title mb-4">
        {{ title }}
      </div>
      <div class="nv-subtitle">
        Read. Speed. Challenge. Engage
      </div>
      <p>

      <div class="links">
      </div>
    </div>

    <div class="main-layout">

        <div>
          <vueper-slides  class="no-shadow" arrows-outside bullets-outside :slide-ratio="1 / 2" >
            <vueper-slide key="1" image="/screenshots/library.png"></vueper-slide>
            <vueper-slide key="2" image="/screenshots/Carousel.png"></vueper-slide>
            <vueper-slide key="3" image="/guide/guide1.png"></vueper-slide>
            <vueper-slide key="4" image="/guide/guide2.png"></vueper-slide>
            <vueper-slide key="5" image="/guide/guide3.png"></vueper-slide>
            <vueper-slide key="6" image="/guide/guide4.png"></vueper-slide>
            <vueper-slide key="7" title="Register Now!"></vueper-slide>
          </vueper-slides>
        </div>

    </div>
    <!--//<img src="/guide/guide1.png"/>-->

  </div>
</template>

<script>
import { mapGetters } from 'vuex'

export default {
  layout: 'default',

  metaInfo () {
    return { title: this.$t('home') }
  },

  data: () => ({
    title: window.config.appName
  }),

  computed: mapGetters({
    authenticated: 'auth/check'
  })
}
</script>

<style scoped>
.top-right {
  position: absolute;
  right: 10px;
  top: 18px;
}

.title {
  font-size: 85px;
}
.vueperslides__arrow {color: black}
</style>
