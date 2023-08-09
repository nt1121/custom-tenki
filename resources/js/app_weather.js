import './bootstrap';
import { createApp } from 'vue/dist/vue.esm-bundler';
import store from './Store/weather';
import router from "./Router";
import InputPassword from './Components/InputPassword.vue'
import AlertMessage from './Components/AlertMessage.vue';
import PageLoading from './Components/PageLoading.vue';
import AreaSelectModal from './Components/AreaSelectModal.vue';

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
    .component('alert-message', AlertMessage)
    .component('page-loading', PageLoading)
    .component('area-select-modal', AreaSelectModal)
    .use(store)
    .use(router)
    .mount('#app');
