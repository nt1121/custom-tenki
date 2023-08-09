import './bootstrap';
import { createApp } from 'vue/dist/vue.esm-bundler';
import store from './Store/index';
import InputPassword from './Components/InputPassword.vue'
import LoginForm from './Components/LoginForm.vue';
import RegisterForm from './Components/RegisterForm.vue';
import AlertMessage from './Components/AlertMessage.vue';
import PageLoading from './Components/PageLoading.vue';
import PasswordResetRequest from './Components/PasswordResetRequest.vue';
import PasswordReset from './Components/PasswordReset.vue';
import UnregisterForm from './Components/UnregisterForm.vue';

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
        },
        showPageLoading() {
            this.$store.commit('common/showPageLoading');
        }
    },
});

app.component('input-password', InputPassword)
    .component('login-form', LoginForm)
    .component('register-form', RegisterForm)
    .component('alert-message', AlertMessage)
    .component('page-loading', PageLoading)
    .component('password-reset-request', PasswordResetRequest)
    .component('password-reset', PasswordReset)
    .component('unregister-form', UnregisterForm)
    .use(store)
    .mount('#app');
