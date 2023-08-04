<script>
import helpers from '../helpers';

export default {
  props: [
    'oldEmail',
    'oldRemember',
    'emailInitialErrorMsg',
    'passwordInitialErrorMsg'
  ],
  data() {
    return {
      isSubmitted: false,
      csrfToken: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      email: this.oldEmail,
      emailErrorMsg: this.emailInitialErrorMsg,
      password: '',
      passwordErrorMsg: this.passwordInitialErrorMsg,
      remember: this.oldRemember
    }
  },
  methods: {
    validate(e) {
      if (this.isSubmitted) {
        e.preventDefault();
        return false;
      }

      this.emailErrorMsg = helpers.validateEmail(this.email);
      this.passwordErrorMsg = helpers.validatePassword(this.password);

      if (this.emailErrorMsg.length || this.passwordErrorMsg.length) {
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
  <form action="/login" method="post" @submit="validate">
    <input type="hidden" name="_token" :value="csrfToken">
    <h1 class="c-page-heading">ログイン</h1>
    <div class="u-mb-20">
      <label class="c-form__label">メールアドレス</label>
      <input class="c-form__input-text" :class="{ 'c-form__input-text--error': emailErrorMsg }" type="text"
        maxlength="150" v-model="email" name="email" required>
      <div class="c-form__input-text-error-msg" v-show="emailErrorMsg">{{ emailErrorMsg }}</div>
    </div>
    <div class="u-mb-20">
      <label class="c-form__label">パスワード</label>
      <input-password name="password" v-model="password" :error-msg="passwordErrorMsg" place-holder=""
        :is-required="true"></input-password>
    </div>
    <div class="u-mb-20">
      <input type="checkbox" name="remember" value="1" class="c-form__input-checkbox" id="login-remember-checkbox"
        v-model="remember"><label for="login-remember-checkbox" class="c-form__input-checkbox-label">ログイン状態を保持する</label>
    </div>
    <div class="u-mb-20">
      <a href="/password_reset_request">パスワードをお忘れの方はこちら</a>
    </div>
    <div class="u-mb-20">
      <a href="/register">新規会員登録はこちら</a>
    </div>
    <button type="submit" class="c-button c-button--primary" :disabled="isSubmitted">ログイン</button>
  </form>
</template>
