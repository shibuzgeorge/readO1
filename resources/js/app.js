import Vue from 'vue'
import store from '~/store'
import router from '~/router'
import i18n from '~/plugins/i18n'
import sweetmodal from '~/plugins/sweetmodal'
import cliploader from '~/plugins/cliploader'
import multiselect from '~/plugins/multiselect'
import paginate from '~/plugins/paginate'
import App from '~/components/App'



import '~/plugins'
import '~/components'

Vue.config.productionTip = false

/* eslint-disable no-new */
new Vue({
  i18n,
  store,
  router,
  sweetmodal,
  cliploader,
  paginate,
  multiselect,
  ...App,

})
