function page (path) {
  return () => import(/* webpackChunkName: '' */ `~/pages/${path}`).then(m => m.default || m)
}

export default [
  { path: '/', name: 'welcome', component: page('welcome.vue') },
    { path: '/about', name: 'about', component: page('about.vue') },
  { path: '/login', name: 'login', component: page('auth/login.vue') },
  { path: '/register', name: 'register', component: page('auth/register.vue') },
  { path: '/password/reset', name: 'password.request', component: page('auth/password/email.vue') },
  { path: '/password/reset/:token', name: 'password.reset', component: page('auth/password/reset.vue') },
  { path: '/email/verify/:id', name: 'verification.verify', component: page('auth/verification/verify.vue') },
  { path: '/email/resend', name: 'verification.resend', component: page('auth/verification/resend.vue') },




    { path: '/yearGroup', name: 'yearGroup', component: page('yearGroup.vue') },

    { path: '/library', name: 'library', component: page('library.vue') },

    { path: '/module', name: 'module.index', component: page('module/index.vue') },
    { path: '/module/create', name: 'module.create', component: page('module/create.vue') },
    { path: '/module/assignStudents', name: 'module.assignStudents', component: page('module/assignStudents.vue') },
    { path: '/module/assignModuleTutors', name: 'module.assignModuleTutors', component: page('module/assignModuleTutors.vue') },
    { path: '/module/edit/:id', name: 'module.edit', component: page('module/edit.vue') },
    { path: '/module/view/:id', name: 'module.show', component: page('module/view.vue') },

    { path: '/textbook/create', name: 'textbook.create', component: page('textbook/create.vue') },
    { path: '/textbook/view/:id', name: 'textbook.show', component: page('textbook/view.vue') },
    { path: '/textbook/edit/:id', name: 'textbook.edit', component: page('textbook/edit.vue') },

    { path: '/text/view/:id', name: 'text.show', component: page('text/view.vue') },
    { path: '/text/create', name: 'text.create', component: page('text/create.vue') },
    { path: '/text/edit', name: 'text.edit', component: page('text/edit.vue') },

    { path: '/admin/users', name: 'admin.users', component: page('admin/users.vue') },
    { path: '/admin/users/edit/:id', name: 'admin.users.edit', component: page('admin/edit.vue') },
    { path: '/admin/users/update/:id', name: 'admin.users.update', component: page('admin/edit.vue') },

  { path: '/home', name: 'home', component: page('home.vue') },
  { path: '/settings',
    component: page('settings/index.vue'),
    children: [
      { path: '', redirect: { name: 'settings.profile' } },
      { path: 'profile', name: 'settings.profile', component: page('settings/profile.vue') },
      { path: 'password', name: 'settings.password', component: page('settings/password.vue') }
    ] },

  { path: '*', component: page('errors/404.vue') }
]
