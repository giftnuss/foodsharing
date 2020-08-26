<template>
  <div class="bootstrap">
    <div>
      {{ $i18n('poll.thumbvoting.description') }}
    </div>
    <b-form-group
      v-for="(option, i) in options"
      :key="option.optionIndex"
    >
      <b-row>
        <b-col>
          <div>{{ option.text }}</div>
        </b-col>
        <b-col>
          <b-form-radio
            v-model="selected[i]"
            value="-1"
            button
          >
            <i class="fas fa-thumbs-down" />
          </b-form-radio>
          <b-form-radio
            v-model="selected[i]"
            value="0"
            button
          >
            <i class="fas fa-meh" />
          </b-form-radio>
          <b-form-radio
            v-model="selected[i]"
            value="1"
            button
          >
            <i class="fas fa-thumbs-up" />
          </b-form-radio>
        </b-col>
      </b-row>
    </b-form-group>
  </div>
</template>

<script>

import { BFormGroup, BFormRadio, BRow, BCol } from 'bootstrap-vue'

export default {
  components: { BFormGroup, BFormRadio, BRow, BCol },
  props: {
    options: {
      type: Array,
      required: true
    }
  },
  data () {
    return {
      selected: Array(this.options.length).fill(0)
    }
  },
  computed: {
    votingRequestValues: function () {
      const v = {}
      for (let i = 0; i < this.selected.length; i++) {
        v[this.options[i].optionIndex] = this.selected[i]
      }
      return v
    }
  },
  watch: {
    votingRequestValues () { this.$emit('updateVotingRequestValues', this.votingRequestValues) }
  },
  mounted () {
    this.$emit('updateValidSelection', true)
    this.$emit('updateVotingRequestValues', this.votingRequestValues)
  }
}
</script>

<style scoped lang="scss">
  .btn .fas {
    vertical-align: middle;
    margin-right: 0.5rem;
  }
  .btn .fas:last-child {
    margin-right: 0;
  }
</style>
