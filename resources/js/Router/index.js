import { createRouter, createWebHistory } from 'vue-router';
import WeatherForecast from '../Pages/WeatherForecast.vue';
import Settings from '../Pages/Settings.vue';
import AreaSelect from '../Pages/AreaSelect.vue';
import ItemSelect from '../Pages/ItemSelect.vue';
import EmailChange from '../Pages/EmailChange.vue';
import PasswordChange from '../Pages/PasswordChange.vue';
import NotFound from '../Pages/NotFound.vue';

const routes = [
    {
        path: '/weather',
        name: 'weather',
        component: WeatherForecast,
    },
    {
        path: '/weather/settings',
        name: 'weather.settings',
        component: Settings,
    },
    {
        path: '/weather/settings/area',
        name: 'weather.settings.area',
        component: AreaSelect,
    },
    {
        path: '/weather/settings/area/:id(\\d+)',
        component: AreaSelect,
    },
    {
        path: '/weather/settings/items',
        name: 'weather.settings.items',
        component: ItemSelect,
    },
    {
        path: '/weather/settings/email',
        name: 'weather.settings.email',
        component: EmailChange,
    },
    {
        path: '/weather/settings/password',
        name: 'weather.settings.password',
        component: PasswordChange,
    },
    {
        path: '/:catchAll(.*)',
        component: NotFound,
    }
];
const router = createRouter({
    routes,
    history: createWebHistory(),
})
export default router;
