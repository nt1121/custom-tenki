<script>
export default {
    data() {
        return {
            loading: true,
            isError: null,
            tooManyRequests: null,
            areaGroup: null,
            list: [],
            selectedAreaId: null,
            selectedAreaName: null,
            isModalVisible: false
        }
    },
    computed: {
        user() {
            return this.$store.getters['spa/user'];
        }
    },
    mounted() {
        this.getData(this.$route.params['id']);
    },
    methods: {
        getData(areaGroupId) {
            const url = '/api/settings/area_select/' + (areaGroupId !== undefined ? areaGroupId : '');
            axios.get(url)
                .then(response => {
                    if (response.data && response.data.user && response.data.area_group) {
                        this.$store.commit('spa/setUser', response.data.user);
                        this.list = response.data.area_group.children;

                        if (response.data.area_group) {
                            this.areaGroup = response.data.area_group;
                        }
                    } else {
                        this.isError = true;
                    }
                })
                .catch(error => {
                    this.isError = true;

                    if (error.response && error.response.status) {
                        if (error.response.status === 429) {
                            this.tooManyRequests = true;
                        } else if (error.response.status === 401 && error.response.data && error.response.data.message === 'Unauthenticated.') {
                            // ログインしていない場合はログイン画面にリダイレクト
                            location.href = '/login';
                        }
                    }
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        showModal(id, name) {
            this.selectedAreaId = id;
            this.selectedAreaName = name;
            this.isModalVisible = true;
        },
        hideModal() {
            this.isModalVisible = false;
        },
        updateUserAreaId() {
            this.$store.commit('common/showPageLoading');
            let isError = null;
            axios.post('/api/users/area_id', {
                _method: 'PATCH',
                user_id: this.user.id,
                area_id: this.selectedAreaId
            })
                .then(response => {
                    if (response.data && response.data.user) {
                        this.hideModal();
                    } else {
                        isError = true;
                    }
                })
                .catch(error => {
                    isError = true;
                })
                .finally(() => {
                    this.$store.commit('common/hidePageLoading');

                    if (isError) {
                        this.$store.commit('common/showAlertMessage', { msg: '情報の更新に失敗しました。', type: 'error' });
                    } else {
                        this.$store.commit('common/showAlertMessage', { msg: '地域を設定しました。', type: 'success' });
                        this.$router.push('/weather/settings');
                    }
                });
        }
    }
}
</script>

<template>
    <transition name="u-fade-route">
        <p v-if="!loading && tooManyRequests">
            一定時間内のリクエストが多すぎます。<br>
            しばらく経ってからもう一度お試しください。
        </p>
        <p v-else-if="!loading && isError">情報の取得に失敗しました。</p>
        <div v-else-if="!loading">
            <h1 v-if="areaGroup.name !== null && areaGroup.parent_area_group_id !== null"
                class="c-page-heading c-page-heading--with-left-arrow">
                <router-link :to="'/weather/settings/area/' + areaGroup.parent_area_group_id">
                    <img src="/img/left_arrow.png" alt="戻る" class="c-page-heading__left-arrow">
                </router-link>{{ areaGroup.name }}
            </h1>
            <h1 v-else-if="areaGroup.name !== null && areaGroup.parent_area_group_id === null"
                class="c-page-heading c-page-heading--with-left-arrow">
                <router-link to="/weather/settings/area">
                    <img src="/img/left_arrow.png" alt="戻る" class="c-page-heading__left-arrow">
                </router-link>{{ areaGroup.name }}
            </h1>
            <h1 v-else class="c-page-heading c-page-heading--with-left-arrow">
                <router-link to="/weather/settings">
                    <img src="/img/left_arrow.png" alt="戻る" class="c-page-heading__left-arrow">
                </router-link>地域の選択
            </h1>
            <div class="p-area-select">
                <div v-for="area in list" :key="area.id" class="p-area-select__area-button-wrapper">
                    <button v-if="area.is_area" type="button" class="c-button c-button--primary p-area-select__area-button"
                        @click="showModal(area.id, area.name)">{{ area.name
                        }}</button>
                    <router-link v-else :to="'/weather/settings/area/' + area.id"
                        class="c-button p-area-select__area-button">
                        {{ area.name }}
                    </router-link>
                </div>
            </div>
        </div>
    </transition>
    <div class="p-modal" v-show="isModalVisible">
        <div class="p-modal__window u-text-center">
            <div class="u-mb-20">{{ selectedAreaName !== null ? selectedAreaName : '' }}を地域に設定しますか？</div>
            <button type="button" class="c-button c-button--primary u-mr-10" @click="updateUserAreaId">設定する</button>
            <button type="button" class="c-button" @click="hideModal">キャンセル</button>
        </div>
    </div>
</template>
