import Vue from 'vue'
import { VueperSlides, VueperSlide } from 'vueperslides'
import 'vueperslides/dist/vueperslides.css'

Vue.use(VueperSlides)
Vue.component('vueper-slides', VueperSlides)

Vue.use(VueperSlide)
Vue.component('vueper-slide', VueperSlide)
export default {
    components: { VueperSlides, VueperSlide }
}