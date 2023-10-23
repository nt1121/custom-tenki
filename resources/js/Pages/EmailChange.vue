<script>
import helpers from '../helpers';

export default {
  data() {
    return {
      loading: true,
      isError: null,
      tooManyRequests: null,
      itemsToDisplay: [],
      email: '',
      emailErrorMsg: '',
      waitingForResponse: null,
    }
  },
  computed: {
    user() {
      return this.$store.getters['spa/user'];
    }
  },
  mounted() {
    this.getData();
  },
  methods: {
    getData() {
      axios.get('/api/settings/email_change')
        .then(response => {
          if (response.data && response.data.user) {
            // テストユーザーの場合は設定画面に戻る
            if (response.data.user.is_test_user) {
              this.$router.push('/weather/settings');
            }

            this.$store.commit('spa/setUser', response.data.user);
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
      this.emailErrorMsg = helpers.validateEmail(this.email);

      if (this.emailErrorMsg.length) {
        return false;
      }

      if (this.email.trim() === this.user.email) {
        this.emailErrorMsg = '現在のメールアドレスと同じです。';
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
      axios.post('/api/users/email', {
        user_id: this.user.id,
        email: this.email
      })
        .then(response => {
          if (!response.data || !response.data.status || response.data.status !== 200) {
            isError = true;
          }
        })
        .catch(error => {
          isError = true;

          if (error.response && error.response.data && error.response.data.errors && error.response.data.errors.email && error.response.data.errors.email[0]) {
            isValidationError = true;
            this.emailErrorMsg = error.response.data.errors.email[0];
          }
        })
        .finally(() => {
          this.$store.commit('common/hidePageLoading');

          if (isValidationError) {
          } else if (isError) {
            this.$store.commit('common/showAlertMessage', { msg: '情報の登録に失敗しました。', type: 'error' });
          } else {
            this.$store.commit('common/showAlertMessage', { msg: '確認メールを送信しました。', type: 'success' });
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
          <img src="/img/left_arrow.png" alt="戻る" class="c-page-heading__left-arrow"></router-link>メールアドレスの変更</h1>
      <p class="u-mb-20">
        新しいメールアドレス宛に確認メールを送信します。<br>
        メールアドレスの確認の完了と同時にメールアドレスの変更が完了します。
      </p>
      <div class="u-mb-20">
        <label class="c-form__label">新しいメールアドレス</label>
        <input class="c-form__input-text" :class="{ 'c-form__input-text--error': emailErrorMsg }" type="text"
          maxlength="150" v-model="email" name="email" required>
        <div class="c-form__input-text-error-msg" v-show="emailErrorMsg">{{ emailErrorMsg }}</div>
      </div>
      <button type="submit" class="c-button c-button--primary" :disabled="waitingForResponse" @click="submit">送信</button>
    </div>
  </transition>
</template>
