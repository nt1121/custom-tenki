<script>
import draggable from 'vuedraggable';

export default {
    data() {
        return {
            loading: true,
            isError: null,
            tooManyRequests: null,
            chosenFromList: null,
            chosenIndex: null,
            itemsToDisplay: [],
            itemsToHide: []
        }
    },
    computed: {
        user() {
            return this.$store.getters['weather/user'];
        }
    },
    mounted() {
        this.getData();
    },
    components: {
        draggable: draggable
    },
    methods: {
        onChoose(evt) {
            this.chosenFromList = evt.from.dataset.list;
            this.chosenIndex = evt.oldDraggableIndex;
        },
        onUnchoose() {
            this.chosenFromList = null;
            this.chosenIndex = null;
        },
        getData() {
            axios.get('/api/settings/item_select')
                .then(response => {
                    if (response.data && response.data.user && response.data.items_to_display && response.data.items_to_hide) {
                        this.$store.commit('weather/setUser', response.data.user);
                        this.itemsToDisplay = response.data.items_to_display;
                        this.itemsToHide = response.data.items_to_hide;
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
        update() {
            this.$store.commit('common/showPageLoading');
            let isError = null;
            axios.post('/api/user_weather_forecast_item', {
                _method: 'PUT',
                user_id: this.user.id,
                item_ids_to_display: this.itemsToDisplay.map((item) => item.id)
            })
                .then(response => {
                    if (!response.data || !response.data.user_weather_forecast_item) {
                        isError = true;
                    }
                })
                .catch(error => {
                    isError = true;
                })
                .finally(() => {
                    this.$store.commit('common/hidePageLoading', null);

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
        <div v-else-if="!loading" class="p-item-select">
            <h1 class="c-page-heading">項目の選択</h1>
            <p class="u-mb-20">項目をドラッグアンドドロップで移動してください。<br>上の項目が左に表示されます。</p>
            <p v-if="itemsToDisplay.length === 0" class="p-item-select__error-msg">表示する項目を１つ以上選択してください。</p>
            <div class="p-item-select__box-wrapper">
                <div class="p-item-select__box">
                    <div class="p-item-select__box-label">表示する項目</div>
                    <draggable :list="itemsToDisplay" item-key="id" tag="ul" group="items" @choose="onChoose"
                        @unchoose="onUnchoose" data-list="display">
                        <template #item="{ element, index }">
                            <li class="p-item-select__box-list-item"
                                :class="{ 'p-item-select__box-list-item--chosen': chosenFromList === 'display' && chosenIndex === index }"
                                :data-item-id="element.id">{{ element.display_name }}</li>
                        </template>
                    </draggable>
                </div>
                <div class="p-item-select__box">
                    <div class="p-item-select__box-label">表示しない項目</div>
                    <draggable :list="itemsToHide" item-key="id" tag="ul" group="items" @choose="onChoose"
                        @unchoose="onUnchoose" data-list="hide">
                        <template #item="{ element, index }">
                            <li class="p-item-select__box-list-item"
                                :class="{ 'p-item-select__box-list-item--chosen': chosenFromList === 'hide' && chosenIndex === index }"
                                :data-item-id="element.id">{{ element.display_name }}</li>
                        </template>
                    </draggable>
                </div>
            </div>
            <button type="button" class="c-button c-button--primary u-mr-20" :disabled="itemsToDisplay.length === 0"
                @click="update">設定を保存</button>
            <router-link to="/weather/settings" class="c-button">キャンセル</router-link>
        </div>
    </transition>
</template>
