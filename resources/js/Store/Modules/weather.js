import axios from 'axios';
import router from "../../Router";

export default {
    namespaced: true,
    state: {
        user: null,
        selectedArea: null,
        isAreaSelectModalVisible: false
    },
    getters: {
        user(state) {
            return state.user;
        },
        selectedArea(state) {
            return state.selectedArea;
        },
        isAreaSelectModalVisible(state) {
            return state.isAreaSelectModalVisible;
        }
    },
    mutations: {
        setUser(state, payload) {
            state.user = payload;
        },
        setSelectedArea(state, payload) {
            state.selectedArea = payload;
        },
        showAreaSelectModal(state) {
            state.isAreaSelectModalVisible = true;
        },
        hideAreaSelectModal(state) {
            state.isAreaSelectModalVisible = false;
        }
    },
    actions: {
        updateUserAreaId(context) {
            console.log('updateUserAreaId');
            context.commit('common/showPageLoading', null, { root: true });
            let error = null;
            axios.post('/api/users/area_id', {
                _method: 'PATCH',
                user_id: context.state.user.id,
                area_id: context.state.selectedArea.id
            })
                .then(response => {
                    if (response.data && response.data.user) {
                        context.commit('hideAreaSelectModal');
                    } else {
                        error = true;
                    }
                })
                .catch(error => {
                    error = true;
                })
                .finally(() => {
                    context.commit('common/hidePageLoading', null, { root: true });

                    if (error) {
                        context.commit('common/showAlertMessage', { msg: '情報の更新に失敗しました。', type: 'error' }, { root: true });
                    } else {
                        context.commit('common/showAlertMessage', { msg: '地域を設定しました。', type: 'success' }, { root: true });
                        router.push('/weather/settings');
                    }
                });
        }
    }
};
