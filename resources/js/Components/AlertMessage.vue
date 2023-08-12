<script>
export default {
    props: [
        'initialMsg',
        'initialType'
    ],
    computed: {
        alertMessages() {
            return this.$store.getters['common/alertMessages'];
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
    <div v-for="alertMessage in alertMessages" :key="alertMessage.id" class="p-alert-message"
        :class="{ 'p-alert-message--success': alertMessage.type === 'success', 'p-alert-message--error': alertMessage.type === 'error' }">
        {{ alertMessage.msg }}<div class="p-alert-message__close-button" @click="hide"></div>
    </div>
</template>
