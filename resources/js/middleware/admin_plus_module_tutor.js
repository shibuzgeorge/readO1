import store from '~/store'

export default (to, from, next) => {

    if (store.getters['auth/role'] === 'Admin' || store.getters['auth/role'] === 'Module Tutor') {
        next()
    } else {
        next({ name: 'home' })
    }
}