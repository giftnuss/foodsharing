<template>
  <div class="bootstrap">
    <b-form-group>
      <b-form-radio
        v-for="option in options"
        :key="option.optionIndex"
        v-model="selected"
        :value="option.optionIndex"
        :disabled="!enabled"
      >
        {{ option.text }}
      </b-form-radio>
    </b-form-group>
  </div>
</template>

<script>

import { BFormGroup, BFormRadio } from 'bootstrap-vue'

export default {
  components: { BFormGroup, BFormRadio },
  props: {
    options: {
      type: Array,
      required: true,
    },
    enabled: {
      type: Boolean,
      default: true,
    },
  },
  data () {
    return {
      selected: -1,
    }
  },
  computed: {
    isValidSelection: function () {
      return this.selected >= 0 && this.selected < this.options.length
    },
    // only the selected option is needed for the REST request
    votingRequestValues: function () {
      return { [this.selected]: 1 }
    },
  },
  watch: {
    isValidSelection () { this.$emit('updateValidSelection', this.isValidSelection) },
    votingRequestValues () { this.$emit('updateVotingRequestValues', this.votingRequestValues) },
  },
  created () {
    this.$emit('updateValidSelection', this.isValidSelection)
    this.$emit('updateVotingRequestValues', this.votingRequestValues)
  },
}
</script>

<style scoped lang="scss">
</style>
