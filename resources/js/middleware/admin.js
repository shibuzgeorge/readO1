import store from '~/store'

export default (to, from, next) => {

  if (store.getters['auth/role'] !== 'Admin') {
    next({ name: 'home' })
  } else {
    next()
  }
}
