<script>
export default {
    data() {
        return {
            loading: true,
            error: null,
            tooManyRequests: null,
            areaGroup: null,
            list: []
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
                    if (response.data && response.data.area_group) {
                        this.list = response.data.area_group.children;

                        if (response.data.area_group) {
                            this.areaGroup = response.data.area_group;
                        }
                    } else {
                        this.error = true
                    }
                })
                .catch(error => {
                    this.error = true

                    if (error.response && error.response.status && error.response.status === 429) {
                        this.tooManyRequests = true;
                    }
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        showModal(id, name) {
            this.$store.commit('weather/setSelectedArea', { id: id, name: name });
            this.$store.commit('weather/showAreaSelectModal');
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
        <p v-else-if="!loading && error">情報の取得に失敗しました。</p>
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
</template>
