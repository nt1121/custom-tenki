<script>
export default {
  props: [
    'name',
    'modelValue',
    'errorMsg',
    'placeHolder',
    'isRequired'
  ],
  emits: [
    'update:modelValue'
  ],
  data() {
    return {
      type: 'password'
    }
  },
  methods: {
    toggleType() {
      if (this.type === 'password') {
        this.type = 'text';
      } else {
        this.type = 'password';
      }
    }
  }
}
</script>

<template>
  <div class="c-form__input-password-wrapper">
    <input class="c-form__input-password" :class="{ 'c-form__input-password--error': errorMsg }" :type="type" :name="name" :value="modelValue"
      @input="$emit('update:modelValue', $event.target.value)" :placeholder="placeHolder" maxlength="255" :required="isRequired">
    <img src="/img/eye_icon.png" alt="表示" class="c-form__input-password-eye-icon" @click="toggleType">
    <span class="c-form__input-password-eye-icon-line" v-show="type === 'text'"></span>
  </div>
  <div class="c-form__input-password-error-msg" v-show="errorMsg">{{ errorMsg }}</div>
</template>
