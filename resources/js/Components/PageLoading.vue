<script>
export default {
    computed: {
        isPageLoadingVisible() {
            return this.$store.getters['common/isPageLoadingVisible'];
        }
    }
}
</script>

<template>
    <div class="p-loading" v-show="isPageLoadingVisible">
        <div class="p-loading__icon"></div>
    </div>
</template>
