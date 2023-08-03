<script>
export default {
    computed: {
        isPageLoadingActive() {
            return this.$store.getters['common/isPageLoadingActive'];
        }
    }
}
</script>

<template>
    <div class="p-loading" v-show="isPageLoadingActive">
        <div class="p-loading__icon"></div>
    </div>
</template>
