<script>
import helpers from '../helpers';

export default {
  data() {
    return {
      loading: true,
      isError: null,
      tooManyRequests: null,
      password: '',
      passwordErrorMsg: '',
      newPassword: '',
      newPasswordErrorMsg: '',
      waitingForResponse: null,
    }
  },
  computed: {
    user() {
      return this.$store.getters['weather/user'];
    }
  },
  mounted() {
    this.getData();
  },
  methods: {
    getData() {
      axios.get('/api/settings/password_change')
        .then(response => {
          if (response.data && response.data.user) {
            // テストユーザーの場合は設定画面に戻る
            if (response.data.user.is_test_user) {
              this.$router.push('/weather/settings');
            }

            this.$store.commit('weather/setUser', response.data.user);
          } else {
            this.isError = true;
          }
        })
        .catch(error => {
          this.isError = true;

          if (error.response && error.response.status) {
            if (error.response.status === 429) {
              this.tooManyRequests = true;
            } else if (error.response.status === 401 && error.response.data && error.response.data.message === 'Unauthenticated.') {
              // ログインしていない場合はログイン画面にリダイレクト
              location.href = '/login';
            }
          }
        })
        .finally(() => {
          this.loading = false;
        });
    },
    validate() {
      this.passwordErrorMsg = helpers.validatePassword(this.password);
      this.newPasswordErrorMsg = helpers.validatePassword(this.newPassword);

      if (this.passwordErrorMsg.length || this.newPasswordErrorMsg.length) {
        return false;
      }

      return true;
    },
    submit() {
      if (this.waitingForResponse) {
        return false;
      }

      if (!this.validate()) {
        return false;
      }

      this.waitingForResponse = true;
      this.$store.commit('common/showPageLoading');
      let isError = null;
      let isValidationError = null;
      axios.post('/api/users/password', {
        _method: 'PATCH',
        user_id: this.user.id,
        password: this.password,
        new_password: this.newPassword
      })
        .then(response => {
          if (!response.data || !response.data.status || response.data.status !== 200) {
            isError = true;
          }
        })
        .catch(error => {
          isError = true;

          if (error.response && error.response.data && error.response.data.errors) {
            if (error.response.data.errors.password && error.response.data.errors.password[0]) {
              isValidationError = true;
              this.passwordErrorMsg = error.response.data.errors.password[0];
            }

            if (error.response.data.errors.new_password && error.response.data.errors.new_password[0]) {
              isValidationError = true;
              this.newPasswordErrorMsg = error.response.data.errors.new_password[0];
            }
          }
        })
        .finally(() => {
          this.$store.commit('common/hidePageLoading');

          if (isValidationError) {
          } else if (isError) {
            this.$store.commit('common/showAlertMessage', { msg: '情報の更新に失敗しました。', type: 'error' });
          } else {
            this.$store.commit('common/showAlertMessage', { msg: 'パスワードを変更しました。', type: 'success' });
            this.$router.push('/weather/settings');
          }

          this.waitingForResponse = false;
        });
    }
  }
}
</script>

<template>
  <transition name="u-fade-route">
    <p v-if="!loading && tooManyRequests">
      一定時間内のリクエストが多すぎます。<br>
      しばらく経ってからもう一度お試しください。
    </p>
    <p v-else-if="!loading && isError">情報の取得に失敗しました。</p>
    <div v-else-if="!loading">
      <h1 class="c-page-heading c-page-heading--with-left-arrow"> <router-link to="/weather/settings">
          <img src="/img/left_arrow.png" alt="戻る" class="c-page-heading__left-arrow"></router-link>パスワードの変更</h1>
      <div class="u-mb-20">
        <label class="c-form__label">現在のパスワード</label>
        <input-password name="password" v-model="password" :error-msg="passwordErrorMsg" place-holder=""
          :is-required="true"></input-password>
      </div>
      <div class="u-mb-20">
        <label class="c-form__label">新しいパスワード</label>
        <input-password name="new_password" v-model="newPassword" :error-msg="newPasswordErrorMsg"
          place-holder="半角英数8~255文字" :is-required="true"></input-password>
      </div>
      <button type="submit" class="c-button c-button--primary" :disabled="waitingForResponse"
        @click="submit">パスワードを変更</button>
    </div>
  </transition>
</template>
