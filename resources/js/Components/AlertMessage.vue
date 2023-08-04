<script>
export default {
    props: [
        'initialMsg',
        'initialType'
    ],
    computed: {
        alertMessage() {
            return this.$store.getters['common/alertMessage'];
        }
    },
    methods: {
        hide() {
            this.$store.commit('common/hideAlertMessage');
        }
    },
    mounted() {
        if (this.initialMsg && this.initialMsg.length && this.initialType) {
            this.$store.commit('common/showAlertMessage', { msg: this.initialMsg, type: this.initialType });
        }
    }
}
</script>

<template>
    <div v-if="alertMessage" class="p-alert-message"
        :class="{ 'p-alert-message--success': alertMessage.type === 'success', 'p-alert-message--error': alertMessage.type === 'error' }">
        {{ alertMessage.msg }}<div class="p-alert-message__close-button" @click="hide"></div>
    </div>
</template>


