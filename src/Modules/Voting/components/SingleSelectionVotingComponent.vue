<template>
  <b-form-group>
    <b-form-radio
      v-for="option in options"
      v-model="selected"
      :value="option.optionIndex"
    > {{ option.text }}
    </b-form-radio>
  </b-form-group>
</template>

<script>

import { BFormGroup, BFormRadio } from 'bootstrap-vue'

export default {
  directives: {BFormGroup, BFormRadio},
  props: {
    options: {
      type: Object,
      required: true
    }
  },
  data() {
    return {
      selected: ''
    }
  },
  computed: {
    votingRequestValues: function () {
      return [selected => 1];
    }
  }
}
</script>

<style scoped lang="scss">
</style>
