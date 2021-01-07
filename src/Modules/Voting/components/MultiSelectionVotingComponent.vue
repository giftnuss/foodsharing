<template>
  <div class="bootstrap">
    <b-form-group>
      <b-form-checkbox
        v-for="option in options"
        :key="option.optionIndex"
        v-model="selected"
        :value="option.optionIndex"
        :disabled="!enabled"
        @change="votingRequestValues.update"
      >
        {{ option.text }}
      </b-form-checkbox>
    </b-form-group>
  </div>
</template>

<script>

import { BFormGroup, BFormCheckbox } from 'bootstrap-vue'

export default {
  components: { BFormGroup, BFormCheckbox },
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
      selected: [],
    }
  },
  computed: {
    // only the selected options are needed for the REST request
    votingRequestValues: function () {
      const v = {}
      for (let i = 0; i < this.selected.length; i++) {
        v[this.selected[i]] = 1
      }
      return v
    },
  },
  watch: {
    votingRequestValues () { this.$emit('updateVotingRequestValues', this.votingRequestValues) },
  },
  created () {
    this.$emit('updateValidSelection', true)
    this.$emit('updateVotingRequestValues', this.votingRequestValues)
  },
}
</script>

<style scoped lang="scss">
</style>
