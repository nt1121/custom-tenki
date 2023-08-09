<script>
import helpers from '../helpers';

export default {
  props: [
    'userId',
    'passwordInitialErrorMsg'
  ],
  data() {
    return {
      isSubmitted: false,
      csrfToken: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      password: '',
      passwordErrorMsg: this.passwordInitialErrorMsg,
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
  <form action="/unregister" method="POST" @submit="validate">
    <input type="hidden" name="_method" value="DELETE">
    <input type="hidden" name="_token" :value="csrfToken">
    <input type="hidden" name="user_id" :value="userId">
    <h1 class="c-page-heading">アカウントの削除</h1>
    <div class="u-mb-20">
      <label class="c-form__label">現在のパスワード</label>
      <input-password name="password" v-model="password" :error-msg="passwordErrorMsg" place-holder=""
        :is-required="true"></input-password>
    </div>
    <button type="submit" class="c-button c-button--danger"
      :disabled="isSubmitted">アカウントを削除する</button>
  </form>
</template>
