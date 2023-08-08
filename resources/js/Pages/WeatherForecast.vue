<script>
export default {
  data() {
    return {
      loading: true,
      isError: null,
      tooManyRequests: null,
      area: null,
      list: null
    }
  },
  mounted() {
    this.getData();
  },
  methods: {
    getData() {
      axios.get('/api/weather')
        .then(response => {
          if (response.data && response.data.user && response.data.area !== undefined && response.data.list !== undefined) {
            this.$store.commit('weather/setUser', response.data.user);

            if (response.data.area && response.data.list) {
              this.area = response.data.area;
              this.list = response.data.list;
            }
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
    <p v-if="!loading && tooManyRequests" class="u-mt-0">
      一定時間内のリクエストが多すぎます。<br>
      しばらく経ってからもう一度お試しください。
    </p>
    <p v-else-if="!loading && isError" class="u-mt-0">情報の取得に失敗しました。</p>
    <div v-else-if="!loading && area === null">
      <p class="u-mb-20 u-mt-0">地域が設定されていません。</p>
      <router-link to="/weather/settings/area" class="c-button">地域の選択</router-link>
    </div>
    <div v-else-if="!loading && area && list" class="p-wheather-forecast">
      <h2 class="p-wheather-forecast__area-name">{{ area.name }}の天気</h2>
      <template v-for="day in list" :key="day.date_key">
        <h3 class="p-wheather-forecast__date">{{ day.date_text }}</h3>
        <div class="p-wheather-forecast__table-wrapper">
          <table class="p-wheather-forecast__table">
            <tr>
              <th class="p-wheather-forecast__table-th" v-for="(item, itemIndex) in day.headers" :key="itemIndex">{{ item }}</th>
            </tr>
            <tr v-for="(valueList, valueListIndex) in day.value_list" :key="valueListIndex">
              <td class="p-wheather-forecast__table-td" v-for="(value, valueIndex) in valueList" :key="valueIndex" v-html="value"></td>
            </tr>
          </table>
        </div>
      </template>
    </div>
  </transition>
</template>
