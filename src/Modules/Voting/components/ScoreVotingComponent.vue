<template>
  <div class="bootstrap">
    <div>
      {{ $i18n('poll.scorevoting.description') }}
    </div>
    <b-form-group>
      <div
        v-for="i in options.length"
        :key="i"
        class="my-1"
      >
        <div>{{ options[i-1].text }}, {{ values[i-1] }}</div>
        <vue-slider
          :min="-3"
          :max="3"
          :value="0"
          :marks="marks"
          :adsorb="true"
          class="my-5 w-50"
          @change="v => values[i-1] = +v"
        />
      </div>
    </b-form-group>
    <div>Value: {{ values }}</div>
  </div>
</template>

<script>

import { BFormGroup } from 'bootstrap-vue'
import VueSlider from 'vue-slider-component'
import 'vue-slider-component/theme/antd.css'

export default {
  components: { BFormGroup, VueSlider },
  props: {
    options: {
      type: Array,
      required: true
    }
  },
  data () {
    return {
      values: [0, 0, 0, 0],
      marks: [-3, -2, -1, 0, 1, 2, 3]
    }
  },
  computed: {
    votingRequestValues: function () {
      return [selected => 1]
    }
  },
  created () {
    this.$emit('updateValidSelection', true)
    this.$emit('updateVotingRequestValues', this.votingRequestValues)
    // this.values[this.options.length - 1] = 0
    // this.values.length = this.options.length
    // this.values = this.values.fill(0, 0, this.options.length)
  }
}
</script>

<style scoped lang="scss">
</style>
