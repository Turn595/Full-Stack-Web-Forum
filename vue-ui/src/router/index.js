import Vue from 'vue'
import VueRouter from 'vue-router'
Vue.use(VueRouter)

const routes = [
  {
    path: '/',
    name: 'Home',
    component: () => import( '../views/Home.vue'),
  },
  {
    path: '/contact',
    name: 'Contact',
    component: () => import( '../views/Contact.vue')
  },
  {
    path: '/about',
    name: 'About',
    component: () => import( '../views/About.vue')
  },
  {
    path: '/article',
    name: 'Article',
    component: () => import( '../views/Article.vue')
  },
  {
    path: '/publish',
    name: 'Publish',
    component: () => import( '../views/Publish.vue')
  }
]

const router = new VueRouter({
  routes
})

export default router
