import { createStore } from 'vuex';
import common from './Modules/common';
import spa from './Modules/spa';

export default createStore({
    modules: {
        common: common,
        spa: spa
    }
});
