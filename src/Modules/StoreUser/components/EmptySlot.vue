<template>
  <li>
    <button
      v-if="allowJoin"
      v-b-tooltip
      @click="$emit('join')"
      :title="$i18n('pickup.take_empty_slot')"
      class="btn"
    >
      <i class="fa fa-question" />
    </button>

    <button
      v-else-if="allowRemove"
      v-b-tooltip
      @click="$emit('remove')"
      @mouseover="hover = true"
      @mouseout="hover = false"
      title="Slot entfernen"
      class="btn"
    >
      <i :class="`fa ${hover ? 'fa-times' : 'fa-question'}`" />
    </button>

    <button
      v-else
      class="btn"
      disabled
    >
      <i class="fa fa-question" />
    </button>
  </li>
</template>

<script>
import bTooltip from '@b/directives/tooltip/tooltip'

export default {
  directives: { bTooltip },
  props: {
    allowJoin: {
      type: Boolean,
      default: false
    },
    allowRemove: {
      type: Boolean,
      default: false
    }
  },
  data () {
    return {
      hover: false
    }
  }
}
</script>

<style scoped>

</style>
