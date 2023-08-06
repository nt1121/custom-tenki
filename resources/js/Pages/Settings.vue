<script>
export default {
  data() {
    return {
      loading: true,
      error: null,
      tooManyRequests: null,
    }
  },
  computed: {
    user() {
      return this.$store.getters['weather/user'];
    }
  },
  mounted() {
    axios.get('/api/users/store_state')
      .then(response => {
        if (response.data && response.data.user) {
          this.$store.commit('weather/setUser', response.data.user);
        } else {
          this.error = true
        }
      })
      .catch(error => {
        this.error = true

        if (error.response && error.response.status && error.response.status === 429) {
          this.tooManyRequests = true;
        }
      })
      .finally(() => {
        this.loading = false;
      });
  }
}
</script>

<template>
  <div class="c-spa-loading" v-if="loading"></div>
  <p v-else-if="tooManyRequests">
    一定時間内のリクエストが多すぎます。<br>
    しばらく経ってからもう一度お試しください。
  </p>
  <p v-else-if="error">情報の取得に失敗しました。</p>
  <template v-else>
    <h1 class="c-page-heading">設定</h1>
    <div class="u-mb-20">
      <h2 class="p-settings__heading">地域</h2>
      <p v-if="user.area_name !== null" class="u-mb-20">{{ user.area_name }}</p>
      <p v-else class="u-mb-20">地域が設定されていません。</p>
      <router-link to="/weather/settings/area" class="c-button">地域の選択</router-link>
    </div>
    <div class="u-mb-20">
      <h2 class="p-settings__heading">表示する項目</h2>
      <ul class="p-settings__selected-item-list">
        <li>天気</li>
        <li>気温</li>
        <li>降水確率</li>
      </ul>
      <button type="button" class="c-button">項目の選択</button>
    </div>
    <div class="u-mb-20">
      <h2 class="p-settings__heading">メールアドレス</h2>
      <p class="u-mb-20">{{ user ? user.email : '' }}</p>
      <p v-if="user.is_test_user" class="p-settings__test-user-error">テストユーザーのメールアドレスは変更できません。</p>
      <button v-else type="button" class="c-button">メールアドレスの変更</button>
    </div>
    <div class="u-mb-20">
      <h2 class="p-settings__heading">パスワード</h2>
      <p v-if="user.is_test_user" class="p-settings__test-user-error">テストユーザーのパスワードは変更できません。</p>
      <button v-else type="button" class="c-button">パスワードの変更</button>
    </div>
    <div class="u-mb-20">
      <h2 class="p-settings__heading">アカウント</h2>
      <p v-if="user.is_test_user" class="p-settings__test-user-error">テストユーザーのアカウントは削除できません。</p>
      <button v-else type="button" class="c-button c-button--danger">アカウントの削除</button>
    </div>
  </template>
</template>
