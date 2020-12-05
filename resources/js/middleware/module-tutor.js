import store from '~/store'

export default (to, from, next) => {

    if (store.getters['auth/role'] !== 'Module Tutor') {
        next({ name: 'home' })
    } else {
        next()
    }
}
