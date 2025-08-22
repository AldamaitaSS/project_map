import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import '@fortawesome/fontawesome-free/css/all.min.css';

// Buat app instance
const app = createApp(App)

// Use router
app.use(router)

// Mount ke DOM
app.mount('#app')