<script>
import helpers from '../helpers';

export default {
  props: [
    'passwordInitialErrorMsg',
    'token'
  ],
  data() {
    return {
      isSubmitted: false,
      csrfToken: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      password: '',
      passwordErrorMsg: this.passwordInitialErrorMsg
    }
  },
  methods: {
    validate(e) {
      if (this.isSubmitted) {
        e.preventDefault();
        return false;
      }

      this.passwordErrorMsg = helpers.validatePassword(this.password);

      if (this.passwordErrorMsg.length) {
        e.preventDefault();
        return false;
      }

      this.isSubmitted = true;
      this.$store.commit('common/showPageLoading');
      return true;
    }
  }
}
</script>

<template>
  <form action="/password_reset" method="post" @submit="validate">
    <input type="hidden" name="_token" :value="csrfToken">
    <input type="hidden" name="token" :value="token">
    <h1 class="c-page-heading">パスワードの再設定</h1>
    <div class="u-mb-20">
      <label class="c-form__label">新しいパスワード</label>
      <input-password name="password" v-model="password" :error-msg="passwordErrorMsg" place-holder="半角英数8~255文字"
        :is-required="true"></input-password>
    </div>
    <button type="submit" class="c-button c-button--primary"
      :disabled="isSubmitted">パスワードを設定</button>
  </form>
</template>
