<script>
export default {
    computed: {
        isAreaSelectModalVisible() {
            return this.$store.getters['weather/isAreaSelectModalVisible'];
        },
        selectedAreaName() {
            const selectedArea = this.$store.getters['weather/selectedArea'];
            if (selectedArea && selectedArea.name !== undefined && selectedArea.name !== null) {
                return selectedArea.name;
            } else {
                return '';
            }
        }
    },
    methods: {
        ok() {
            this.$store.dispatch('weather/updateUserAreaId');
        },
        cancel() {
            this.$store.commit('weather/hideAreaSelectModal');
        }
    }
}
</script>

<template>
    <div class="p-modal" v-show="isAreaSelectModalVisible">
        <div class="p-modal__window u-text-center">
            <div class="u-mb-20">{{ selectedAreaName }}を地域に設定しますか？</div>
            <button type="button" class="c-button c-button--primary u-mr-10" @click="ok">設定する</button>
            <button type="button" class="c-button" @click="cancel">キャンセル</button>
        </div>
    </div>
</template>
