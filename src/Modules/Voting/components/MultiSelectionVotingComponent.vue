<template>
  <div class="bootstrap">
    <b-form-group>
      <b-form-checkbox
        v-for="option in options"
        :key="option.optionIndex"
        v-model="selected"
        :value="option.optionIndex"
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
      required: true
    }
  },
  data () {
    return {
      selected: []
    }
  },
  computed: {
    // only the selected options is needed for the REST request
    votingRequestValues: function () {
      const v = {}
      for (const x in this.selected) {
        v[x.toString()] = 1
      }
      return v
    }
  },
  watch: {
    votingRequestValues () { this.$emit('updateVotingRequestValues', this.votingRequestValues) }
  },
  created () {
    this.$emit('updateValidSelection', true)
    this.$emit('updateVotingRequestValues', this.votingRequestValues)
  }
}
</script>

<style scoped lang="scss">
</style>
