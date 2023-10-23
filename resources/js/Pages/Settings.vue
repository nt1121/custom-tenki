<script>
export default {
  data() {
    return {
      loading: true,
      isError: null,
      tooManyRequests: null,
      itemsToDisplay: [],
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
      axios.get('/api/settings')
        .then(response => {
          if (response.data && response.data.user && response.data.items_to_display) {
            this.$store.commit('spa/setUser', response.data.user);
            this.itemsToDisplay = response.data.items_to_display;
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
          <li v-for="item in itemsToDisplay" :key="item.id">{{ item.display_name }}</li>
        </ul>
        <router-link to="/weather/settings/items" class="c-button">項目の選択</router-link>
      </div>
      <div class="u-mb-20">
        <h2 class="p-settings__heading">メールアドレス</h2>
        <p v-if="!user.is_test_user" class="u-mb-20">{{ user ? user.email : '' }}</p>
        <p v-if="user.is_test_user" class="p-settings__test-user-error">テストユーザーのメールアドレスは変更できません。</p>
        <router-link v-else to="/weather/settings/email" class="c-button">メールアドレスの変更</router-link>
      </div>
      <div class="u-mb-20">
        <h2 class="p-settings__heading">パスワード</h2>
        <p v-if="user.is_test_user" class="p-settings__test-user-error">テストユーザーのパスワードは変更できません。</p>
        <router-link v-else to="/weather/settings/password" class="c-button">パスワードの変更</router-link>
      </div>
      <div class="u-mb-20">
        <h2 class="p-settings__heading">アカウント</h2>
        <p v-if="user.is_test_user" class="p-settings__test-user-error">テストユーザーのアカウントは削除できません。</p>
        <a v-else href="/unregister" class="c-button c-button--danger">アカウントの削除</a>
      </div>
    </div>
  </transition>
</template>
