import { createRouter, createWebHistory } from 'vue-router'

// Coba import dengan path yang lebih eksplisit
import Login from '../components/Login.vue'
import Dashboard from '../components/Dashboard.vue'
import Project from '../components/Project.vue'

const routes = [
  {
    path: '/',
    redirect: '/login'
  },
  {
    path: '/login',
    name: 'Login',
    component: Login
  },
  {
    path: '/dashboard', 
    name: 'Dashboard',
    component: Dashboard
  },
  {
    path: '/project',
    name: 'Project',
    component: Project
  },
  {
    path: '/project/:id',
    name: 'ProjectEdit',
    component: Project,
    props: true
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

export default router
