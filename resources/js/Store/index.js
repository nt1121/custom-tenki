import { createStore } from 'vuex';
import common from './common';

export default createStore({
    modules: {
        common: common
    }
});
