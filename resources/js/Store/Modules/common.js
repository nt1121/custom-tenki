//import axios from 'axios';

export default {
    namespaced: true,
    state: {
        alertMessageTimeOutId: null,
        alertMessage: null,
        isPageLoadingVisible: false
    },
    getters: {
        alertMessage(state) {
            return state.alertMessage;
        },
        isPageLoadingVisible(state) {
            return state.isPageLoadingVisible;
        }
    },
    mutations: {
        showAlertMessage(state, payload) {
            if (state.alertMessageTimeOutId !== null) {
                clearTimeout(state.alertMessageTimeOutId);
            }
            state.alertMessage = payload;
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
            state.isPageLoadingVisible = true;
        },
        hidePageLoading(state) {
            state.isPageLoadingVisible = false;
        }
    }
};
