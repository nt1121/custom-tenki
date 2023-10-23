export default {
    namespaced: true,
    state: {
        user: null
    },
    getters: {
        user(state) {
            return state.user;
        }
    },
    mutations: {
        setUser(state, payload) {
            state.user = payload;
        }
    }
};
