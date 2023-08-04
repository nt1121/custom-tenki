<script>
import helpers from '../helpers';

export default {
  props: [
    'oldEmail',
    'emailInitialErrorMsg'
  ],
  data() {
    return {
      isSubmitted: false,
      csrfToken: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      email: this.oldEmail,
      emailErrorMsg: this.emailInitialErrorMsg
    }
  },
  methods: {
    validate(e) {
      if (this.isSubmitted) {
        e.preventDefault();
        return false;
      }

      this.emailErrorMsg = helpers.validateEmail(this.email);

      if (this.emailErrorMsg.length) {
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
  <form action="/password_reset_request" method="post" @submit="validate">
    <input type="hidden" name="_token" :value="csrfToken">
    <h1 class="c-page-heading">パスワードの再設定</h1>
    <p class="u-mb-20">
      アカウントのメールアドレスを入力して「送信」ボタンを押してください。<br>
      メールアドレス宛に確認メールが送信されます。
    </p>
    <div class="u-mb-20">
      <label class="c-form__label">メールアドレス</label>
      <input class="c-form__input-text" :class="{ 'c-form__input-text--error': emailErrorMsg }" type="text"
        maxlength="150" v-model="email" name="email" required>
      <div class="c-form__input-text-error-msg" v-show="emailErrorMsg">{{ emailErrorMsg }}</div>
    </div>
    <button type="submit" class="c-button c-button--primary" :disabled="isSubmitted">送信</button>
  </form>
</template>
