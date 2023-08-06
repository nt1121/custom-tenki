import { createStore } from 'vuex';
import common from './Modules/common';
import weather from './Modules/weather';

export default createStore({
    modules: {
        common: common,
        weather: weather
    }
});
