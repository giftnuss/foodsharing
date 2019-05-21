<template>
  <li :class="{clickable: allowJoin}">
    <b-img
      v-if="allowJoin"
      v-b-tooltip
      :title="$i18n('pickup.take_empty_slot')"
      @click="$emit('join')"
      alt="empty"
      src="/img/nobody.gif"
    />
    <b-img
      v-else
      alt="empty"
      src="/img/nobody.gif"
    />
  </li>
</template>

<script>
import bImg from '@b/components/image/img'
import bTooltip from '@b/directives/tooltip/tooltip'

export default {
  components: { bImg },
  directives: { bTooltip },
  props: {
    allowJoin: {
      type: Boolean,
      default: false
    }
  }
}
</script>

<style scoped>
</style>
