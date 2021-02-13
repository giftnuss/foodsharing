<template>
  <div class="empty-slot">
    <button
      v-if="allowJoin && !allowRemove"
      v-b-tooltip="$i18n('pickup.take_empty_slot')"
      class="btn"
      @click="$emit('join')"
    >
      <i class="fas fa-question" />
    </button>
    <button
      v-else-if="allowRemove && !allowJoin"
      v-b-tooltip="$i18n('pickup.slot_remove')"
      class="btn"
      @mouseover="hover = true"
      @mouseout="hover = false"
      @click="$emit('remove')"
    >
      <i :class="`fas ${hover ? 'fa-times' : 'fa-question'}`" />
    </button>
    <b-dropdown
      v-else-if="allowJoin && allowRemove"
      no-caret
      toggle-class="btn p-0"
      variant="tertiary"
    >
      <template #button-content>
        <i class="fas fa-question" />
      </template>
      <b-dropdown-item @click="$emit('join')">
        <i class="fas fa-check-circle" /> {{ $i18n('pickup.take_empty_slot') }}
      </b-dropdown-item>
      <b-dropdown-item @click="$emit('remove')">
        <i class="fas fa-times-circle" /> {{ $i18n('pickup.slot_remove') }}
      </b-dropdown-item>
    </b-dropdown>

    <button
      v-else
      disabled
      class="btn"
    >
      <i class="fas fa-question" />
    </button>
  </div>
</template>

<script>

import { VBTooltip, BDropdown, BDropdownItem } from 'bootstrap-vue'

export default {
  components: { BDropdown, BDropdownItem },
  directives: { VBTooltip },
  props: {
    allowJoin: {
      type: Boolean,
      default: false,
    },
    allowRemove: {
      type: Boolean,
      default: false,
    },
  },
  data () {
    return {
      hover: false,
    }
  },
}
</script>

<style lang="scss" scoped>
.empty-slot {
  display: inline-block;
}
</style>
