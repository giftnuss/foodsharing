<template>
  <b-form-group>
    <b-form-checkbox
      v-for="option in options"
      :id="option.optionIndex"
      v-model="selected"
      :value="option.optionIndex"
    > {{ option.text }}
    </b-form-checkbox>
  </b-form-group>
</template>

<script>

import { BFormGroup, BFormCheckbox } from 'bootstrap-vue'

export default {
  directives: {BFormGroup, BFormCheckbox},
  props: {
    options: {
      type: Object,
      required: true
    }
  },
  data() {
    return {
      selected: []
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
