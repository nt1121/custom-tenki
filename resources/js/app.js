import './bootstrap';
import { createApp } from 'vue/dist/vue.esm-bundler';
import store from './Store/index';
import InputPassword from './Components/InputPassword.vue'
import LoginForm from './Components/LoginForm.vue';
import RegisterForm from './Components/RegisterForm.vue';
import AlertMessage from './Components/AlertMessage.vue';
import PageLoading from './Components/PageLoading.vue';

const app = createApp({
    data() {
        return {
            canToggleHamburgerButton: true,
            isHamburgerMenuActive: false
        }
    },
    methods: {
        toggleHamburgerMenu() {
            if (!this.canToggleHamburgerButton) {
                return;
            }
            this.canToggleHamburgerButton = false;
            this.isHamburgerMenuActive = !this.isHamburgerMenuActive;
            setTimeout(() => {
                this.canToggleHamburgerButton = true;
            }, 200);
        }
    },
})
app.component('input-password', InputPassword)
    .component('login-form', LoginForm)
    .component('register-form', RegisterForm)
    .component('alert-message', AlertMessage)
    .component('page-loading', PageLoading)
    .use(store)
    .mount('#app')
