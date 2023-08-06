import { createRouter, createWebHistory } from 'vue-router';
import WeatherForecast from '../Pages/WeatherForecast.vue';
import Settings from '../Pages/Settings.vue';
import AreaSelect from '../Pages/AreaSelect.vue';
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
        name: 'weather.area',
        component: AreaSelect,
    },
    {
        path: '/weather/settings/area/:id(\\d+)',
        component: AreaSelect,
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
