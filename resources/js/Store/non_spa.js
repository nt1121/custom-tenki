import { createStore } from 'vuex';
import common from './Modules/common';

export default createStore({
    modules: {
        common: common
    }
});
