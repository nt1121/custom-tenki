//import axios from 'axios';

export default {
    namespaced: true,
    state: {
        alertMessageTimeOutId: null,
        alertMessage: null,
        isPageLoadingActive: false
    },
    getters: {
        alertMessage(state) {
            return state.alertMessage;
        },
        isPageLoadingActive(state) {
            return state.isPageLoadingActive;
        }
    },
    mutations: {
        showAlertMessage(state, payload) {
            if (state.alertMessageTimeOutId !== null) {
                clearTimeout(state.alertMessageTimeOutId);
            }
            state.alertMessage = { type: payload.type, msg: payload.msg };
            state.alertMessageTimeOutId = setTimeout(() => {
                state.alertMessage = null;
            }, 4000);
        },
        hideAlertMessage(state) {
            if (state.alertMessageTimeOutId !== null) {
                clearTimeout(state.alertMessageTimeOutId);
            }
            state.alertMessage = null;
        },
        showPageLoading(state) {
            state.isPageLoadingActive = true;
        }
    }
};
