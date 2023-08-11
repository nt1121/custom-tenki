//import axios from 'axios';

export default {
    namespaced: true,
    state: {
        alertMessageTimeOutId: null,
        alertMessageId: 0,
        alertMessages: [],
        isPageLoadingVisible: false
    },
    getters: {
        alertMessages(state) {
            return state.alertMessages;
        },
        isPageLoadingVisible(state) {
            return state.isPageLoadingVisible;
        }
    },
    mutations: {
        showAlertMessage(state, payload) {
            state.alertMessageId++;

            if (state.alertMessageId > 9999) {
                state.alertMessageId = 1;
            }

            if (state.alertMessageTimeOutId !== null) {
                clearTimeout(state.alertMessageTimeOutId);
            }

            state.alertMessages = [{ id: state.alertMessageId, msg: payload.msg, type: payload.type }];
            state.alertMessageTimeOutId = setTimeout(() => {
                state.alertMessages = [];
            }, 4000);
        },
        hideAlertMessage(state) {
            if (state.alertMessageTimeOutId !== null) {
                clearTimeout(state.alertMessageTimeOutId);
            }

            state.alertMessages = [];
        },
        showPageLoading(state) {
            state.isPageLoadingVisible = true;
        },
        hidePageLoading(state) {
            state.isPageLoadingVisible = false;
        }
    }
};
