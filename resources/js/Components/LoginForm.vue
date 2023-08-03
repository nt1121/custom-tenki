<script>
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

      // メールアドレスのバリデーション
      if (this.email.length === 0) {
        this.emailErrorMsg = '入力してください。';
      } else if (this.email.length > 150) {
        this.emailErrorMsg = '150文字以下で入力してください。';
      } else {
        const emailRegex = /^[\w\-._]+@[\w\-._]+\.[A-Za-z]+$/;
        if (emailRegex.test(this.email)) {
          this.emailErrorMsg = '';
        } else {
          this.emailErrorMsg = 'メールアドレスの形式で入力してください。';
        }
      }

      // パスワードのバリデーション
      if (this.password.length === 0) {
        this.passwordErrorMsg = '入力してください。';
      } else if (this.password.length < 8) {
        this.passwordErrorMsg = '8文字以上で入力してください。';
      } else if (this.password.length > 255) {
        this.passwordErrorMsg = '255文字以下で入力してください。';
      } else {
        const passwordRegex = /^[a-zA-Z0-9]+$/;
        if (passwordRegex.test(this.password)) {
          this.passwordErrorMsg = '';
        } else {
          this.passwordErrorMsg = '半角英数で入力してください。';
        }
      }

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
      <input-password name="password" v-model="password" :error-msg="passwordErrorMsg" place-holder="" :is-required="true"/>
    </div>
    <div class="u-mb-20">
      <input type="checkbox" name="remember" value="1" class="c-form__input-checkbox" id="login-remember-checkbox"
        v-model="remember"><label for="login-remember-checkbox" class="c-form__input-checkbox-label">ログイン状態を保持する</label>
    </div>
    <div class="u-mb-20">
      <a href="/password_reset">パスワードをお忘れの方はこちら</a>
    </div>
    <div class="u-mb-20">
      <a href="/register">新規会員登録はこちら</a>
    </div>
    <button type="submit" class="c-button c-button--primary" :disabled="isSubmitted">ログイン</button>
  </form>
</template>
