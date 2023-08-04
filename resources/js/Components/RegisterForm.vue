<script>
import helpers from '../helpers';

export default {
  props: [
    'oldEmail',
    'oldRemember',
    'emailInitialErrorMsg',
    'passwordInitialErrorMsg',
    'oldAgreeToTermsOfUse',
    'oldConsentToPrivacyPolicy'
  ],
  data() {
    return {
      isSubmitted: false,
      csrfToken: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      email: this.oldEmail,
      emailErrorMsg: this.emailInitialErrorMsg,
      password: '',
      passwordErrorMsg: this.passwordInitialErrorMsg,
      agreeToTermsOfUse: this.oldAgreeToTermsOfUse,
      consentToPrivacyPolicy: this.oldConsentToPrivacyPolicy
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

      if (this.emailErrorMsg.length || this.passwordErrorMsg.length || !this.agreeToTermsOfUse || !this.consentToPrivacyPolicy) {
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
  <form action="/register" method="post" @submit="validate">
    <input type="hidden" name="_token" :value="csrfToken">
    <h1 class="c-page-heading">新規会員登録</h1>
    <div class="u-mb-20">
      <label class="c-form__label">メールアドレス</label>
      <input class="c-form__input-text" :class="{ 'c-form__input-text--error': emailErrorMsg }" type="text"
        maxlength="150" v-model="email" name="email" placeholder="150文字以内" required>
      <div class="c-form__input-text-error-msg" v-show="emailErrorMsg">{{ emailErrorMsg }}</div>
    </div>
    <div class="u-mb-20">
      <label class="c-form__label">パスワード</label>
      <input-password name="password" v-model="password" :error-msg="passwordErrorMsg" place-holder="半角英数8~255文字"
        :is-required="true"></input-password>
    </div>
    <div class="u-mb-5">
      <input type="checkbox" name="agree_to_terms_of_use" value="1" class="c-form__input-checkbox"
        v-model="agreeToTermsOfUse" id="agree-to-terms-of-use-checkbox"><label for="agree-to-terms-of-use-checkbox"
        class="c-form__input-checkbox-label"><a href="/terms" target="_blank"
          rel="noopener noreferrer">利用規約</a>に同意する</label>
    </div>
    <div class="u-mb-20">
      <input type="checkbox" name="consent_to_privacy_policy" value="1" class="c-form__input-checkbox"
        v-model="consentToPrivacyPolicy" id="consent-to-privacy-policy-checkbox"><label
        for="consent-to-privacy-policy-checkbox" class="c-form__input-checkbox-label"><a href="/privacy" target="_blank"
          rel="noopener noreferrer">プライバシーポリシー</a>に同意する</label>
    </div>
    <div class="u-mb-20">
      <a href="/login">アカウントをお持ちの場合はこちら</a>
    </div>
    <button type="submit" class="c-button c-button--primary"
      :disabled="isSubmitted || !agreeToTermsOfUse || !consentToPrivacyPolicy">新規会員登録</button>
  </form>
</template>
